<?php
/*
SimpleMap Plugin
general-options.php: Displays the General Options admin page
*/

global $wpdb;
$db_table_name = $this->table;
$db_cat_table_name = $this->cat_table;

$domains_list = array(
	'United States' => '.com',
	'Austria' => '.at',
	'Australia' => '.com.au',
	'Bosnia and Herzegovina' => '.com.ba',
	'Belgium' => '.be',
	'Brazil' => '.com.br',
	'Canada' => '.ca',
	'Switzerland' => '.ch',
	'Czech Republic' => '.cz',
	'Germany' => '.de',
	'Denmark' => '.dk',
	'Spain' => '.es',
	'Finland' => '.fi',
	'France' => '.fr',
	'Italy' => '.it',
	'Japan' => '.jp',
	'Netherlands' => '.nl',
	'Norway' => '.no',
	'New Zealand' => '.co.nz',
	'Poland' => '.pl',
	'Russia' => '.ru',
	'Sweden' => '.se',
	'Taiwan' => '.tw',
	'United Kingdom' => '.co.uk'
);

$order1 = __('City/Town', 'SimpleMap').', '.__('State/Province', 'SimpleMap').', '.__('Zip/Postal Code', 'SimpleMap');
$order2 = __('Zip/Postal Code', 'SimpleMap').', '.__('City/Town', 'SimpleMap').', '.__('State/Province', 'SimpleMap');

$address_format_list = array(
	'city state zip' => "$order1",
	'zip city state' => "$order2"
);

$count = (int)$wpdb->get_var("SELECT COUNT(*) FROM $db_table_name");
unset($disabled);
$disabledmsg = '';

if ($count > 100) {
	if ($autoload == 'all') {
		echo '<!-- Autoload All was selected, but there are more than 100 locations. -->';
		$autoload = 'some';
		unset($selected_autoload);
		$selected_autoload[$autoload] = ' selected="selected"';
		
		$options['autoload'] = 'some';
		update_option($this->db_option, $options);
	}
	$disabledmsg = sprintf(__('%s Auto-load all locations %s is disabled because you have more than 100 locations in your database.', 'SimpleMap'), '<strong>', '</strong>');
	$disabled['all'] = ' disabled="disabled"';
}

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

<script type="text/javascript">
jQuery(document).ready(function($) {
	if ($(document).width() < 1300) {
		$('.postbox-container').css({'width': '99%'});
	}
	else {
		$('.postbox-container').css({'width': '49%'});
	}
	
	if ($('#autoload').val() == 'none') {
		$('#lock_default_location').attr('checked', false);
		$('#lock_default_location').attr('disabled', true);
		$('#lock_default_location_label').addClass('disabled');
	}
	
	$('#autoload').change(function() {
		if ($(this).val() != 'none') {
			$('#lock_default_location').attr('disabled', false);
			$('#lock_default_location_label').removeClass('disabled');
		}
		else {
			$('#lock_default_location').attr('checked', false);
			$('#lock_default_location').attr('disabled', true);
			$('#lock_default_location_label').addClass('disabled');
		}
	});
	
	$('#address_format').siblings().addClass('hidden');
	if ($('#address_format').val() == 'town, province postalcode')
		$('#order_1').removeClass('hidden');
	else if ($('#address_format').val() == 'town province postalcode')
		$('#order_2').removeClass('hidden');
	else if ($('#address_format').val() == 'town-province postalcode')
		$('#order_3').removeClass('hidden');
	else if ($('#address_format').val() == 'postalcode town-province')
		$('#order_4').removeClass('hidden');
	else if ($('#address_format').val() == 'postalcode town, province')
		$('#order_5').removeClass('hidden');
	else if ($('#address_format').val() == 'postalcode town')
		$('#order_6').removeClass('hidden');
	else if ($('#address_format').val() == 'town postalcode')
		$('#order_7').removeClass('hidden');
	
	$('#address_format').change(function() {
		$(this).siblings().addClass('hidden');
		if ($(this).val() == 'town, province postalcode')
			$('#order_1').removeClass('hidden');
		else if ($(this).val() == 'town province postalcode')
			$('#order_2').removeClass('hidden');
		else if ($(this).val() == 'town-province postalcode')
			$('#order_3').removeClass('hidden');
		else if ($(this).val() == 'postalcode town-province')
			$('#order_4').removeClass('hidden');
		else if ($(this).val() == 'postalcode town, province')
			$('#order_5').removeClass('hidden');
		else if ($(this).val() == 'postalcode town')
			$('#order_6').removeClass('hidden');
		else if ($(this).val() == 'town postalcode')
			$('#order_7').removeClass('hidden');
	});
	
	// #autoload, #lock_default_location
});
</script>

