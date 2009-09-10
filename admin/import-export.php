<?php
/*
SimpleMap Plugin
import-export.php: Displays the Import/Export admin page
*/

?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	
});
</script>

<div class="wrap">
	<h2><?php _e('SimpleMap: Import/Export CSV', 'SimpleMap'); ?></h2>
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.', 'SimpleMap').'</a></p></div>';
	
	?>

	<div id="dashboard-widgets-wrap" class="clear">

		<div id='dashboard-widgets' class='metabox-holder'>
		
			<div class='postbox-container' style='max-width: 800px;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<div class="postbox">
		
						<h3><?php _e('Import From File', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 0 10px 10px 10px;">
						
							<h4><?php _e('Preparing Your CSV File', 'SimpleMap'); ?></h4>
							
							<p><?php _e('To ensure that your data is formatted properly, please download an export of your database below and paste your new data into that file. The columns should be in the following order (with or without a header row):', 'SimpleMap'); ?></p>
							
							<p><em><?php _e('Name, Address, Address Line 2, City, State/Province, Country, ZIP/Postal Code, Phone, Fax, URL, Category, Description, Special (1 or 0), Latitude, Longitude', 'SimpleMap'); ?><br />
							<small><?php _e('If you have a header row in your CSV, it must have all the field names in lowercase English:', 'SimpleMap'); ?> </em><strong>name,address,address2,city,state,country,zip,phone,fax,url,category,description,special,lat,lng</strong></small></p>
							
							<p><?php _e('If your CSV values are enclosed by double quotation marks ( " ), be sure to escape any double quotation marks from within the fields before you import the CSV. You can escape a quotation mark by preceding it with a backslash ( \\ ).', 'SimpleMap') ?></p>
						
							<h4><?php _e('Importing Your CSV File', 'SimpleMap'); ?></h4>
							
							<p><?php _e('If you have more than 100 records to import, it is best to do one of the following:', 'SimpleMap'); ?></p>
							
							<ol>
								<li><?php _e('Geocode your own data before importing it'); ?></li>
								<li><?php _e('Split your file into multiple files with no more than 100 lines each'); ?></li>
							</ol>
							
							<p><?php printf(__('Geocoding your own data will allow you to import thousands of records very quickly. If your locations need to be geocoded by SimpleMap, any file with more than 100 records might stall your server. %s Resources for geocoding your own locations can be found here.%s', 'SimpleMap'), '<a href="http://groups.google.com/group/Google-Maps-API/web/resources-non-google-geocoders" target="_blank">', '</a>'); ?></p>
							
							<p><?php _e('If you are importing a file you exported from SimpleMap (and haven\'t changed since), be sure to check the box below since the locations are already geocoded.', 'SimpleMap'); ?></p>
						
							<form name="import_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php" enctype="multipart/form-data" class="inabox">
								<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (2 * 1024 * 1024); ?>" />
								<input type="hidden" name="action" value="import" />
								
								<p style="margin-top: 0;"><label for="uploadedfile"><?php _e('File to import (maximum size 2MB):', 'SimpleMap'); ?></label><input type="file" style="padding-left: 10px; border: none; font-size: 0.9em;" id="uploadedfile" name="uploadedfile" />
								<br /><br />
								<input type="checkbox" id="manual_latlng" name="manual_latlng" value="1" /> <label for="manual_latlng"><?php _e('Check this box if the locations in the file are already geocoded.', 'SimpleMap'); ?></label>
								</p>
								<input type="submit" class="button-primary" value="<?php _e('Import CSV File', 'SimpleMap'); ?>" />
							
							</form>
							
							<p style="color: #777; font: italic 1.1em Georgia;"><?php _e('Importing a file may take several seconds; please be patient.', 'SimpleMap'); ?></p>
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<div class="postbox">
		
						<h3><?php _e('Export To File', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 10px;">
					
							<form name="export_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php">
							
								<input type="hidden" name="action" value="export" />
								<input type="submit" class="button-primary" value="<?php _e('Export Database to CSV File', 'SimpleMap'); ?>" />
						
							</form>
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
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