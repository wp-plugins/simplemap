<?php
/*
SimpleMap Plugin
csv-process.php: Imports/exports a CSV file to/from the database
*/



include "../includes/connect-db.php";
include "../includes/sminc.php";
include "../includes/parsecsv.lib.php";

if ( isset($_POST['action'] ) ) {
	$action = $_POST['action'];
} else if ( isset( $_GET['action'] ) ) {
	$action = $_GET['action'];
} else {
	die( 'No action set!' );
}
	
if ( isset( $action ) ) {

	// EXPORT to CSV file
	if ( $action == 'export' ) {
	
		$csv = new parseCSV();
		
		$table_data = array();
	
		//$csv_output = '"name","address","address2","city","state","country","zip","phone","fax","url","category","tags","description","special","lat","lng"'."\n";
		
		$values = mysql_query( "SELECT name, address, address2, city, state, country, zip, phone, fax, url, category, tags, description, special, lat, lng FROM $table ORDER BY name" );
		
		$fields = array( "name","address","address2","city","state","country","zip","phone","fax","url","category","tags","description","special","lat","lng" );
		
		while ( $row = mysql_fetch_assoc( $values ) ) {
			$table_data[] = $row;
		}
		
		$csv->output( true, "SimpleMap_" . date( "Y-m-d" ) . ".csv", $table_data, $fields );
	}

	// IMPORT CSV file
	else if ( $_POST['action'] == 'import' ) {
		
		//var_dump($_FILES['uploadedfile']);
		
		
		/* Trying: add checkbox on import page for manually entered lat/lng values
		===================================================================================== */
		
		//if ( $_POST['manual_latlng'] ) {
			
		$ignorelines 	= '';
		$csvcontent 	= file_get_contents( $_FILES['uploadedfile']['tmp_name'] );
		$lineseparator 	= "\n";
		$linescontent 	= explode( $lineseparator, $csvcontent );
		$count 			= count( $linescontent );
				
		$csv = new parseCSV();
		$csv->auto( $_FILES['uploadedfile']['tmp_name'] );
		
		$errors = '';
		$lines = 0;
		
		/* Matches column names in CSV to columns in database
		*******************************************************************/
		
		$fields = array(
			'name' => 'name',
			'address' => 'address',
			'address2' => 'address2',
			'city' => 'city',
			'state' => 'state',
			'zip' => 'zip',
			'country' => 'country',
			'phone' => 'phone',
			'fax' => 'fax',
			'url' => 'url',
			'category' => 'category',
			'tags' => 'tags',
			'description' => 'description',
			'special' => 'special',
			'lat' => 'lat',
			'lng' => 'lng'
		);
		
		$csv_array = $csv->data;
		foreach ( $csv_array[0] as $key => $value ) {
			foreach ( $fields as $db_field => $csv_field ) {
				if ( $db_field == 'address' ) {
					if ( preg_match( "/address/i", $key ) && !preg_match( "/address2/i", $key ) )
						$fields['address'] = $key;
				} else if ( preg_match( "/" . $db_field . "/i", $key ) ) {
					$fields[$db_field] = $key;
					continue;
				}
			}
		}
		//echo "<pre>";print_r( $fields ); die();
		/*
		echo "Column matches (your CSV column name = SimpleMap column name):<br />\n";
		foreach ($fields as $key => $value) {
			echo "'$value' = '$key'<br />\n";
		}
		*/
		
		/* End of column matching
		*******************************************************************/
		
		/* Validate & insert data row by row
		*******************************************************************/
		
		foreach ( $csv->data as $data ) {
			
			// This stores the values into a temporary array called $row
			// and uses the standard field names for simplicity in this loop.
			$row = array();
			foreach ( $fields as $key => $value ) {
				$row[$key] = isset( $data[$value] ) ? $data[$value] : '';
			}
			
			// Add 'http://' to the URL if it isn't already there
			if ( $row['url'] != '' ) {
				if ( strpos( $row['url'], 'http://' ) === false )
					$row['url'] = 'http://' . $row['url'];
			}
			
			// Re-encode HTML entities in description, and change any '<br />' back to '\n'
			$row['description'] = htmlspecialchars( str_replace ( '<br />', "\n", $row['description'] ) );
			
			// If 'special' is blank, set it to zero
			if ( $row['special'] == '' || !$row['special'] )
				$row['special'] = '0';
				
			$ready_to_insert = false;
			
			// If latitude & longitude are both present, do not geocode
			if ( $row['lat'] != '' && $row['lng'] != '' ) {
				$ready_to_insert = true;
				
			} else {
				global $options;
				
				if( !defined( "MAPS_HOST" ) )
					define( "MAPS_HOST", "maps.google.com" );
	
				if ( !defined( "KEY" ) )
					define( "KEY", $options['api_key'] );
				
				$geocodeAddress = $row['name'] . ', ' . $row['city'];
				if ( $row['state'] )
					$geocodeAddress .= ', ' . $row['state'];
				
				$geocodeAddress .= ', ' . $row['country'];
				
				$geocode_pending = true;
				
				/* Begin Geocode
				*******************************************************************/
				
				while ( $geocode_pending ) {
				
					$base_url = "http://" . MAPS_HOST . "/maps/geo?sensor=false&output=csv&key=" . KEY;
					$request_url = $base_url . "&q=" . urlencode($geocodeAddress);
					
					if ( function_exists( 'curl_get_contents' ) )
						$request_string = curl_get_contents( $request_url );
					else
						$request_string = file_get_contents( $request_url );
					
					$response = explode( ',', $request_string );
					
					$status = $response[0];
					
					if ( $status == '200' ) {
						// Successful geocode
						$geocode_pending = false;
						$row['lat'] = $response[2];
						$row['lng'] = $response[3];
						
						$ready_to_insert = true;
					} else if ($status == '620') {
					    // sent geocodes too fast
					    $delay = 5000;
					} else {
						// failure to geocode
						$geocode_pending = false;
						$errors .= sprintf( __('Location "%s" failed to geocode, with status %s', 'SimpleMap' ), $row['name'], $status )."<br />";
					}
					
					if ( isset( $delay ) )
						usleep( $delay );
				}
				
				/* End Geocode
				*******************************************************************/
			}
			
			// If the record now has a latitude and longitude value, insert it into the database
			if ( $ready_to_insert ) {
				
				// Protection from mysql injection
				foreach ( $row as $key => $value )
					$row[$key] = mysql_real_escape_string( trim( $value ) );
				
				$query = "INSERT INTO $table (name, address, address2, city, state, zip, country, phone, fax, url, category, tags, description, special, lat, lng) VALUES ('" . $row['name'] . "', '" . $row['address'] . "', '" . $row['address2'] . "', '" . $row['city'] . "', '" .$row['state'] . "', '" . $row['zip'] . "', '" . $row['country'] . "', '" . $row['phone'] . "', '" . $row['fax'] . "', '" . $row['url'] . "', '" . $row['category'] . "', '" . $row['tags'] . "', '" . $row['description'] . "', '" . $row['special'] . "', '" . $row['lat'] . "', '" . $row['lng'] . "');";
				
				$result = @mysql_query($query);
				
				// If there is no result, note the record that failed
				if ( !$result )
					$errors .= sprintf( __('Location "%s" was not successfully inserted into the database.%s', 'SimpleMap'), $row['name'], "<br />" );
				else
					$lines++;
			}
		
		}
			
		if ( $errors != '' )
			$errors = '<br />' . $errors . "<br />Find out what the errors mean <a href='http://code.google.com/apis/maps/documentation/javascript/v2/reference.html#GGeoStatusCode.Constants' target='new'>here</a>";
		
		$message = urlencode( sprintf( __( '%d records imported successfully.', 'SimpleMap' ), $lines ) . $errors );

		
		//echo urldecode($message);
		
		header( "Location: ../../../../wp-admin/admin.php?page=manage-database&message=$message" );
		exit();
		
	}
}


?>