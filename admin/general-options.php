<?php
/*
SimpleMap Plugin
general-options.php: Displays the General Options admin page
*/

$themes1 = readStyles('../wp-content/plugins/simplemap/styles');
$themes2 = array();

if (file_exists('../wp-content/plugins/simplemap-styles'))
	$themes2 = readStyles('../wp-content/plugins/simplemap-styles');

function readStyles($dir) {
	$themes = array();
	if ($handle = opendir($dir)) {
	    while (false !== ($file = readdir($handle))) {
	        if ($file != "." && $file != ".." && $file != ".svn") {
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

	<h2><?php _e('General Options', 'SimpleMap'); ?></h2>

	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="http://code.google.com/apis/maps/signup.html" target="_blank">'.__('Click here to sign up for a Google Maps API key.', 'SimpleMap').'</a></p></div>';
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
			<th scope="row"><label for="api_key"><?php _e('Google Maps API Key', 'SimpleMap'); ?></label></th>
			<td>
				<input type="text" name="api_key" id="api_key" size="60" value="<?php echo $api_key; ?>" /><br />
				<small><em><a href="http://code.google.com/apis/maps/signup.html" title="Sign up for a Google Maps API key" target="_blank"><?php _e('Click here', 'SimpleMap'); ?></a> <?php _e('to sign up for a Google Maps API key for your domain.', 'SimpleMap'); ?></em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="map_width"><?php _e('Map Size', 'SimpleMap'); ?></label></th>
			<td>
				<label for="map_width" style="display: inline-block; width: 60px;"><?php _e('Width:', 'SimpleMap'); ?> </label>
				<input type="text" name="map_width" id="map_width" size="13" value="<?php echo $map_width; ?>" /><br />
				<label for="map_height" style="display: inline-block; width: 60px;"><?php _e('Height:', 'SimpleMap'); ?> </label>
				<input type="text" name="map_height" id="map_height" size="13" value="<?php echo $map_height; ?>" /><br />
				<small><em><?php _e('Enter a numeric value with CSS units, such as', 'SimpleMap'); ?> </em><strong>100%</strong><em> <?php _e('or', 'SimpleMap'); ?> </em><strong>500px</strong><em>.</em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="default_lat"><?php _e('Starting Location', 'SimpleMap'); ?></label></th>
			<td>
				<label for="default_lat" style="display: inline-block; width: 60px;"><?php _e('Latitude:', 'SimpleMap'); ?> </label>
				<input type="text" name="default_lat" id="default_lat" size="13" value="<?php echo $default_lat; ?>" /><br />
				<label for="default_lng" style="display: inline-block; width: 60px;"><?php _e('Longitude:', 'SimpleMap'); ?> </label>
				<input type="text" name="default_lng" id="default_lng" size="13" value="<?php echo $default_lng; ?>" />
				<p><small><em><?php _e('Enter the location the map should open to by default, when no location has been searched for. For example, if your locations are mostly in the same city, you might want to start centered on that city.', 'SimpleMap'); ?> <a href="http://www.getlatlon.com/" target="_blank"><?php _e('Click here', 'SimpleMap'); ?></a> <?php _e('to find the latitude and longitude of an address.', 'SimpleMap'); ?></em></small></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="units"><?php _e('Distance Units', 'SimpleMap'); ?></label></th>
			<td>
				<select name="units" id="units">
					<?php
					unset($selected_units);
					$selected_units[$units] = ' selected="selected"';
					?>
					<option value="mi"<?php echo $selected_units['mi']; ?>><?php _e('Miles', 'SimpleMap'); ?></option>
					<option value="km"<?php echo $selected_units['km']; ?>><?php _e('Kilometers', 'SimpleMap'); ?></option>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="default_radius"><?php _e('Default Search Radius', 'SimpleMap'); ?></label></th>
			<td>
				<select name="default_radius" id="default_radius">
					<?php
					include ("../wp-content/plugins/simplemap/includes/search-radii-array.php");
					foreach ($search_radii as $value) {
						$r = (int)$value;
						echo "<option value='$value'".$selected_radius[$r].">$value $units</option>\n";
					}
					?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="zoom_level"><?php _e('Default Zoom Level', 'SimpleMap'); ?></label></th>
			<td>
				<select name="zoom_level" id="zoom_level">
					<?php
					for ($i = 1; $i <= 19; $i++) {
						echo "<option value=\"$i\"".$selected_zoom[$i].">$i</option>\n";
					}
					?>
				</select>&nbsp;
				<small><em><?php _e('1 is the most zoomed out (the whole world is visible) and 19 is the most zoomed in.', 'SimpleMap'); ?></em></small>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="map_type"><?php _e('Default Map Type', 'SimpleMap'); ?></label></th>
			<td>
				<div class="radio-thumbnail<?php echo $selected_type_div["G_NORMAL_MAP"]; ?>">
					<label style="display: block;" for="map_type_normal">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-normal.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Normal map', 'SimpleMap'); ?><br />
						<input type="radio" style="border: none;" name="map_type" id="map_type_normal" value="G_NORMAL_MAP"<?php echo $selected_type["G_NORMAL_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_SATELLITE_MAP"]; ?>">
					<label style="display: block;" for="map_type_satellite">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-satellite.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Satellite map', 'SimpleMap'); ?><br />
						<input type="radio" style="border: none;" name="map_type" id="map_type_satellite" value="G_SATELLITE_MAP"<?php echo $selected_type["G_SATELLITE_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_HYBRID_MAP"]; ?>">
					<label style="display: block;" for="map_type_hybrid">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-hybrid.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Hybrid map', 'SimpleMap'); ?><br />
						<input type="radio" style="border: none;" name="map_type" id="map_type_hybrid" value="G_HYBRID_MAP"<?php echo $selected_type["G_HYBRID_MAP"]; ?> />
					</label>
				</div>
				
				<div class="radio-thumbnail<?php echo $selected_type_div["G_PHYSICAL_MAP"]; ?>">
					<label style="display: block;" for="map_type_terrain">
						<img src="<?php echo $this->plugin_url; ?>images/map-type-terrain.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Terrain map', 'SimpleMap'); ?><br />
						<input type="radio" style="border: none;" name="map_type" id="map_type_terrain" value="G_PHYSICAL_MAP"<?php echo $selected_type["G_PHYSICAL_MAP"]; ?> />
					</label>
				</div>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="default_state"><?php _e('Default Location State', 'SimpleMap'); ?></label></th>
			<td>
				<select name="default_state" id="default_state">
					<?php
					include ("../wp-content/plugins/simplemap/includes/states-array.php");
					foreach ($state_list as $key => $value) {
						$selected = '';
						if ($key == $options['default_state'])
							$selected = ' selected="selected"';
						echo "<option value='$key'$selected>$key</option>\n";
					}
					?>
				</select><br />
				<small><em><?php _e('If most of your locations are in the same state, choose that state here to make adding new locations easier.', 'SimpleMap'); ?></em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="special_text"><?php _e('Special Location Label', 'SimpleMap'); ?></label></th>
			<td>
				<input type="text" name="special_text" id="special_text" size="30" value="<?php echo $special_text; ?>" /><br />
				<small><em><?php _e('If you want to distinguish certain locations (i.e. Ten-Year Members, Most Popular, etc.) then enter the label for them here. Leave it blank to disable this feature.', 'SimpleMap'); ?></em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="autoload"><?php _e('Autoload Address', 'SimpleMap'); ?></label></th>
			<td>
				<input type="text" name="autoload" id="autoload" size="30" value="<?php echo $autoload; ?>" /><br />
				<small><em><?php _e('Enter an address, city, or zip code here if you want the map to automatically show all locations in that area.', 'SimpleMap'); ?></em></small>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="map_stylesheet"><?php _e('Theme', 'SimpleMap'); ?></label></th>
			<td>
				<select name="map_stylesheet" id="map_stylesheet">
					<?php
					unset($selected_style);
					$selected_style[$map_stylesheet] = ' selected="selected"';
					
					echo '<optgroup label="'.__('Default Themes', 'SimpleMap').'">'."\n";
					foreach ($themes1 as $file => $name) {
						$file_full = 'simplemap/styles/'.$file;
						echo '<option value="'.$file_full.'"'.$selected_style[$file_full].'>'.$name.'</option>'."\n";
					}
					echo '</optgroup>'."\n";
					
					if (!empty($themes2)) {
						echo '<optgroup label="'.__('Custom Themes', 'SimpleMap').'">'."\n";
						foreach ($themes2 as $file => $name) {
							$file_full = 'simplemap-styles/'.$file;
							echo '<option value="'.$file_full.'"'.$selected_style[$file_full].'>'.$name.'</option>'."\n";
						}
						echo '</optgroup>'."\n";
					}
					?>
				</select><br />
				<small><em><?php _e('To add your own theme, upload your own CSS file to a new directory in your plugins folder called', 'SimpleMap'); ?> </em><strong>simplemap-styles</strong><em>. <?php _e('To give it a name, use the following header in the top of your stylesheet:', 'SimpleMap'); ?></em></small><br />
<pre style="color: #060;">/*
Theme Name: THEME_NAME_HERE
*/</pre>

			</td>
		</tr>
		
		<tr valign="middle">
			<th scope="row"><label for="powered_by"><?php _e('SimpleMap Link', 'SimpleMap'); ?></label></th>
			<td>
				<?php
				$powered_by_checked = '';
				if ($powered_by == 'show')
					$powered_by_checked = ' checked="checked"';
				?>
				<label for="powered_by"><input type="checkbox" name="powered_by" id="powered_by" value="1"<?php echo $powered_by_checked; ?> /> <?php _e('Show the "Powered by SimpleMap" link', 'SimpleMap'); ?></label>
			</td>
		</tr>
		
		</table>
		
		<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />
		
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Options', 'SimpleMap') ?>" />
		</p>
	
	</form>
	
</div>