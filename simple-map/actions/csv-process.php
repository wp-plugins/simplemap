<?php

include "../includes/connect-db.php";

if (isset($_POST['action'])) {

	// EXPORT to CSV file
	if ($_POST['action'] == 'export') {
			
		/*
		$result = mysql_query("SHOW COLUMNS FROM $table");
		while ($row = mysql_fetch_assoc($result)) {
			$csv_output .= $row['Field'].',';
		}
		$csv_output = substr($csv_output, 0, -1)."\n";
		*/
		$csv_output = "name,address,address2,city,state,zip,phone,fax,url,special\n";
		
		$values = mysql_query("SELECT name, address, address2, city, state, zip, phone, fax, url, special FROM $table ORDER BY name");
		while ($row = mysql_fetch_assoc($values)) {
			$csv_output .= '"'.join('","', str_replace('"', '""', $row))."\"\n";
		}
		
		header("Content-type: application");
		header("Content-disposition: csv; filename=" . date("Y-m-d") . "_".$table.".csv; size=".strlen($csv_output));
		
		print $csv_output;
		
		exit;

	}

	// IMPORT CSV file
	else if ($_POST['action'] == 'import') {
		//echo "inside import<br />";

		/* Would you like to add an empty field at the beginning of these records?
		/* This is useful if you have a table with the first field being an auto_increment integer
		/* and the csv file does not have such as empty field before the records.
		/* Set 1 for yes and 0 for no. ATTENTION: don't set to 1 if you are not sure.
		/* This can dump data in the wrong fields if this extra field does not exist in the table
		/********************************/
		$addauto = 0;
		
		$csvcontent = file_get_contents($_FILES['uploadedfile']['tmp_name']);

		$fieldseparator = '","';
		$lineseparator = "\n";
		
		$linescontent = split($lineseparator, $csvcontent);
		$linescontent = array_slice($linescontent, 1, -1);
		
		$lines = 0;
		$queries = "";
		$linearray = array();
		
		$delay = 0;

		foreach($linescontent as $line) {
		
			$lines++;
			//$line = trim($line, '"');
			$line = str_replace("\r", "", $line);
			$line = str_replace("'", "\'", $line);
		
			$linearray = quotesplit($line);
			
			foreach($linearray as $l) {
				$l = trim($l, '"');
			}
			
			//print_r($linearray);
			
			if ($linearray[8] != '') {
				if (strpos($linearray[8], 'http://') === false)
					$linearray[8] = 'http://'.$linearray[8];
			}
			
			if (strlen($linearray[5] > 5))
				$linearray[5] = substr($linearray[5], 0, 5);
				
			if ($linearray[6] != '') {
				if (strpos($linearray[6], '(') === false) {
					$phone = explode('-', $linearray[6]);
					$linearray[6] = '('.$phone[0].') '.$phone[1].'-'.$phone[2];
				}
			}
			
			if ($linearray[7] != '') {
				if (strpos($linearray[7], '(') === false) {
					$fax = explode('-', $linearray[7]);
					$linearray[7] = '('.$fax[0].') '.$fax[1].'-'.$fax[2];
				}
			}
			
			if ($linearray[9] == '')
				$linearray[9] = '0';
			
			define("MAPS_HOST", "maps.google.com");
			define("KEY", "ABQIAAAARUPnkmQVF3Ef2h5dPDdAsRS6FfcgL6tnWKCG0XjBSWt-JgDDsxSLDKMNN7ubiz7zLUE44CpmKACm9g");
			
			$geocodeAddress = $linearray[1].', '.$linearray[3].', '.$linearray[4];
			//echo '<br/>';
			
			// BEGIN Geocode ======================================================
			
			$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
			$request_url = $base_url . "&q=" . urlencode($geocodeAddress);
			$xml = simplexml_load_file($request_url) or die("url not loading");
			
			$status = $xml->Response->Status->code;
			if (strcmp($status, "200") == 0) {
				// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->Response->Placemark->Point->coordinates;
				$coordinatesSplit = split(",", $coordinates);
				// Format: Longitude, Latitude, Altitude
				$linearray[10] = $coordinatesSplit[1];
				$linearray[11] = $coordinatesSplit[0];
				
				//print_r($linearray);
			
				$linemysql = implode("','", $linearray);
		
				if($addauto == 1)
					$query = "INSERT INTO $table (id, name, address, address2, city, state, zip, phone, fax, url, special, lat, lng) VALUES ('', '$linemysql');";
				else
					$query = "INSERT INTO $table (name, address, address2, city, state, zip, phone, fax, url, special, lat, lng) VALUES ('$linemysql');";
		
				//$queries .= $query . "\n";
			
				@mysql_query($query);
				//echo "executing : $query<br />";
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

		@mysql_close($con);
		
		if ($lines == 1)
			$message = urlencode("$lines record imported successfully.");
		else
			$message = urlencode(($lines - 2)." records imported successfully.");
		
		//echo "Found a total of $lines records in this csv file.\n";
		header("Location: ../../../../wp-admin/admin.php?page=Manage%20Database&message=$message");
		exit();

	}
}

function quotesplit($s)
{
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