<div class="wrap">
		
	<?php
	$sm_page_title = __('SimpleMap: General Options', 'SimpleMap');
	include "../wp-content/plugins/simplemap/includes/toolbar.php";
	?>
	
	<div id="dashboard-widgets-wrap" class="clear">
	
	<form method="post" action="<?php echo $action_url; ?>">
		<input type="hidden" name="submitted" value="1" />
		<?php wp_nonce_field('simplemap-nonce'); ?>

		<div id='dashboard-widgets' class='metabox-holder'>
		
			<div class='postbox-container' style='width:49%;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<div class="postbox">
						
						<h3><?php _e('Location Defaults', 'SimpleMap'); ?></h3>
						
						<div class="inside">
							<p class="sub"><?php _e('If most of your locations are in the same area, choose the country and state/province here to make adding new locations easier.', 'SimpleMap'); ?></p>
							
							<div class="table">
								<table class="form-table">
								
									<tr valign="top">
										<td width="150"><label for="default_domain"><?php _e('Google Maps Domain', 'SimpleMap'); ?></label></td>
										<td>
											<select name="default_domain" id="default_domain">
												<?php
												foreach ($domains_list as $key => $value) {
													$selected = '';
													if ($value == $options['default_domain'])
														$selected = ' selected="selected"';
													echo "<option value='$value'$selected>$key ($value)</option>\n";
												}
												?>
											</select>
										</td>
									</tr>
			
									<tr valign="top">
										<td width="150"><label for="default_country"><?php _e('Default Country', 'SimpleMap'); ?></label></td>
										<td>
											<select name="default_country" id="default_country">
												<?php
												foreach ($country_list as $key => $value) {
													$selected = '';
													if ($key == $options['default_country'])
														$selected = ' selected="selected"';
													echo "<option value='$key'$selected>$value</option>\n";
												}
												?>
											</select>
										</td>
									</tr>
									
									<tr valign="top">
										<td scope="row"><label for="default_state"><?php _e('Default State/Province', 'SimpleMap'); ?></label></td>
										<td><input type="text" name="default_state" id="default_state" size="30" value="<?php echo $default_state; ?>" /></td>
									</tr>
								
									<tr valign="top">
										<td width="150"><label for="address_format"><?php _e('Address Format', 'SimpleMap'); ?></label></td>
										<td>
											<select id="address_format" name="address_format">
												<option value="town, province postalcode"<?php echo $selected_address_format['town, province postalcode']; ?> /><?php echo '['.__('City/Town', 'SimpleMap').'], ['.__('State/Province', 'SimpleMap').']&nbsp;&nbsp;['.__('Zip/Postal Code', 'SimpleMap').']'; ?>

												<option value="town province postalcode"<?php echo $selected_address_format['town province postalcode']; ?> /><?php echo '['.__('City/Town', 'SimpleMap').']&nbsp;&nbsp;['.__('State/Province', 'SimpleMap').']&nbsp;&nbsp;['.__('Zip/Postal Code', 'SimpleMap').']'; ?>
												
												<option value="town-province postalcode"<?php echo $selected_address_format['town-province postalcode']; ?> /><?php echo '['.__('City/Town', 'SimpleMap').'] - ['.__('State/Province', 'SimpleMap').']&nbsp;&nbsp;['.__('Zip/Postal Code', 'SimpleMap').']'; ?>
												
												<option value="postalcode town-province"<?php echo $selected_address_format['postalcode town-province']; ?> /><?php echo '['.__('Zip/Postal Code', 'SimpleMap').']&nbsp;&nbsp;['.__('City/Town', 'SimpleMap').'] - ['.__('State/Province', 'SimpleMap').']'; ?>
												
												<option value="postalcode town, province"<?php echo $selected_address_format['postalcode town, province']; ?> /><?php echo '['.__('Zip/Postal Code', 'SimpleMap').']&nbsp;&nbsp;['.__('City/Town', 'SimpleMap').'], ['.__('State/Province', 'SimpleMap').']'; ?>
												
												<option value="postalcode town"<?php echo $selected_address_format['postalcode town']; ?> /><?php echo '['.__('Zip/Postal Code', 'SimpleMap').']&nbsp;&nbsp;['.__('City/Town', 'SimpleMap').']'; ?>
												
												<option value="town postalcode"<?php echo $selected_address_format['town postalcode']; ?> /><?php echo '['.__('City/Town', 'SimpleMap').']&nbsp;&nbsp;['.__('Zip/Postal Code', 'SimpleMap').']'; ?>
											</select>
											<span class="hidden" id="order_1"><br /><?php _e('Example', 'SimpleMap'); ?>: Minneapolis, MN 55403</span>
											<span class="hidden" id="order_2"><br /><?php _e('Example', 'SimpleMap'); ?>: Minneapolis MN 55403</span>
											<span class="hidden" id="order_3"><br /><?php _e('Example', 'SimpleMap'); ?>: S&atilde;o Paulo - SP 85070</span>
											<span class="hidden" id="order_4"><br /><?php _e('Example', 'SimpleMap'); ?>: 85070 S&atilde;o Paulo - SP</span>
											<span class="hidden" id="order_5"><br /><?php _e('Example', 'SimpleMap'); ?>: 46800 Puerto Vallarta, JAL</span>
											<span class="hidden" id="order_6"><br /><?php _e('Example', 'SimpleMap'); ?>: 126 25&nbsp;&nbsp;Stockholm</span>
											<span class="hidden" id="order_7"><br /><?php _e('Example', 'SimpleMap'); ?>: London&nbsp;&nbsp;EC1Y 8SY</span>
										</td>
									</tr>
								
								</table>
								
							</div> <!-- table -->
		
							<p class="submit">
								<input type="submit" class="button-primary" value="<?php _e('Save Options', 'SimpleMap') ?>" /><br /><br />
							</p>
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<div class="postbox">
						
						<h3><?php _e('Map Configuration', 'SimpleMap'); ?></h3>
						
						<div class="inside">
							<p class="sub"><?php printf(__('See %s the Help page%s for an explanation of these options.', 'SimpleMap'), '<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.urlencode(__('SimpleMap Help', 'SimpleMap')).'">','</a>&nbsp;'); ?></p>
							
							<div class="table">
								<table class="form-table">
									
									<tr valign="top">
										<td width="150"><label for="api_key"><?php _e('Google Maps API Key', 'SimpleMap'); ?></label></td>
										<td>
											<input type="text" name="api_key" id="api_key" size="50" value="<?php echo $api_key; ?>" /><br />
											<small><em><?php printf(__('%s Click here%s to sign up for a Google Maps API key for your domain.', 'SimpleMap'), '<a href="'.$api_link.'">', '</a>'); ?></em></small>
										</td>
									</tr>
									
									<tr valign="top">
										<td width="150"><label for="default_lat"><?php _e('Starting Location', 'SimpleMap'); ?></label></td>
										<td>
											<label for="default_lat" style="display: inline-block; width: 60px;"><?php _e('Latitude:', 'SimpleMap'); ?> </label>
											<input type="text" name="default_lat" id="default_lat" size="13" value="<?php echo $default_lat; ?>" /><br />
											<label for="default_lng" style="display: inline-block; width: 60px;"><?php _e('Longitude:', 'SimpleMap'); ?> </label>
											<input type="text" name="default_lng" id="default_lng" size="13" value="<?php echo $default_lng; ?>" />
											
											<p><input type="text" name="default_address" id="default_address" size="30" value="" />&nbsp;<a class="button" onclick="codeAddress();" href="#">Geocode Address</a></p>
										</td>
									</tr>
									
									<tr valign="top">
										<td><label for="units"><?php _e('Distance Units', 'SimpleMap'); ?></label></td>
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
										<td><label for="default_radius"><?php _e('Default Search Radius', 'SimpleMap'); ?></label></td>
										<td>
											<select name="default_radius" id="default_radius">
												<?php
												foreach ($search_radii as $value) {
													$r = (int)$value;
													echo "<option value='$value'".$selected_radius[$r].">$value $units</option>\n";
												}
												?>
											</select>
										</td>
									</tr>
									
									<tr valign="top">
										<td><label for="results_limit"><?php _e('Number of Results to Display', 'SimpleMap'); ?></label></td>
										<td>
											<select name="results_limit" id="results_limit">
												<option value="0"<?php echo $selected_results_limit[0]; ?>>No Limit</option>
												<?php
												for ($i = 5; $i <= 50; $i += 5) {
													echo "<option value=\"$i\"".$selected_results_limit[$i].">$i</option>\n";
												}
												?>
											</select><br />
											<small><em><?php _e('Select "No Limit" to display all results within the search radius.', 'SimpleMap'); ?></em></small>
										</td>
									</tr>
									
									<tr valign="top">
										<td><label for="autoload"><?php _e('Auto-Load Database', 'SimpleMap'); ?></label></td>
										<td>
											<select name="autoload" id="autoload">
												<option value="none"<?php echo $selected_autoload['none']; ?>><?php _e('No auto-load', 'SimpleMap'); ?></option>
												<option value="some"<?php echo $selected_autoload['some']; ?>><?php _e('Auto-load search results', 'SimpleMap'); ?></option>
												<option value="all"<?php echo $selected_autoload['all'].$disabled['all']; ?>><?php _e('Auto-load all locations', 'SimpleMap'); ?></option>
											</select>
											<?php if ($disabledmsg != '') { echo '<br /><small><em>'.$disabledmsg.'</small></em>'; } ?>
											<?php
											$lock_default_checked = '';
											if ($lock_default_location == 'lock')
												$lock_default_checked = ' checked="checked"';
											?>
											<br /><label for="lock_default_location" id="lock_default_location_label"><input type="checkbox" name="lock_default_location" id="lock_default_location" value="1"<?php echo $lock_default_checked; ?> /> <?php _e('Stick to default location set above', 'SimpleMap'); ?></label>
										</td>
									</tr>
									
									<tr valign="top">
										<td><label for="zoom_level"><?php _e('Default Zoom Level', 'SimpleMap'); ?></label></td>
										<td>
											<select name="zoom_level" id="zoom_level">
												<?php
												for ($i = 1; $i <= 19; $i++) {
													echo "<option value=\"$i\"".$selected_zoom[$i].">$i</option>\n";
												}
												?>
											</select><br />
											<small><em><?php _e('1 is the most zoomed out (the whole world is visible) and 19 is the most zoomed in.', 'SimpleMap'); ?></em></small>
										</td>
									</tr>
									
									<tr valign="top">
										<td><label for="special_text"><?php _e('Special Location Label', 'SimpleMap'); ?></label></td>
										<td>
											<input type="text" name="special_text" id="special_text" size="30" value="<?php echo $special_text; ?>" />
										</td>
									</tr>
								
								</table>
								
							</div> <!-- table -->
		
							<p class="submit">
								<input type="submit" class="button-primary" value="<?php _e('Save Options', 'SimpleMap') ?>" /><br /><br />
							</p>
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					</div>
				</div>
					
				<div class='postbox-container' style='width:49%;'>
					<div id='side-sortables' class='meta-box-sortables ui-sortable'>
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<div class="postbox" >
						
						<h3><?php _e('Map Style Defaults', 'SimpleMap'); ?></h3>
						
						<div class="inside">
							<p class="sub"><?php printf(__('To insert SimpleMap into a post or page, type this shortcode in the body: %s', 'SimpleMap'), '<code>[simplemap]</code>'); ?></p>
							
							<div class="table">
								<table class="form-table">
									
									<tr valign="top">
										<td width="150"><label for="map_width"><?php _e('Map Size', 'SimpleMap'); ?></label></td>
										<td>
											<label for="map_width" style="display: inline-block; width: 60px;"><?php _e('Width:', 'SimpleMap'); ?> </label>
											<input type="text" name="map_width" id="map_width" size="13" value="<?php echo $map_width; ?>" /><br />
											<label for="map_height" style="display: inline-block; width: 60px;"><?php _e('Height:', 'SimpleMap'); ?> </label>
											<input type="text" name="map_height" id="map_height" size="13" value="<?php echo $map_height; ?>" /><br />
											<small><em><?php printf(__('Enter a numeric value with CSS units, such as %s or %s.', 'SimpleMap'), '</em><code>100%</code><em>', '</em><code>500px</code><em>'); ?></em></small>
										</td>
									</tr>
							
									<tr valign="top">
										<td><label for="map_type"><?php _e('Default Map Type', 'SimpleMap'); ?></label></td>
										<td>
											<div class="radio-thumbnail<?php echo $selected_type_div["G_NORMAL_MAP"]; ?>">
												<label style="display: block;" for="map_type_normal">
													<img src="<?php echo $this->plugin_url; ?>images/map-type-normal.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Road map', 'SimpleMap'); ?><br />
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
										<td><label for="map_stylesheet"><?php _e('Theme', 'SimpleMap'); ?></label></td>
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
											<small><em><?php printf(__('To add your own theme, upload your own CSS file to a new directory in your plugins folder called %s simplemap-styles%s.  To give it a name, use the following header in the top of your stylesheet:', 'SimpleMap'), '</em><code>', '</code><em>'); ?></em></small><br />
											<div style="margin-left: 20px;">
												<code style="color: #060; background: none;">/*<br />Theme Name: THEME_NAME_HERE<br />*/</code>
											</div>
							
										</td>
									</tr>
									
									<tr valign="middle">
										<td><label for="display_search"><?php _e('Display Search Form', 'SimpleMap'); ?></label></td>
										<td>
											<?php
											$display_search_checked = '';
											if ($display_search == 'show')
												$display_search_checked = ' checked="checked"';
											?>
											<label for="display_search"><input type="checkbox" name="display_search" id="display_search" value="1"<?php echo $display_search_checked; ?> /> <?php _e('Show the search form above the map', 'SimpleMap'); ?></label>
										</td>
									</tr>
									
									<tr valign="middle">
										<td><label for="powered_by"><?php _e('SimpleMap Link', 'SimpleMap'); ?></label></td>
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
							</div> <!-- table -->
		
							<p class="submit" align="right">
								<input type="submit" class="button-primary" value="<?php _e('Save Options', 'SimpleMap') ?>" />&nbsp;&nbsp;
							</p>
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
				
				</div> <!-- meta-box-sortables -->
			</div> <!-- postbox-container -->
		</div> <!-- dashboard-widgets -->
		</form>
		
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right; margin-right: 2%;">
			<input type="hidden" name="cmd" value="_s-xclick"/>
			<input type="hidden" name="hosted_button_id" value="7382728"/>
			<input type="image" src="http://alisothegeek.com/ag_donate.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		
		<div class="clear">
		</div>
	</div><!-- dashboard-widgets-wrap -->
</div> <!-- wrap -->
