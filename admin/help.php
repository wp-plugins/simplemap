<?php
/*
SimpleMap Plugin
help.php: Displays the Help admin page
*/

?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	
});
</script>

<div class="wrap">
		
	<?php
	$sm_page_title = __('SimpleMap: Help', 'SimpleMap');
	include "../wp-content/plugins/simplemap/includes/toolbar.php";
	?>
	
	<div><p><?php _e('Jump to a section:', 'SimpleMap'); ?> <a href="#displaying_your_map"><?php _e('Displaying Your Map', 'SimpleMap'); ?></a> | <a href="#general_options"><?php _e('General Options', 'SimpleMap'); ?></a> | <a href="#adding_a_location"><?php _e('Adding a Location', 'SimpleMap'); ?></a> | <a href="#everything_else"><?php _e('Everything Else', 'SimpleMap'); ?></a></p></div>

	<div id="dashboard-widgets-wrap" class="clear">

		<div id='dashboard-widgets' class='metabox-holder'>
		
			<div class='postbox-container' style='max-width: 800px;'>
			
				<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
				
					<a name="displaying_your_map"></a>
					<div class="postbox">
		
						<h3><?php _e('Displaying Your Map', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 0 10px 10px 10px;">
							
							<div class="table">
								<table class="form-table">
							
									<tr><td><?php _e('To show your map on any post or page, insert the shortcode in the body:', 'SimpleMap'); ?> <code style="font-size: 1.2em; background: #ffffe0;">[simplemap]</code></td></tr>
							
									<tr><td><?php _e('If you want only certain categories to show on a map, insert shortcode like this, where the numbers are replaced with the ID numbers of your desired categories:', 'SimpleMap'); ?> <code style="font-size: 1.2em; background: #ffffe0;">[simplemap categories=2,5,14]</code></td></tr>
									
									<tr><td><?php _e('You can place content above or below your map, just like in any other post. Note that any content placed below the map will be pushed down by the list of search results (unless you have them displaying differently with a custom theme).', 'SimpleMap'); ?></td></tr>
									
									<tr><td><?php printf(__('Configure the appearance of your map on the %s General Options page.%s', 'SimpleMap'), '<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">', '</a>'); ?></td></tr>
									
								</table>
							</div>
							
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
				
					<a name="general_options"></a>
					<div class="postbox">
		
						<h3><?php _e('General Options', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 0 10px 10px 10px;">
							
							<div class="table">
								<table class="form-table">
									
									<tr valign="top">
										<td width="150"><strong><?php _e('Starting Location', 'SimpleMap'); ?></strong></td>
										<td><?php _e('Enter the location the map should open to by default, when no location has been searched for. If you do not know the latitude and longitude of your starting location, enter the address in the provided text field and press "Geocode Address."', 'SimpleMap'); ?></td>
									</tr>
									
									<tr valign="top">
										<td width="150"><strong><?php _e('Auto-Load Database', 'SimpleMap'); ?></strong></td>
										<td>
											<?php printf(__('%s No auto-load:%s Locations will not load automatically.', 'SimpleMap'), '<strong>', '</strong>'); ?><br />
											<?php printf(__('%s Auto-load search results:%s The locations will load based on the default location, default search radius and zoom level you have set.', 'SimpleMap'), '<strong>', '</strong>'); ?><br />
											<?php printf(__('%s Auto-load all locations:%s All of the locations in your database will load at the default zoom level you have set, disregarding your default search radius. %s This option is not enabled if you have more than 100 locations in your database.%s', 'SimpleMap'), '<strong>', '</strong>', '<em>', '</em>'); ?><br /><br />
											
											<?php _e('If you leave the checkbox unchecked, then the auto-load feature will automatically move the map to the center of all the loaded locations. If you check the box, your default location will be respected regardless of the locations the map is loading.', 'SimpleMap'); ?>
										</td>
									</tr>
									
									<tr valign="top">
										<td width="150"><strong><?php _e('Special Location Label', 'SimpleMap'); ?></strong></td>
										<td><?php _e('This is meant to flag certain locations with a specific label. It shows up in the search results with a gold star next to it. Originally this was developed for an organization that wanted to highlight people that had been members for more than ten years. It could be used for something like that, or for "Favorite Spots," or "Free Wi-Fi," or anything you want. You can also leave it blank to disable it.', 'SimpleMap'); ?></td>
									</tr>
									
								</table>
							</div>
							
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<a name="adding_a_location"></a>
					<div class="postbox">
		
						<h3><?php _e('Adding a Location', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 0 10px 10px 10px;">
							
							<div class="table">
								<table class="form-table">
							
									<tr><td>
										<?php _e('To properly add a new location, you must enter one or both of the following:', 'SimpleMap'); ?><br />
										<span style="padding-left: 20px;"><?php _e('1. A full address', 'SimpleMap'); ?></span><br />
										<span style="padding-left: 20px;"><?php _e('2. A latitude and longitude', 'SimpleMap'); ?></span><br />
										<?php _e('If you enter a latitude and longitude, then the address will not be geocoded, and your custom values will be left in place. Entering an address without latitude or longitude will result in the address being geocoded before it is submitted to the database.', 'SimpleMap'); ?>
									</td></tr>
									
									<tr><td>
										<?php _e('You must also enter a name for every location.', 'SimpleMap'); ?>
									</td></tr>
									
								</table>
							</div>
							
							<div class="clear"></div>
							
						</div> <!-- inside -->
					</div> <!-- postbox -->
					
					<!-- =========================================
					==============================================
					========================================== -->
					
					<a name="everything_else"></a>
					<div class="postbox">
		
						<h3><?php _e('Everything Else', 'SimpleMap'); ?></h3>
						
						<div class="inside" style="padding: 0 10px 10px 10px;">
							
							<div class="table">
								<table class="form-table">
							
									<tr><td>
										<?php printf(__('If you have any other questions or comments, please visit the %s SimpleMap Support Forums%s. Search the forums for your problem before you post; the same issue may have been solved already by someone else.', 'SimpleMap'), '<a href="http://alisothegeek.com/forum/" target="_blank">', '</a>'); ?>
									</td></tr>
									
								</table>
							</div>
							
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