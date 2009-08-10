<?php
/*
SimpleMap Plugin
import-export.php: Displays the Import/Export admin page
*/

$options = $this->get_options();
?>

<div class="wrap">
	<h2>SimpleMap: Import/Export CSV</h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>You must enter an API key for your domain. <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simple-map/simple-map.php">Enter a key on the General Options page.</a></p></div>';
	?>
	
	<form name="import_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
		<input type="hidden" name="action" value="import" />
		
		<h3>Import From File</h3>
		<p><em>Importing a file may take several seconds; please be patient.</em></p>
		
		File to import: <input type="file" name="uploadedfile" />
		<br />
		<input type="submit" class="button-primary" value="Import" />
	
	</form>
	
	<p>&nbsp;</p>
	
	<form name="export_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php">
		
		<h3>Export To File</h3>
		
		<input type="hidden" name="action" value="export" />
		<input type="submit" class="button-primary" value="Export Database" />
	
	</form>
</div>