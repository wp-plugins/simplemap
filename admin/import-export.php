<?php
/*
SimpleMap Plugin
import-export.php: Displays the Import/Export admin page
*/

$options = $this->get_options();
?>

<div class="wrap">
	<h2><?php _e('SimpleMap: Import/Export CSV', 'SimpleMap'); ?></h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.', 'SimpleMap').'</a></p></div>';
	?>
	
	<form name="import_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
		<input type="hidden" name="action" value="import" />
		
		<h3><?php _e('Import From File', 'SimpleMap'); ?></h3>
		<p><span style="color: #090;"><?php _e('Importing a file may take several seconds; please be patient.', 'SimpleMap'); ?></span></p>
		
		<p><small><?php _e('To ensure that you have the correct column structure, please download an export of your database below, even if it is empty, and compare your column order to what is in the export. If you are using a spreadsheet application, be sure to remove any double quotation marks (") from your data before you export the CSV.', 'SimpleMap') ?></small></p>
		
		<?php _e('File to import:', 'SimpleMap'); ?> <input type="file" style="padding-left: 10px; border: none; font-size: 0.9em;" name="uploadedfile" />
		<br />
		<input type="submit" class="button-primary" value="<?php _e('Import', 'SimpleMap'); ?>" />
	
	</form>
	
	<p>&nbsp;</p>
	
	<form name="export_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php">
		
		<h3><?php _e('Export To File', 'SimpleMap'); ?></h3>
		
		<input type="hidden" name="action" value="export" />
		<input type="submit" class="button-primary" value="<?php _e('Export Database', 'SimpleMap'); ?>" />
	
	</form>
</div>