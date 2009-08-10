<?php
/*
SimpleMap Plugin
general-options.php: Displays the General Options admin page
*/

$themes1 = readStyles('../wp-content/plugins/simple-map/styles');
$themes2 = array();

if (file_exists('../wp-content/plugins/simple-map-styles'))
	$themes2 = readStyles('../wp-content/plugins/simple-map-styles');

function readStyles($dir) {
	$themes = array();
	if ($handle = opendir($dir)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != "..") {
	        	$theme_data = implode('', file($dir.'/'.$file));
	
				$name = '';
				if (preg_match('|Theme Name:(.*)$|mi', $theme_data, $matches))
					$name = _cleanup_header_comment($matches[1]);
				else
					$name = basename($file);
					
				$themes[$file] = $name;
	        }
	    }
	    closedir($handle);
	}
	return($themes);
}
?>

<div class="wrap">

	<h2>General Options</h2>

	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>You must enter an API key for your domain. <a href="http://code.google.com/apis/maps/signup.html" target="_blank">Click here to sign up for a Google Maps API key.</a></p></div>';
	?>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="7382728">
		<input type="image" src="http://alisothegeek.com/ag_donate.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="float: right;">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
		
	<form method="post" action="<?php echo $action_url; ?>">
		<input type="hidden" name="submitted" value="1" />
		<?php wp_nonce_field('simplemap-nonce'); ?>
	
		<table class="form-table">
		
		<tr valign="top">
			<th scope="row"><label for="api_key">Google Maps API Key</label></th>
			<td>
				<input type="text" name="api_key" id="api_key" size="60" value="<?php echo $api_key; ?>" /><br />
				<small><em><a href="http://code.google.com/apis/maps/signup.html" title="Sign up for a Google Maps API key" target="_blank">Click here</a> to sign up for a Google Maps API key for your domain.</em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="map_width">Map Size</label></th>
			<td>
				<label for="map_width" style="display: inline-block; width: 60px;">Width: </label>
				<input type="text" name="map_width" id="map_width" size="13" value="<?php echo $map_width; ?>" /><br />
				<label for="map_height" style="display: inline-block; width: 60px;">Height: </label>
				<input type="text" name="map_height" id="map_height" size="13" value="<?php echo $map_height; ?>" /><br />
				<small><em>Enter a numeric value with CSS units, such as </em><strong>100%</strong><em> or </em><strong>500px</strong><em>.</em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="default_lat">Starting Location</label></th>
			<td>
				<label for="default_lat" style="display: inline-block; width: 60px;">Latitude: </label>
				<input type="text" name="default_lat" id="default_lat" size="13" value="<?php echo $default_lat; ?>" /><br />
				<label for="default_lng" style="display: inline-block; width: 60px;">Longitude: </label>
				<input type="text" name="default_lng" id="default_lng" size="13" value="<?php echo $default_lng; ?>" />
				<p><small><em>Enter the location the map should open to by default, when no location has been searched for. For example, if your locations are mostly in the same city, you might want to start centered on that city. <a href="http://www.getlatlon.com/" target="_blank">Click here</a> to find the latitude and longitude of an address.</em></small></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="zoom_level">Default Zoom Level</label></th>
			<td>
				<select name="zoom_level" id="zoom_level">
					<?php
					for ($i = 1; $i <= 19; $i++) {
						echo "<option value=\"$i\"".$selected_zoom[$i].">$i</option>\n";
					}
					?>
				</select>&nbsp;
				<small><em>1 is the most zoomed out (the whole world is visible) and 19 is the most zoomed in.</em></small>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="map_type">Default Map Type</label></th>
			<td>
				<div class="radio-thumbnail<?php echo $selected_type_div["G_NORMAL_MAP"]; ?>">
					<label style="display: block;" for="map_type_normal">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-normal.jpg" width="100" height="100" style="border: 1px solid #999;" /><br />Normal map<br />
						<input type="radio" name="map_type" id="map_type_normal" value="G_NORMAL_MAP"<?php echo $selected_type["G_NORMAL_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_SATELLITE_MAP"]; ?>">
					<label style="display: block;" for="map_type_satellite">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-satellite.jpg" width="100" height="100" style="border: 1px solid #999;" /><br />Satellite map<br />
						<input type="radio" name="map_type" id="map_type_satellite" value="G_SATELLITE_MAP"<?php echo $selected_type["G_SATELLITE_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_HYBRID_MAP"]; ?>">
					<label style="display: block;" for="map_type_hybrid">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-hybrid.jpg" width="100" height="100" style="border: 1px solid #999;" /><br />Hybrid map<br />
						<input type="radio" name="map_type" id="map_type_hybrid" value="G_HYBRID_MAP"<?php echo $selected_type["G_HYBRID_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_PHYSICAL_MAP"]; ?>">
					<label style="display: block;" for="map_type_terrain">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-terrain.jpg" width="100" height="100" style="border: 1px solid #999;" /><br />Terrain map<br />
						<input type="radio" name="map_type" id="map_type_terrain" value="G_PHYSICAL_MAP"<?php echo $selected_type["G_PHYSICAL_MAP"]; ?> />
					</label>
				</div>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="default_state">Default Location State</label></th>
			<td>
				<select name="default_state" id="default_state">
					<?php
					include ("../wp-content/plugins/simple-map/includes/states-array.php");
					foreach ($state_list as $key => $value) {
						$selected = '';
						if ($key == $options['default_state'])
							$selected = ' selected="selected"';
						echo "<option value='$key'$selected>$key</option>\n";
					}
					?>
				</select><br />
				<small><em>If most of your locations are in the same state, choose that state here to make adding new locations easier.</em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="special_text">Special Location Label</label></th>
			<td>
				<input type="text" name="special_text" id="special_text" size="30" value="<?php echo $special_text; ?>" /><br />
				<small><em>If you want to distinguish certain locations (i.e. Ten-Year Members, Most Popular, etc.) then enter the label for them here. Leave it blank to disable this feature.</em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="map_stylesheet">Theme</label></th>
			<td>
				<select name="map_stylesheet" id="map_stylesheet">
					<?php
					unset($selected_style);
					$selected_style[$map_stylesheet] = ' selected="selected"';
					
					echo '<optgroup label="Default Themes">'."\n";
					foreach ($themes1 as $file => $name) {
						$file_full = 'simple-map/styles/'.$file;
						echo '<option value="'.$file_full.'"'.$selected_style[$file_full].'>'.$name.'</option>'."\n";
					}
					echo '</optgroup>'."\n";
					
					if (!empty($themes2)) {
						echo '<optgroup label="Custom Themes">'."\n";
						foreach ($themes2 as $file => $name) {
							$file_full = 'simple-map-styles/'.$file;
							echo '<option value="'.$file_full.'"'.$selected_style[$file_full].'>'.$name.'</option>'."\n";
						}
						echo '</optgroup>'."\n";
					}
					?>
				</select><br />
				<small><em>To add your own theme, upload your own CSS file to a new directory in your plugins folder called </em><strong>simple-map-styles</strong><em>. To give it a name, use the following header in the top of your stylesheet:</em></small><br />
<pre style="color: #060;">/*
Theme Name: THEME_NAME_HERE
*/</pre>

			</td>
		</tr>
		
		</table>
		
		<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />
		
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Options') ?>" />
		</p>
	
	</form>
	
</div>