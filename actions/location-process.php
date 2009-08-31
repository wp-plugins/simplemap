<?php
/*
SimpleMap Plugin
location-process.php: Adds/edits/deletes a location from the database
*/

import_request_variables('pg', 'bcl_');

include "../includes/connect-db.php";
include "../includes/states-array.php";
include "../includes/sminc.php";

if ($bcl_action == 'delete') {

	$query = "DELETE FROM ".$table." WHERE id = '$bcl_del_id'";
	$result = mysql_query($query) or die (mysql_error());
	header("Location: {$_SERVER['HTTP_REFERER']}&message=".urlencode(__('Record deleted.', 'SimpleMap')));
	exit();

}

else if ($bcl_action == 'delete_all') {

	$query = "DELETE FROM ".$table;
	$result = mysql_query($query) or die (mysql_error());
	header("Location: {$_SERVER['HTTP_REFERER']}&message=".urlencode(__('Database cleared.', 'SimpleMap')));
	exit();
	
}

else {
	
	$bcl_store_description = htmlspecialchars($bcl_store_description);
	
	if (strpos($bcl_store_url, 'http://') === false && $bcl_store_url != '')
		$bcl_store_url = 'http://'.$bcl_store_url;
		
	if (!isset($bcl_store_category))
		$bcl_store_category = '';
	
	isset($bcl_store_special) ? $bcl_store_special = 1 : $bcl_store_special = 0;
	
	isset($bcl_special_text_exists) ? $options_specialtext = true : $options_specialtext = false;

	// Get existing address from database to see if we need to re-do the geocoding
	$result = mysql_query("SELECT address, city, state, country FROM ".$table." WHERE id = '$bcl_del_id'");
	while ($row = mysql_fetch_array($result)) {
		$prev_address = $row['address'];
		$prev_city = $row['city'];
		$prev_state = $row['state'];
		$prev_country = $row['country'];
	}
	
	// Only geocode if the address has changed
	if ($prev_address != $bcl_store_address || $prev_city != $bcl_store_city || $prev_state != $bcl_store_state || $prev_country != $bcl_store_country) {
	
		define("MAPS_HOST", "maps.google.com");
		define("KEY", $bcl_api_key);
		
		$geocodeAddress = "$bcl_store_address, $bcl_store_city";
		if ($bcl_store_state != 'none')
			$geocodeAddress .= ", $bcl_store_state";
		$geocodeAddress .= ", $bcl_store_country";
		
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
			$bcl_store_lat = $coordinatesSplit[1];
			$bcl_store_lng = $coordinatesSplit[0];
		}
		else {
			// failure to geocode
			$geocode_pending = false;
			echo "Address " . $geocodeAddress . " failed to geocode. ";
			echo "Received status " . $status . "<br/>\n";
		}
		
		// END Geocode ======================================================
	}
	
	$esc_store_name =		mysql_real_escape_string(($bcl_store_name));
	$esc_store_address =	mysql_real_escape_string(($bcl_store_address));
	$esc_store_address2 =	mysql_real_escape_string(($bcl_store_address2));
	$esc_store_city =		mysql_real_escape_string(($bcl_store_city));
	$esc_store_state =		mysql_real_escape_string(($bcl_store_state));
	$esc_store_country =	mysql_real_escape_string(($bcl_store_country));
	$esc_store_zip =		mysql_real_escape_string(($bcl_store_zip));
	$esc_store_phone =		mysql_real_escape_string(($bcl_store_phone));
	$esc_store_fax =		mysql_real_escape_string(($bcl_store_fax));
	$esc_store_url =		mysql_real_escape_string(($bcl_store_url));
	$esc_store_category =	mysql_real_escape_string(($bcl_store_category));
	$esc_store_description= mysql_real_escape_string($bcl_store_description);
	
	if ($bcl_action == 'edit' || $bcl_action == 'inline-save') {
		$query = "UPDATE $table SET
					name = '$esc_store_name', address = '$esc_store_address', address2 = '$esc_store_address2', city = '$esc_store_city', state = '$esc_store_state', country = '$esc_store_country', zip = '$esc_store_zip', phone = '$esc_store_phone', fax = '$esc_store_fax', url = '$esc_store_url', description = '$esc_store_description', category = '$esc_store_category', special = '$bcl_store_special', lat = '$bcl_store_lat', lng = '$bcl_store_lng'
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
			$bcl_store_category = stripslashes($bcl_store_category);
			$bcl_store_description = stripslashes($bcl_store_description);
		?>
			<tr id='post-<?php echo $bcl_store_id; ?>' class='<?php echo $bcl_altclass; ?>author-self status-publish iedit' valign="top">
					<!-- <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="1" /></th> -->
					
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $bcl_store_name; ?></span></strong>
						<div class="row-actions">
							<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this post inline"><?php _e('Quick Edit', 'SimpleMap'); ?></a> | </span>
							<span class='delete'><a class='submitdelete' title='Delete this location' href='../wp-content/plugins/simplemap/actions/location-process.php?action=delete&amp;del_id=<?php echo $bcl_store_id; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($bcl_store_name); ?>\'?');"><?php _e('Delete', 'SimpleMap'); ?></a></span>
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
						<div class="store_country"><?php echo $bcl_store_country; ?></div>
						<div class="store_phone"><?php echo $bcl_store_phone; ?></div>
						<div class="store_fax"><?php echo $bcl_store_fax; ?></div>
						<div class="store_url"><?php echo $bcl_store_url; ?></div>
						<div class="store_description"><?php echo $bcl_store_description; ?></div>
						<div class="store_category"><?php echo $bcl_store_category; ?></div>
						<div class="store_lat"><?php echo $bcl_store_lat; ?></div>
						<div class="store_lng"><?php echo $bcl_store_lng; ?></div>
						
						<?php if ($bcl_special_text_exists == 1) { ?>
							<div class="store_special"><?php echo $bcl_store_special; ?></div></div>
						<?php } ?>
					</td>
					
					<td>
						<span class="row_address"><?php echo $bcl_store_address."</span>";
						if ($bcl_store_address2)
							echo "<br /><span class='row_address2'>".$bcl_store_address2."</span>";
						echo "<br /><span class='row_city'>$bcl_store_city<span> ";
						if ($bcl_store_state != 'none')
							echo "<span class='row_state'>$bcl_store_state</span> ";
						echo "<span class='row_zip'>$bcl_store_zip</span>";
						echo "<br /><span class='row_country'>".strtoupper($country_list[$bcl_store_country])."</span>"; ?>
					</td>
					
					<td><span class="row_phone">
						<?php echo $bcl_store_phone."</span>";
						if ($bcl_store_fax)
							echo "<br/>".__('Fax:', 'SimpleMap')." <span class='row_fax'>".$bcl_store_fax."</span>";
						if ($bcl_store_url)
							echo "<br/><span class='row_url'>".$bcl_store_url."</span>"; ?>
					</td>
					
					<td>
						<span class="row_category"><?php echo $bcl_store_category; ?></span>
					
					</td>
					
					<td>
						<span class="row_description"><?php echo nl2br(html_entity_decode($bcl_store_description)); ?></span>
					</td>
					
					<?php if ($bcl_special_text_exists == 1) { ?>
						<td><span class="row_special">
							<?php if ($bcl_store_special == 1) { echo "&#x2713;"; } ?>
						</span></td>
					<?php } ?>
					
				</tr>
			<?php
		}
	}
	else if ($bcl_action == 'add') {
		$query = "INSERT INTO $table
					(name ,address, address2, city, state, zip, country, phone, fax, url, description, category, special, lat, lng) VALUES
					('$esc_store_name', '$esc_store_address', '$esc_store_address2', '$esc_store_city', '$esc_store_state', '$esc_store_zip', '$esc_store_country', '$esc_store_phone', '$esc_store_fax', '$esc_store_url', '$esc_store_description', '$esc_store_category', '$bcl_store_special', '$bcl_store_lat', '$bcl_store_lng')";
		
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


?>