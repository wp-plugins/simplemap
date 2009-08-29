<?php
/*
SimpleMap Plugin
csv-process.php: Imports/exports a CSV file to/from the database
*/

include "../includes/connect-db.php";
include "../includes/sminc.php";

if (isset($_POST['action'])) {

	// EXPORT to CSV file
	if ($_POST['action'] == 'export') {
	
		$csv_output = '"name","address","address2","city","state","country","zip","phone","fax","url","category","description","special"'."\n";
		
		$values = mysql_query("SELECT name, address, address2, city, state, country, zip, phone, fax, url, category, description, special FROM $table ORDER BY name");
		while ($row = mysql_fetch_assoc($values)) {
			$description = str_replace('"', "'", html_entity_decode(str_replace("\n", '', nl2br($row['description']))));
			$csv_output .= '"'.$row['name'].'","'.$row['address'].'","'.$row['address2'].'","'.$row['city'].'","'.$row['state'].'","'.$row['country'].'","'.$row['zip'].'","'.$row['phone'].'","'.$row['fax'].'","'.$row['url'].'","'.$row['category'].'","'.$description.'","'.$row['special'].'"'."\n";
			//$csv_output .= join(',', $row)."\n";
		}
		
		header("Content-type: application");
		header("Content-disposition: csv; filename=SimpleMap_".date("Y-m-d").".csv; size=".strlen($csv_output));
		
		print $csv_output;
		
		exit;

	}

	// IMPORT CSV file
	else if ($_POST['action'] == 'import') {
	
		// This works GREAT but does not geocode:
		
		//mysql_query("LOAD DATA LOCAL INFILE '".$_FILES['uploadedfile']['tmp_name']."' INTO TABLE ".$table." FIELDS TERMINATED BY ',' (name, address, address2, city, state, country, zip, phone, fax, url, category, description, special);");
		
		//mysql_query("ALTER TABLE $table DISABLE KEYS");
		//mysql_query("LOCK TABLES $table WRITE");
		$csvcontent = file_get_contents($_FILES['uploadedfile']['tmp_name']);
	
		if (strpos($csvcontent, '","') === false)
			$fieldseparator = ',';
		else
			$fieldseparator = '","';
		
		$lineseparator = "\n";
		
		$linescontent = split($lineseparator, $csvcontent);
		$linescontent = array_slice($linescontent, 1, -1);
		if ($linescontent[0] == 'name,address,address2,city,state,country,zip,phone,fax,url,category,description,special' || $linescontent[0] == '"name","address","address2","city","state","country","zip","phone","fax","url","category","description","special"')
			$linescontent = array_slice($linescontent, 1);
		
		$lines = 0;
		$queries = "";
		$linearray = array();
		
		$delay = 0;

		foreach($linescontent as $line) {
		
			/* Array number keys:
				0 = name
				1 = address
				2 = address2
				3 = city
				4 = state
				5 = country
				6 = zip
				7 = phone
				8 = fax
				9 = url
				10 = category
				11 = description
				12 = special
			*/
		
			$lines++;
			$line = str_replace("\r", "", $line);
			//$line = str_replace("'", "\'", $line);
		
			$linearray = quotesplit($line);
			
			foreach ($linearray as $l) {
				$l = trim($l, '"');
			}
			foreach ($linearray as $key => $value) {
				$linearray[$key] = mysql_real_escape_string($value);
			}
			
			// Add 'http://' to the URL if it isn't already there
			if ($linearray[9] != '') {
				if (strpos($linearray[9], 'http://') === false)
					$linearray[9] = 'http://'.$linearray[9];
			}
			
			// Re-encode HTML entities in description, and change any '<br />' back to '\n'
			$linearray[11] = htmlentities(str_replace('<br />', "\n", $linearray[11]));
			
			// If 'special' is blank, set it to zero
			if ($linearray[12] == '')
				$linearray[12] = '0';
				
			define("MAPS_HOST", "maps.google.com");
			define("KEY", $options['api_key']);
			
			$geocodeAddress = $linearray[1].', '.$linearray[3];
			if ($linearray[4] != 'none')
				$geocodeAddress .= ', '.$linearray[4];
			$geocodeAddress .= ', '.$linearray[5];
			
			// BEGIN Geocode ======================================================
			
			$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
			$request_url = $base_url . "&q=" . urlencode($geocodeAddress);
			//$xml = simplexml_load_file($request_url) or die("url not loading");
			$request_string = curl_get_contents($request_url);
			$xml = simplexml_load_string($request_string) or die("URL not loading");
			
			$status = $xml->Response->Status->code;
			if (strcmp($status, "200") == 0) {
				// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->Response->Placemark->Point->coordinates;
				$coordinatesSplit = split(",", $coordinates);
				// Format: Longitude, Latitude, Altitude
				$linearray[13] = $coordinatesSplit[1];
				$linearray[14] = $coordinatesSplit[0];
				
				//print_r($linearray);
			
				$linemysql = implode("','", $linearray);
	
				$query = "INSERT INTO $table (name, address, address2, city, state, country, zip, phone, fax, url, category, description, special, lat, lng) VALUES ('$linemysql');";
		
				//$queries .= $query . "\n\n";
				@mysql_query($query);

				//echo "executing : $query<br />";
				//echo $insert_result;
			}
			else if (strcmp($status, "620") == 0) {
		      // sent geocodes too fast
		      $delay += 100000;
			}
			else {
				// failure to geocode
				$geocode_pending = false;
				echo "Address " . $geocodeAddress . " failed to geocode. ";
				echo "Received status " . $status . "<br/>\n";
			}
			usleep($delay);
			
			// END Geocode ======================================================
		
		}
		
		//echo $queries;
		//mysql_query($queries);
		
		//mysql_query("UNLOCK TABLES");
		//mysql_query("ALTER TABLE $table ENABLE KEYS");
		
		$message = urlencode("$lines records imported successfully.");
		
		header("Location: ../../../../wp-admin/admin.php?page=Manage%20Database&message=$message");
		exit();

	}
}


function quotesplit($s) {
    $r = Array();
    $p = 0;
    $l = strlen($s);
    while ($p < $l) {
        while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false)) $p++;
        if ($s[$p] == '"') {
            $p++;
            $q = $p;
            while (($p < $l) && ($s[$p] != '"')) {
                if ($s[$p] == '\\') { $p+=2; continue; }
                $p++;
            }
            $r[] = stripslashes(substr($s, $q, $p-$q));
            $p++;
            while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false)) $p++;
            $p++;
        } else if ($s[$p] == "'") {
            $p++;
            $q = $p;
            while (($p < $l) && ($s[$p] != "'")) {
                if ($s[$p] == '\\') { $p+=2; continue; }
                $p++;
            }
            $r[] = stripslashes(substr($s, $q, $p-$q));
            $p++;
            while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false)) $p++;
            $p++;
        } else {
            $q = $p;
            while (($p < $l) && (strpos(",;",$s[$p]) === false)) {
                $p++;
            }
            $r[] = stripslashes(trim(substr($s, $q, $p-$q)));
            while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false)) $p++;
            $p++;
        }
    }
    return $r;
}


?>