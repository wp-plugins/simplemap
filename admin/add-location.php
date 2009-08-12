<?php
/*
SimpleMap Plugin
add-location.php: Displays the Add Location admin page
*/
?>

<div class="wrap">
	<h2><?php _e('SimpleMap: Add Location'); ?></h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.').'</a></p></div>';
	?>
	
	<?php
	if (isset($_GET['added'])) {
		$added = stripslashes($_GET['added']);
		echo '<div id="message" class="updated fade"><p><strong>'.$added.'</strong> '.__('added successfully.').'</p></div>';
	}
	?>
	<form method="post" action="<?php echo $this->plugin_url; ?>actions/location-process.php">
		<input type="hidden" name="action" value="add" />
	<?php wp_nonce_field('update-options'); ?>
	
		<table class="form-table">
		
		<tr valign="top">
			<th scope="row"><label for="store_name"><?php _e('Name'); ?></label></th>
			<td><input type="text" name="store_name" id="store_name" size="27" value="" /></td>
		</tr>
		 
		<tr valign="top">
			<th scope="row"><label for="store_address"><?php _e('Address'); ?></label></th>
			<td><input type="text" name="store_address" id="store_address" size="27" value="" />
			<br /><input type="text" name="store_address2" size="27" value="" /></td>
		</tr>
		 
		<tr valign="top">
			<th scope="row"><label for="store_city"><?php _e('City, State, Zip'); ?></label></th>
			<td><input type="text" name="store_city" id="store_city" value="" size="13" />
			<select name="store_state">
				<?php
				include ("../wp-content/plugins/simplemap/includes/states-array.php");
				foreach ($state_list as $key => $value) {
					$selected = '';
					if ($key == $options['default_state'])
						$selected = ' selected="selected"';
					echo "<option value='$key'$selected>$key</option>\n";
				}
				?>
			</select><input type="text" name="store_zip" size="6" maxlength="5" /></td>
		</tr>
		 
		<tr valign="top">
			<th scope="row"><label for="store_phone1"><?php _e('Phone'); ?></label></th>
			<td><input type="text" id="store_phone1" name="store_phone1" size="4" maxlength="3" value="" />
			<input type="text" name="store_phone2" size="4" maxlength="3" value="" />
			<input type="text" name="store_phone3" size="5" maxlength="4" value="" /></td>
		</tr>
		 
		<tr valign="top">
			<th scope="row"><label for="store_fax1"><?php _e('Fax'); ?></label></th>
			<td><input type="text" name="store_fax1" id="store_fax1" size="4" maxlength="3" value="" />
			<input type="text" name="store_fax2" size="4" maxlength="3" value="" />
			<input type="text" name="store_fax3" size="5" maxlength="4" value="" /></td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="store_url">URL</label></th>
			<td><input type="text" name="store_url" id="store_url" size="27" value="" />
			<br /><?php _e('Please include the'); ?> <strong>http://</strong></td>
		</tr>
		
		<?php if ($options['special_text'] != '') { ?>
			<tr valign="top">
				<th scope="row"><label for="store_special"><?php echo $options['special_text']; ?></label></th>
				<td><input type="checkbox" id="store_special" name="store_special" value="1" /></td>
			</tr>
		<?php } ?>
		
		</table>
		
		<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />
		
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Add Location') ?>" />
		</p>
	
	</form>
</div>