<?php
/*
SimpleMap Plugin
import-export.php: Displays the Import/Export admin page
*/

$options = $this->get_options();
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.postbox-container').css({'width': '59%'});
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
		
			<div class='postbox-container' style='width:49%;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<div class="postbox">
		
		<h3><?php _e('Import From File', 'SimpleMap'); ?></h3>
		
		<div class="inside" style="padding: 0 10px 10px 10px;">
		
		<h4><?php _e('Preparing Your CSV File', 'SimpleMap'); ?></h4>
		
		<p><?php _e('The best way to correctly format your CSV file:', 'SimpleMap'); ?></p>
		
		<ol>
			<li>Export your database below (even if it is empty)</li>
			<li>Paste your data into that CSV file</li>
			<li>Re-import that CSV file</li>
		</ol>
		
		<p><?php printf(__('%s Importing large CSV files may stall your server. To prevent this, please only import 200 records at a time.%s If you need to split up your data, create multiple CSV files with no more than 200 lines each and upload them one at a time.', 'SimpleMap'), '<strong style="color: #c00;">', '</strong>'); ?></p>
		
		<p><?php _e('If you are using a spreadsheet application, be sure to remove any double quotation marks (") from your data before you export the CSV from the application.', 'SimpleMap') ?></p>
		
		<p><span style="color: #c00; font: italic 1.1em Georgia;"><?php _e('Importing a file may take several seconds; please be patient.', 'SimpleMap'); ?></span></p>
	
	<form name="import_form" method="post" action="<?php echo $this->plugin_url; ?>actions/csv-process.php" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
		<input type="hidden" name="action" value="import" />
		
		<?php _e('File to import:', 'SimpleMap'); ?> <input type="file" style="padding-left: 10px; border: none; font-size: 0.9em;" name="uploadedfile" />
		<br />
		<input type="submit" class="button-primary" value="<?php _e('Import', 'SimpleMap'); ?>" />
	
	</form>
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
		<input type="submit" class="button-primary" value="<?php _e('Export Database', 'SimpleMap'); ?>" />
	
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