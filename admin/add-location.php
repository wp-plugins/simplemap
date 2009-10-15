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
		
	<?php
	$sm_page_title = __('SimpleMap: Add Location', 'SimpleMap');
	include "../wp-content/plugins/simplemap/includes/toolbar.php";
	?>
	
	<?php
	if (isset($_GET['added'])) {
		$added = stripslashes($_GET['added']);
		echo '<div id="message" class="updated fade"><p><strong>'.$added.'</strong> '.__('added successfully.', 'SimpleMap').'</p></div>';
	}
	?>
	
	<form method="post" action="<?php echo $this->plugin_url; ?>actions/location-process.php" id="new_location_form" name="new_location_form">
		<input type="hidden" name="action" value="add" />
	<?php wp_nonce_field('update-options'); ?>

		<div id='dashboard-widgets' class='metabox-holder'>
		
			<div class='postbox-container' style='max-width: 800px;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<div class="postbox">
		
		<h3><?php _e('Name and Description', 'SimpleMap'); ?></h3>
		
		<div class="inside">
		<div class="table">
		
		<table class="form-table">
			
			<tr valign="top">
				<td width="150"><label for="store_name"><?php _e('Name', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_name" id="store_name" size="30" value="" class="required" /><span id="name_error" class="hidden" style="font-weight: bold; color: #c00;">&nbsp;<?php _e('Please enter a name.', 'SimpleMap'); ?></span></td>
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
			
			<tr valign="top">
				<td width="150"><label for="store_tags"><?php _e('Tags', 'SimpleMap'); ?></label></td>
				<td><input type="text" name="store_tags" id="store_tags" size="30" value="" class="required" /></td>
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
		<p class="sub"><?php _e('You must enter either an address or a latitude/longitude. If you enter both, the address will not be geocoded and your latitude/longitude values will remain intact.', 'SimpleMap'); ?></p>
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
				<td><input type="text" name="store_state" id="store_state" value="<?php echo $options['default_state']; ?>" size="30" /></td>
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
				<br /><?php _e('Please include <strong>http://</strong>', 'SimpleMap'); ?></td>
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