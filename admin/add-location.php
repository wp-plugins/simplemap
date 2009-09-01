<?php
/*
SimpleMap Plugin
add-location.php: Displays the Add Location admin page
*/

global $wpdb;
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	//$('#name_error').hide();
	$('#submit-link').click(function() {
		if ($('#store_name').val() == '') {
			$('#name_error').removeClass('hidden');
			$('#store_name').focus();
			return false;
		}
		return true;
	});
});
</script>

<div class="wrap">
	<h2><?php _e('SimpleMap: Add Location', 'SimpleMap'); ?></h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.', 'SimpleMap').'</a></p></div>';
	
	if (isset($_GET['added'])) {
		$added = stripslashes($_GET['added']);
		echo '<div id="message" class="updated fade"><p><strong>'.$added.'</strong> '.__('added successfully.', 'SimpleMap').'</p></div>';
	}
	?>
	
	<form method="post" action="<?php echo $this->plugin_url; ?>actions/location-process.php" id="new_location_form" name="new_location_form">
		<input type="hidden" name="action" value="add" />
	<?php wp_nonce_field('update-options'); ?>

		<div id='dashboard-widgets' class='metabox-holder'>
		
			<div class='postbox-container' style='width:59%;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<div class="postbox">
		
		<h3><?php _e('Name and Description', 'SimpleMap'); ?></h3>
		
		<div class="inside">
		<div class="table">
		
		<table class="form-table">
			
			<tr valign="top">
				<td width="150"><label for="store_name"><?php _e('Name', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_name" id="store_name" size="30" value="" class="required" /><span id="name_error" class="hidden" style="font-weight: bold; color: #c00;">&nbsp;Please enter a name.</span></td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_description"><?php _e('Description', 'SimpleMap'); ?></label></td>
				<td><textarea name="store_description" id="store_description" cols="31" rows="6"></textarea></td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_category"><?php _e('Category', 'SimpleMap'); ?></label></td>
				<td>
					<?php
					$all_cats = $wpdb->get_results("SELECT * FROM ".$this->cat_table, ARRAY_A);
					if ($all_cats != null) {
					?>
						<select name="store_category" id="store_category">
						<?php
						foreach ($all_cats as $cat) {
							echo '<option value="'.htmlspecialchars($cat['name']).'">'.htmlspecialchars($cat['name']).'</option>'."\n";
						}
						?>
						</select>
					<?php } else { ?>
						<small><em><?php printf(__('You can add categories from the %s General Options screen.%s', 'SimpleMap'), '<a href="admin.php?page=simplemap/simplemap.php">', '</a>'); ?></em></small>
					<?php } ?>
				</td>
			</tr>
		
		</table>
		
		</div> <!-- table -->
		<div class="clear"></div>
		</div> <!-- inside -->
		</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<div class="postbox">
		
		<h3><?php _e('Geographic Location', 'SimpleMap'); ?></h3>
		
		<div class="inside">
		<p class="sub"><?php _e('You must enter either an address or a latitude/longitude. If you enter both, the address will override the latitude/longitude.', 'SimpleMap'); ?></p>
		<div class="table">
	
		<table class="form-table">
			 
			<tr valign="top">
				<td width="150"><label for="store_address"><?php _e('Address', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_address" id="store_address" size="30" value="" /><br />
				<input type="text" name="store_address2" size="30" value="" /></td>
			</tr>
			 
			<tr valign="top">
				<td><label for="store_city"><?php _e('City/Town', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_city" id="store_city" value="" size="30" /></td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_state"><?php _e('State/Province', 'SimpleMap'); ?></label></td>
				<td>
					<select name="store_state" id="store_state">
						<option value="none">&mdash;</option>
						<optgroup label="United States">
							<?php
							foreach ($states_list as $key => $value) {
								$selected = '';
								if ($key == $options['default_state'])
									$selected = ' selected="selected"';
								echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>'."\n";
							}
							?>
						</optgroup>
						<optgroup label="Canada">
							<?php
							foreach ($canada_list as $key => $value) {
								$selected = '';
								if ($key == $options['default_state'])
									$selected = ' selected="selected"';
								echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>'."\n";
							}
							?>
						</optgroup>
						<optgroup label="Australia">
							<?php
							foreach ($australia_list as $key => $value) {
								$selected = '';
								if ($key == $options['default_state'])
									$selected = ' selected="selected"';
								echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>'."\n";
							}
							?>
						</optgroup>
					</select>
				</td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_zip"><?php _e('Zip/Postal Code', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_zip" id="store_zip" value="" size="30" maxlength="20" /></td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_country"><?php _e('Country', 'SimpleMap'); ?></label></td>
				<td>
					<select name="store_country" id="store_country">
						<?php
						foreach ($country_list as $key => $value) {
							$selected = '';
							if ($key == $options['default_country'])
								$selected = ' selected="selected"';
							echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>'."\n";
						}
						?>
					</select>
				</td>
			</tr>
			 
			<tr valign="top">
				<td><label for="store_lat"><?php _e('Latitude/Longitude', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_lat" id="store_lat" size="14" value="" />
				<input type="text" name="store_lng" id="store_lng" size="14" value="" /></td>
			</tr>
		
		</table>
		
		</div> <!-- table -->
		<div class="clear"></div>
		</div> <!-- inside -->
		</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					</div>
				</div>
					
				<div class='postbox-container' style='width:59%;'>
					<div id='side-sortables' class='meta-box-sortables ui-sortable'>
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<div class="postbox">
		
		<h3><?php _e('Miscellaneous Information', 'SimpleMap'); ?></h3>
		
		<div class="inside">
		<div class="table">
		
		<table class="form-table">
			 
			<tr valign="top">
				<td width="150"><label for="store_phone"><?php _e('Phone', 'SimpleMap'); ?></label></td>
				<td><input type="text" id="store_phone" name="store_phone" size="30" maxlength="28" value="" /></td>
			</tr>
			 
			<tr valign="top">
				<td><label for="store_fax"><?php _e('Fax', 'SimpleMap'); ?></label></td>
				<td><input type="text" id="store_fax" name="store_fax" size="30" maxlength="28" value="" /></td>
			</tr>
			
			<tr valign="top">
				<td><label for="store_url"><?php _e('URL', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_url" id="store_url" size="30" value="" />
				<br /><?php _e('Please include', 'SimpleMap'); ?> <strong>http://</strong></td>
			</tr>
			
			<?php if ($options['special_text'] != '') { ?>
			<tr valign="top">
				<td><label for="store_special"><?php echo $options['special_text']; ?></label></td>
				<td><input type="checkbox" id="store_special" name="store_special" value="1" /></td>
			</tr>
			<?php } ?>
		
		</table>
		
		</div> <!-- table -->
		<div class="clear"></div>
		</div> <!-- inside -->
		</div> <!-- postbox -->
		
		<p class="submit">
			<a class="button-primary" id="submit-link" href="javascript:codeNewAddress();"><?php _e('Add Location', 'SimpleMap') ?></a>
			<!-- <input type="submit" class="button-primary" value="<?php _e('Add Location', 'SimpleMap') ?>" /> -->
		</p>
					
					<!-- =========================================
					==============================================
					========================================== -->
				
				</div> <!-- meta-box-sortables -->
			</div> <!-- postbox-container -->
		</div> <!-- dashboard-widgets -->
		
		<div class="clear">
		</div>
	</div><!-- dashboard-widgets-wrap -->
</div> <!-- wrap -->