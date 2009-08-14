<?php
/*
SimpleMap Plugin
location-process.php: Adds/edits/deletes a location from the database
*/

import_request_variables('pg', 'bcl_');

include "../includes/connect-db.php";
include "../includes/sminc.php";

if ($bcl_action == 'delete') {

	$query = "DELETE FROM ".$table." WHERE id = '$bcl_del_id'";
	$result = mysql_query($query) or die (mysql_error());
	header("Location: {$_SERVER['HTTP_REFERER']}");
	exit();

}

else if ($bcl_action == 'delete_all') {

	$query = "DELETE FROM ".$table;
	$result = mysql_query($query) or die (mysql_error());
	header("Location: {$_SERVER['HTTP_REFERER']}");
	exit();
	
}

else {

	define("MAPS_HOST", "maps.google.com");
	define("KEY", $options['api_key']);
	
	if (strpos($bcl_store_url, 'http://') === false && $bcl_store_url != '')
		$bcl_store_url = 'http://'.$bcl_store_url;
	
	($bcl_store_phone1) ? $bcl_store_phone = "($bcl_store_phone1) $bcl_store_phone2-$bcl_store_phone3" : $bcl_store_phone = '';
	
	($bcl_store_fax1) ? $bcl_store_fax = "($bcl_store_fax1) $bcl_store_fax2-$bcl_store_fax3" : $bcl_store_fax = '';
	
	(isset($bcl_store_special)) ? $bcl_store_special = 1 : $bcl_store_special = 0;
	
	$geocodeAddress = "$bcl_store_address, $bcl_store_city, $bcl_store_state";
	
	// BEGIN Geocode ======================================================
	
	$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;
	$request_url = $base_url . "&q=" . urlencode($geocodeAddress);
	//$xml = simplexml_load_file($request_url) or die("url not loading"); // THROWING URL FILE-ACCESS ERROR FOR REMOTE FILES
	$request_string = curl_get_contents($request_url);
	$xml = simplexml_load_string($request_string) or die("URL not loading");
	
	$status = $xml->Response->Status->code;
	if (strcmp($status, "200") == 0) {
		// Successful geocode
		$geocode_pending = false;
		$coordinates = $xml->Response->Placemark->Point->coordinates;
		$coordinatesSplit = split(",", $coordinates);
		// Format: Longitude, Latitude, Altitude
		$bcl_store_lat = $coordinatesSplit[1];
		$bcl_store_lng = $coordinatesSplit[0];
	
		if ($bcl_action == 'edit' || $bcl_action == 'inline-save') {
			$query = "UPDATE $table SET
						name = '$bcl_store_name', address = '$bcl_store_address', address2 = '$bcl_store_address2', city = '$bcl_store_city', state = '$bcl_store_state', zip = '$bcl_store_zip', phone = '$bcl_store_phone', fax = '$bcl_store_fax', url = '$bcl_store_url', special = '$bcl_store_special', lat = '$bcl_store_lat', lng = '$bcl_store_lng'
						WHERE id = '$bcl_store_id'";
			
			$result = mysql_query($query);
			if (!$result) {
				die("Invalid query: " . mysql_error() . "<br />\nQuery: " . $query . "<br />\n");
			}
			else { 
				$bcl_store_name = stripslashes($bcl_store_name);
				$bcl_store_address = stripslashes($bcl_store_address);
				$bcl_store_address2 = stripslashes($bcl_store_address2);
				$bcl_store_city = stripslashes($bcl_store_city);
			?>
				<tr id='post-<?php echo $bcl_store_id; ?>' class='<?php echo $bcl_altclass; ?>author-self status-publish iedit' valign="top">
					<!-- <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="1" /></th> -->
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $bcl_store_name; ?></span></strong>
						<div class="row-actions">
						<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this post inline">Quick Edit</a> | </span>
						<span class='delete'><a class='submitdelete' title='Delete this location' href='../wp-content/plugins/simplemap/actions/location-process?action=delete&amp;del_id=<?php echo $bcl_store_id; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($bcl_store_name); ?>\'?');">Delete</a></span>
					</div>
						<div class="hidden" id="inline_<?php echo $bcl_store_id; ?>">
						<div class="store_id"><?php echo $bcl_store_id; ?></div>
						<div class="altclass"><?php echo $bcl_altclass; ?></div>
						<div class="store_name"><?php echo $bcl_store_name; ?></div>
						<div class="store_address"><?php echo $bcl_store_address; ?></div>
						<div class="store_address2"><?php echo $bcl_store_address2; ?></div>
						<div class="store_city"><?php echo $bcl_store_city; ?></div>
						<div class="store_state"><?php echo $bcl_store_state; ?></div>
						<div class="store_zip"><?php echo $bcl_store_zip; ?></div>
						<div class="store_phone1"><?php echo substr($bcl_store_phone, 1, 3); ?></div>
						<div class="store_phone2"><?php echo substr($bcl_store_phone, 6, 3); ?></div>
						<div class="store_phone3"><?php echo substr($bcl_store_phone, -4); ?></div>
						<div class="store_fax1"><?php echo substr($bcl_store_fax, 1, 3); ?></div>
						<div class="store_fax2"><?php echo substr($bcl_store_fax, 6, 3); ?></div>
						<div class="store_fax3"><?php echo substr($bcl_store_fax, -4); ?></div>
						<div class="store_url"><?php echo $bcl_store_url; ?></div>
						<div class="store_special"><?php echo $bcl_store_special; ?></div></div>
					</td>
					<td>
						<span class="row_address"><?php echo $bcl_store_address."</span>";
						if ($bcl_store_address2)
							echo "<br /><span class='row_address2'>".$bcl_store_address2."</span>";
						echo "<br /><span class='row_city'>$bcl_store_city<span>, 
						<span class='row_state'>$bcl_store_state</span> 
						<span class='row_zip'>$bcl_store_zip</span>"; ?>
					</td>
					<td><span class="row_phone">
						<?php echo $bcl_store_phone."</span>";
						if ($bcl_store_fax)
							echo "<br/>Fax: <span class='row_fax'>".$bcl_store_fax."</span>"; ?>
					</td>
					<td><span class="row_url"><?php echo $bcl_store_url; ?></span></td>
					<td><span class="row_special">
						<?php if ($bcl_store_special == 1) { echo "&#x2713;"; } ?>
					</span></td>
				</tr>
				<?php
			}
		}
		else if ($bcl_action == 'add') {
			$query = "INSERT INTO $table
						(name, address, address2, city, state, zip, phone, fax, url, special, lat, lng) VALUES
						('$bcl_store_name', '$bcl_store_address', '$bcl_store_address2', '$bcl_store_city', '$bcl_store_state', '$bcl_store_zip', '$bcl_store_phone', '$bcl_store_fax', '$bcl_store_url', '$bcl_store_special', '$bcl_store_lat', '$bcl_store_lng')";
			
			$result = mysql_query($query);
			if (!$result) {
				die("Invalid query: " . mysql_error() . "<br />\nQuery: " . $query . "<br />\n");
			}
			else {
				$urlname = urlencode(stripslashes($bcl_store_name));
				header("Location: {$_SERVER['HTTP_REFERER']}&added=$urlname");
				exit();
			}
		}
	}
	else {
		// failure to geocode
		$geocode_pending = false;
		echo "Address " . $geocodeAddress . " failed to geocode. ";
		echo "Received status " . $geocodeAddress . "
	\n";
	}
	
	// END Geocode ======================================================
	
}

?>