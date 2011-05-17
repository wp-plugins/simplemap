<?php
if ( !class_exists( 'SM_Options' ) ){
	class SM_Options{
		
		// update options of form submission
		function sm_options() {
			add_action( 'admin_init', array( &$this, 'update_options' ) );
		}
		
		// Processes Options form if loaded
		function update_options(){
			global $simple_map, $sm_locations;

			// Delete all SimpleMap data.
			if ( isset( $_GET['sm-action'] )  && 'delete-simplemap' == $_GET['sm-action'] ) {
				
				// Confirm we have both permisssion to do this and we have intent to do this.
				if ( current_user_can( 'manage_options' ) && check_admin_referer( 'delete-simplemap' ) ) {
					
					// Delete locations
					if ( $locations = query_posts( array( 'post_type' => 'sm-location', 'posts_per_page' => -1 ) ) ) {
						
						// Delete posts (and therby postmeta as well). Second arg bypasses trash
						foreach ( $locations as $key => $location ) {
							set_time_limit( 20 ); 
							wp_delete_post( $location->ID, true );
						}
					}
					
					// Delete categories and tags
					$taxonomies = array( 'sm-category', 'sm-tag', 'sm-day', 'sm-time' );
					$args = array( 'hide_empty' => 0 );
					if ( $terms = get_terms( $taxonomies, $args ) ) {
						foreach( $terms as $key => $term ) {
							wp_delete_term( $term->term_id, $term->taxonomy );
						}
						
					}
										
					// Delete Options
					if ( get_option( 'SimpleMap_options' ) ) {
						delete_option( 'SimpleMap_options' );
					}
					
					do_action( 'sm-delete-all-data' );
					
					wp_safe_redirect( admin_url( 'admin.php?page=simplemap' ) );
				}
			}

			$options = get_option( 'SimpleMap_options' );
			$default = $simple_map->get_default_options();

			// Update Options if form was submitted or if WordPress options doesn't exist yet.
			if ( isset( $_POST['sm_general_options_submitted'] ) || ! isset( $options['map_width'] ) ) {
				
				if ( isset( $_POST['sm_general_options_submitted'] ) )
					check_admin_referer( 'sm-general-options' );
				
				$new_options = array();
				
				// Validate POST Options
				$new_options['api_key'] 				= ( isset( $_POST['api_key'] ) && !empty( $_POST['api_key'] ) ) ? $_POST['api_key'] : '';
				$new_options['map_width'] 				= ( isset( $_POST['map_width'] ) && !empty( $_POST['map_width'] ) ) ? $_POST['map_width'] : $default['map_width'];
				$new_options['map_height'] 				= ( isset( $_POST['map_height'] ) && !empty( $_POST['map_height'] ) ) ? $_POST['map_height'] : $default['map_height'];
				$new_options['default_lat'] 			= ( isset( $_POST['default_lat'] ) && !empty( $_POST['default_lat'] ) ) ? $_POST['default_lat'] : $default['default_lat'] ;
				$new_options['default_lng'] 			= ( isset( $_POST['default_lng'] ) && !empty( $_POST['default_lng'] ) ) ? $_POST['default_lng'] : $default['default_lng'] ;
				$new_options['zoom_level'] 				= ( isset( $_POST['zoom_level'] ) ) ? absint( $_POST['zoom_level'] ) : $default['zoom_level'] ;
				$new_options['default_radius'] 			= ( isset( $_POST['default_radius'] ) && !empty( $_POST['default_radius'] ) ) ? absint( $_POST['default_radius'] ) : $default['default_radius'] ;
				$new_options['map_type'] 				= ( isset( $_POST['map_type'] ) && !empty( $_POST['map_type'] ) ) ? $_POST['map_type'] : $default['map_type'];
				$new_options['special_text'] 			= ( isset( $_POST['special_text'] ) ) ? $_POST['special_text'] : $default['special_text'];
				$new_options['default_state'] 			= ( isset( $_POST['default_state'] ) && !empty( $_POST['default_state'] ) ) ? $_POST['default_state'] : $default['default_state'];
				$new_options['default_country']			= ( isset( $_POST['default_country'] ) && !empty( $_POST['default_country'] ) ) ? esc_attr( $_POST['default_country'] ) : $default['default_country'];
				$new_options['default_domain'] 			= ( isset( $_POST['default_domain'] ) && !empty( $_POST['default_domain'] ) ) ? $_POST['default_domain'] : $default['default_domain'];
				$new_options['address_format'] 			= ( isset( $_POST['address_format'] ) && !empty( $_POST['address_format'] ) ) ? $_POST['address_format'] : $default['address_format'];
				$new_options['map_stylesheet'] 			= ( isset( $_POST['map_stylesheet'] ) && !empty( $_POST['map_stylesheet'] ) ) ? $_POST['map_stylesheet'] : $default['map_stylesheet'];
				$new_options['units'] 					= ( isset( $_POST['units'] ) && !empty( $_POST['units'] ) ) ? $_POST['units'] : $default['units'];
				$new_options['results_limit'] 			= ( isset( $_POST['results_limit'] ) ) ? absint( $_POST['results_limit'] ) : $default['results_limit'];
				$new_options['autoload'] 				= ( isset( $_POST['autoload'] ) && !empty( $_POST['autoload'] ) ) ? $_POST['autoload'] : $default['autoload'];
				$new_options['map_pages'] 				= isset( $_POST['map_pages'] ) ? $_POST['map_pages'] : $default['map_pages'];
				$new_options['lock_default_location'] 	= ( isset( $_POST['lock_default_location'] ) && !empty( $_POST['lock_default_location'] ) ) ? true : $default['lock_default_location'];
				$new_options['powered_by'] 				= ( isset( $_POST['powered_by'] ) && 'on' == $_POST['powered_by'] ) ? 1 : 0;
				$new_options['display_search'] 			= ( isset( $_POST['display_search'] ) && !empty( $_POST['display_search'] ) ) ? $_POST['display_search'] : $default['display_search'];

				$new_options = apply_filters( 'sm-new-general-options', $new_options, $default );
				
				if ( $new_options !== $default && update_option( 'SimpleMap_options', $new_options ) ) {
					do_action( 'sm-general-options-updated' );
					wp_redirect( admin_url( 'admin.php?page=simplemap&sm-msg=1' ) );
					die();

				}
			}
			
		}
		
		// Prints the options page
		function print_page(){
			global $simple_map, $wpdb;
			$options = get_option( 'SimpleMap_options' );
			if ( !isset( $options['api_key'] ) )
				$options['api_key'] = '';
				
			extract( $options );
			
			// Set Autoload Vars
			$count = count( $wpdb->get_col( "SELECT ID FROM `" . $wpdb->posts . "` WHERE post_type = 'sm-location' AND post_status = 'publish' LIMIT 250" ) );

			if ( $count == 250 ) {
				$disabled_autoload = false; // let it happen. we're limiting to 500 in the query
				$disabledmsg = sprintf( __( 'You have to many locations to auto-load them all. Only the closest %d will be displayed if auto-load all is selected.', 'SimpleMap' ), '250' );
			} else {
				$disabled_autoload = false;
				$disabledmsg = ''; 
			}
			
			// Extract styles
			$themes1 = $themes2 = array();

			if ( file_exists( SIMPLEMAP_PATH . '/inc/styles' ) )
				$themes1 = $this->read_styles( SIMPLEMAP_PATH . '/inc/styles' );
			
			if ( file_exists( WP_PLUGIN_DIR . '/simplemap-styles' ) )
				$themes2 = $this->read_styles( WP_PLUGIN_DIR . '/simplemap-styles' );
			
			$themes1 = apply_filters( 'sm-general-options-themes1', $themes1 );
			$themes2 = apply_filters( 'sm-general-options-themes1', $themes2 );
			?>
			<div class="wrap">
				
				<?php
				// Title
				$sm_page_title = apply_filters( 'sm-general-options-page-title', 'SimpleMap: General Options' );
				
				// Toolbar
				$simple_map->show_toolbar( $sm_page_title );
					
				// Messages			 
				if ( isset( $_GET['sm-msg'] ) && '1' == $_GET['sm-msg'] )
					echo '<div class="updated fade"><p>'.__('SimpleMap settings saved.', 'SimpleMap').'</p></div>';
				
				?>
				
				<div id="dashboard-widgets-wrap" class="clear">
				
				<form method="post" action="">
					<input type="hidden" name="sm_general_options_submitted" value="1" />
					
					<?php wp_nonce_field( 'sm-general-options' ); ?>
			
					<?php do_action( 'sm-general-options-page-top' ); ?>
			
					<div id='dashboard-widgets' class='metabox-holder'>
					
						<?php do_action( 'sm-general-options-dash-widgets-top' ); ?>

						<div class='postbox-container' style='width:49%;'>
						
							<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
							
								<?php do_action( 'sm-general-options-normal-sortables-top' ); ?>
								
								<div class="postbox">
									
									<h3><?php _e( 'Location Defaults', 'SimpleMap' ); ?></h3>
									
									<div class="inside">
										<p class="sub"><?php _e( 'If most of your locations are in the same area, choose the country and state/province here to make adding new locations easier.', 'SimpleMap' ); ?></p>
										
										<div class="table">
											<table class="form-table">
											
												<tr valign="top">
													<td width="150"><label for="default_domain"><?php _e( 'Google Maps Domain', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="default_domain" id="default_domain">
															<?php
															foreach ( $simple_map->get_domain_options() as $key => $value ) {
																echo "<option value='" . $value . "' " . selected( $default_domain, $value ) . ">" . $key . " (" . $value . ")</option>\n";
															}
															?>
														</select>
													</td>
												</tr>
						
												<tr valign="top">
													<td width="150"><label for="default_country"><?php _e( 'Default Country', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="default_country" id="default_country">
															<?php
															foreach ( $simple_map->get_country_options() as $key => $value ) {
																echo "<option value='" . $key . "' " . selected( $default_country, $key ) . ">" . $value . "</option>\n";
															}
															?>
														</select>
													</td>
												</tr>
												
												<tr valign="top">
													<td scope="row"><label for="default_state"><?php _e( 'Default State/Province', 'SimpleMap' ); ?></label></td>
													<td><input type="text" name="default_state" id="default_state" size="30" value="<?php echo $default_state; ?>" /></td>
												</tr>
											
												<tr valign="top">
													<td width="150"><label for="address_format"><?php _e( 'Address Format', 'SimpleMap' ); ?></label></td>
													<td>
														<select id="address_format" name="address_format">
															<option value="town, province postalcode" <?php selected( $address_format, 'town, province postalcode' ); ?> /><?php echo '[' . __( 'City/Town', 'SimpleMap' ) . '], [' . __( 'State/Province', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']'; ?>
			
															<option value="town province postalcode" <?php selected( $address_format, 'town province postalcode' ); ?> /><?php echo '[' . __( 'City/Town', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'State/Province', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']'; ?>
															
															<option value="town-province postalcode" <?php selected( $address_format, 'town-province postalcode' ); ?> /><?php echo '[' . __( 'City/Town', 'SimpleMap' ) . '] - [' . __( 'State/Province', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __('Zip/Postal Code', 'SimpleMap' ) . ']'; ?>
															
															<option value="postalcode town-province" <?php selected( $address_format, 'postalcode town-province' ); ?> /><?php echo '[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'City/Town', 'SimpleMap' ) . '] - [' . __( 'State/Province', 'SimpleMap' ) . ']'; ?>
															
															<option value="postalcode town, province" <?php selected( $address_format, 'postalcode town, province' ); ?> /><?php echo '[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'City/Town', 'SimpleMap' ) . '], [' . __( 'State/Province', 'SimpleMap' ) . ']'; ?>
															
															<option value="postalcode town" <?php selected( $address_format, 'postalcode town' ); ?> /><?php echo '[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'City/Town', 'SimpleMap' ) . ']'; ?>
															
															<option value="town postalcode" <?php selected( $address_format, 'town postalcode' ); ?> /><?php echo '[' . __( 'City/Town', 'SimpleMap' ) . ']&nbsp;&nbsp;[' . __( 'Zip/Postal Code', 'SimpleMap' ) . ']'; ?>
														</select>
														<span class="hidden" id="order_1"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: Minneapolis, MN 55403</span>
														<span class="hidden" id="order_2"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: Minneapolis MN 55403</span>
														<span class="hidden" id="order_3"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: S&atilde;o Paulo - SP 85070</span>
														<span class="hidden" id="order_4"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: 85070 S&atilde;o Paulo - SP</span>
														<span class="hidden" id="order_5"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: 46800 Puerto Vallarta, JAL</span>
														<span class="hidden" id="order_6"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: 126 25&nbsp;&nbsp;Stockholm</span>
														<span class="hidden" id="order_7"><br /><?php _e( 'Example', 'SimpleMap' ); ?>: London&nbsp;&nbsp;EC1Y 8SY</span>
													</td>
												</tr>
											
											</table>
											
										</div> <!-- table -->
					
										<p class="submit">
											<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'SimpleMap' ) ?>" /><br /><br />
										</p>
										<div class="clear"></div>
										
									</div> <!-- inside -->
								</div> <!-- postbox -->
								
								
								<!-- #### MAP CONFIGURATION #### -->								
								<div class="postbox">
									
									<h3><?php _e( 'Map Configuration', 'SimpleMap' ); ?></h3>
									
									<div class="inside">
										<p class="sub"><?php printf( __( 'See %s the Help page%s for an explanation of these options.', 'SimpleMap' ), '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=simplemap-help">', '</a>&nbsp;' ); ?></p>
										
										<div class="table">
											<table class="form-table">
												
												<tr valign="top">
													<td width="150"><label for="api_key"><?php _e( 'Google Maps API Key', 'SimpleMap' ); ?></label></td>
													<td>
														<input type="text" name="api_key" id="api_key" size="50" value="<?php echo esc_attr( $api_key ); ?>" /><br />
														<small><em><?php printf( __( '%s Click here%s to sign up for a Google Maps API key for your domain.', 'SimpleMap' ), '<a href="' . $simple_map->get_api_link() . '">', '</a>'); ?></em></small>
													</td>
												</tr>
												
												<tr valign="top">
													
													<?php 
													if ( '' != $options['api_key'] ) {
														$disabled_api = false;
														$api_how_to = __( 'Type in an address, state, or zip to geocode the default location.', 'SimpleMap' );
													} else {
														$disabled_api = true;
														$api_how_to = __( 'After you enter an API Key, you can type in an address, state, or zip here to geocode the default location.', 'SimpleMap' );
													}
													?>
													
													<td width="150"><label for="default_lat"><?php _e( 'Starting Location', 'SimpleMap' ); ?></label></td>
													<td>
														<label for="default_lat" style="display: inline-block; width: 60px;"><?php _e( 'Latitude:', 'SimpleMap' ); ?> </label>
														<input type="text" name="default_lat" id="default_lat" size="13" value="<?php echo esc_attr( $default_lat ); ?>" /><br />
														<label for="default_lng" style="display: inline-block; width: 60px;"><?php _e( 'Longitude:', 'SimpleMap' ); ?> </label>
														<input type="text" name="default_lng" id="default_lng" size="13" value="<?php echo esc_attr( $default_lng ); ?>" />
														
														<p>
															<input <?php disabled( $disabled_api ); ?> type="text" name="default_address" id="default_address" size="30" value="" />&nbsp;<a class="button" <?php disabled( $disabled_api ); ?> onclick="codeAddress();return false;" href="#"><?php _e( 'Geocode Address', 'SimpleMap' ); ?></a>
															<br /><small><span class='howto'><?php echo $api_how_to; ?></span></small>
														</p>
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="units"><?php _e( 'Distance Units', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="units" id="units">
															<option value="mi" <?php selected( $units, 'mi' ); ?>><?php _e( 'Miles', 'SimpleMap' ); ?></option>
															<option value="km" <?php selected( $units, 'km' ); ?>><?php _e( 'Kilometers', 'SimpleMap' ); ?></option>
														</select>
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="default_radius"><?php _e( 'Default Search Radius', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="default_radius" id="default_radius">
															<?php
															foreach ( $simple_map->get_search_radii() as $value ) {
																$r = (int) $value;
																echo "<option value='" . esc_attr( $value ) . "' " . selected( $value, $default_radius, false ) . ">" . esc_attr( $value ) . " " . esc_attr( $units ) . "</option>\n";
															}
															?>
														</select>
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="results_limit"><?php _e( 'Number of Results to Display', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="results_limit" id="results_limit">
															<option value="0" <?php selected( $results_limit, 0 ); ?>>No Limit</option>
															<?php
															for ( $i = 5; $i <= 50; $i += 5 ) {
																echo "<option value='" . esc_attr( $i ) . "' " . selected( $results_limit, $i, false ) . ">" . esc_attr( $i ) . "</option>\n";
															}
															?>
														</select><br />
														<small></small><span class='howto'><?php _e( 'Select "No Limit" to display all results within the search radius.', 'SimpleMap' ); ?></span></small>
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="autoload"><?php _e( 'Auto-Load Database', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="autoload" id="autoload">
															<option value="none" <?php selected( $autoload, 'none' ); ?>><?php _e( 'No auto-load', 'SimpleMap' ); ?></option>
															<option value="some" <?php selected( $autoload, 'some' ); ?>><?php _e('Auto-load search results', 'SimpleMap'); ?></option>
															<option value="all" <?php selected( $autoload, 'all' );?> <?php disabled( $disabled_autoload ); ?>><?php _e('Auto-load all locations', 'SimpleMap'); ?></option>
														</select>
														<br />
														<small><em><?php _e( sprintf ( '%sNo auto-load%s shows map without any locations.%s%sAuto-load search results%s displays map based on default values for search form.%s%sAuto-load all%s ignores default search form values and loads all locations.', '<strong>', '</strong>', '<br />', '<strong>', '</strong>', '<br />', '<strong>', '</strong>' ) ); ?></em></small>
														<?php if ( $disabledmsg != '' ) { echo '<br /><small style="color:red";><em>' . $disabledmsg . '</small></em>'; } ?>

														<!--<br /><label for="lock_default_location" id="lock_default_location_label"><input type="checkbox" name="lock_default_location" id="lock_default_location" value="1" <?php checked( $lock_default_location ); ?> /> <?php _e('Stick to default location set above', 'SimpleMap'); ?></label>-->
													</td>
												</tr>
																								
												<tr valign="top">
													<td><label for="zoom_level"><?php _e('Default Zoom Level', 'SimpleMap'); ?></label></td>
													<td>
														<select name="zoom_level" id="zoom_level">
															<option value='0' <?php selected( $zoom_level, 0 ); ?> >Auto Zoom</option>
															<?php
															for ( $i = 1; $i <= 19; $i++ ) {
																echo "<option value='" . esc_attr( $i ) . "' " . selected( $zoom_level, $i ) . ">" . esc_attr( $i ) . "</option>\n";
															}
															?>
														</select><br />
														<small><em><?php _e( '1 is the most zoomed out (the whole world is visible) and 19 is the most zoomed in.', 'SimpleMap' ); ?></em></small>
													</td>
												</tr>

												<tr valign="top">
													<td><label for="special_text"><?php _e( 'Special Location Label', 'SimpleMap' ); ?></label></td>
													<td>
														<input type="text" name="special_text" id="special_text" size="30" value="<?php echo esc_attr( $special_text ); ?>" />
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="map_pages"><?php _e( 'Map Page IDs', 'SimpleMap' ); ?></label></td>
													<td>
														<input type="text" name="map_pages" id="map_pages" size="30" value="<?php echo esc_attr( $map_pages ); ?>" /><br />
														<small><em><?php _e( 'Enter the IDs of the pages/posts the map will appear on, separated by commas. The map scripts will only be loaded on those pages. Leave blank or enter 0 to load the scripts on all pages.', 'SimpleMap' ); ?></em></small>
													</td>
												</tr>
											
											</table>
											
										</div> <!-- table -->
					
										<p class="submit">
											<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'SimpleMap' ) ?>" /><br /><br />
										</p>
										<div class="clear"></div>
										
									</div> <!-- inside -->
								</div> <!-- postbox -->
								
								<?php do_action( 'sm-general-options-normal-sortables-bottom' ); ?>
								
								</div>
							</div>
								
							<div class='postbox-container' style='width:49%;'>
								
								<div id='side-sortables' class='meta-box-sortables ui-sortable'>
								
								<?php do_action( 'sm-general-options-side-sortables-top' ); ?>
								

								<!-- #### PREMIUM SUPPORT #### -->
								
								<div class="postbox" >
									
									<h3 style='color:#fff;text-shadow:0 1px 0 #000;background: #fff url( <?php echo SIMPLEMAP_URL; ?>/inc/images/blue-grad.png ) top left repeat-x;'><?php _e( 'Premium Support and Features', 'SimpleMap' ); ?></h3>
									
									<div class="inside" style='padding: 0pt 10px 10px;' >
										
										<?php
										// Check for premium support status
										global $simplemap_ps;

										if ( ! url_has_ftps_for_item( $simplemap_ps ) ) : ?>
										
											<h4><?php printf( __( 'SimpleMap Premium Support Benefits', 'SimpleMap' ), esc_attr( site_url() ) ); ?></h4>
											<p>
												<?php printf( __( 'SimpleMap now offers a premium support package for the low cost of %s per year per domain.', 'SimpleMap' ), '$30.00 USD' ); ?>
											</p>
											<p>
												<?php _e( 'By signing up for SimpleMap premium support, you help to ensure future enhancements to this excellent project as well as the following benefits:', 'SimpleMap' ); ?>
											</p>
										
											<ul style='margin-left:25px;list-style-type:disc'>
												<li><?php _e( 'Around the clock access to our extensive knowledge base and support forum from within your WordPress dashboard', 'SimpleMap' ); ?></li>
												<li><?php _e( 'Professional and timely response times to all your questions from the SimpleMap team', 'SimpleMap' ); ?></li>
												<li><?php _e( 'A 10% discount for any custom functionality you request from the SimpleMap developers', 'SimpleMap' ); ?></li>
												<li><?php _e( 'A 6-12 month advance access to new features integrated into the auto upgrade functionality of WordPress', 'SimpleMap' ); ?></li>
											</ul>
											
											<ul style='margin-left:25px;list-style-type:none'>
												<li><a href='<?php echo get_ftps_paypal_button( $simplemap_ps ); ?>'><?php _e( 'Signup Now', 'SimpleMap' ); ?></a></li>
												<li><a target='_blank' href='<?php echo get_ftps_learn_more_link( $simplemap_ps ); ?>'><?php _e( 'Learn More', 'SimpleMap' ); ?></a></li>
											</ul>
										<?php else : ?>

											<p class='howto'><?php printf( "Your premium support for <code>%s</code> was purchased on <code>%s</code> by <code>%s</code> (%s). It will remain valid for this URL until <code>%s</code>.", get_ftps_site( $simplemap_ps ), date( "F d, Y", get_ftps_purchase_date( $simplemap_ps ) ), get_ftps_name( $simplemap_ps ), get_ftps_email( $simplemap_ps ), date( "F d, Y", get_ftps_exp_date( $simplemap_ps ) ) ); ?></p>
											<p><a href='#' id='premium_help'><?php _e( 'Launch Premium Support widget', 'SimpleMap' ); ?></a> | <a target="blank" href="http://support.simplemap-plugin.com?sso=<?php echo get_ftps_sso_key( $simplemap_ps ); ?>"><?php _e( 'Visit Premium Support web site', 'SimpleMap' );?></a></p>
											<script type="text/javascript" charset="utf-8">
											  Tender = {
											    hideToggle: true,
											    sso: "<?php echo get_ftps_sso_key( $simplemap_ps ); ?>",
											    widgetToggles: [document.getElementById('premium_help')]
											  }
											</script>
											<script src="https://simplemap.tenderapp.com/tender_widget.js" type="text/javascript"></script>
										
										<?php endif; ?>
										
									</div> <!-- inside -->
								</div> <!-- postbox -->


								<!-- #### MAP STYLES #### -->
								
								<div class="postbox" >
									
									<h3><?php _e( 'Map Style Defaults', 'SimpleMap' ); ?></h3>
									
									<div class="inside">
										<p class="sub"><?php printf( __( 'To insert SimpleMap into a post or page, type this shortcode in the body: %s', 'SimpleMap' ), '<code>[simplemap]</code>' ); ?></p>
										
										<div class="table">
											<table class="form-table">
												
												<tr valign="top">
													<td width="150"><label for="map_width"><?php _e( 'Map Size', 'SimpleMap' ); ?></label></td>
													<td>
														<label for="map_width" style="display: inline-block; width: 60px;"><?php _e( 'Width:', 'SimpleMap' ); ?> </label>
														<input type="text" name="map_width" id="map_width" size="13" value="<?php echo esc_attr( $map_width ); ?>" /><br />
														<label for="map_height" style="display: inline-block; width: 60px;"><?php _e( 'Height:', 'SimpleMap' ); ?> </label>
														<input type="text" name="map_height" id="map_height" size="13" value="<?php echo esc_attr( $map_height ); ?>" /><br />
														<small><em><?php printf( __( 'Enter a numeric value with CSS units, such as %s or %s.', 'SimpleMap' ), '</em><code>100%</code><em>', '</em><code>500px</code><em>' ); ?></em></small>
													</td>
												</tr>
										
												<tr valign="top">
													<td><label for="map_type"><?php _e( 'Default Map Type', 'SimpleMap' ); ?></label></td>
													<td>
														<div class="radio-thumbnail<?php if ( 'G_NORMAL_MAP' == $map_type ) { echo ' radio-thumbnail-current'; } ?>">
															<label style="display: block;" for="map_type_normal">
																<img src="<?php echo SIMPLEMAP_URL; ?>/inc/images/map-type-normal.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Road map', 'SimpleMap'); ?><br />
																<input type="radio" style="border: none;" name="map_type" id="map_type_normal" value="G_NORMAL_MAP" <?php checked( $map_type, 'G_NORMAL_MAP' ); ?> />
															</label>
														</div>
														
														<div class="radio-thumbnail<?php if ( 'G_SATELLITE_MAP' == $map_type ) { echo ' radio-thumbnail-current'; } ?>">
															<label style="display: block;" for="map_type_satellite">
																<img src="<?php echo SIMPLEMAP_URL; ?>/inc/images/map-type-satellite.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Satellite map', 'SimpleMap'); ?><br />
																<input type="radio" style="border: none;" name="map_type" id="map_type_satellite" value="G_SATELLITE_MAP" <?php checked( $map_type, 'G_SATELLITE_MAP' ); ?> />
															</label>
														</div>
														
														<div class="radio-thumbnail<?php if ( 'G_HYBRID_MAP' == $map_type ) { echo ' radio-thumbnail-current'; } ?>">
															<label style="display: block;" for="map_type_hybrid">
																<img src="<?php echo SIMPLEMAP_URL; ?>/inc/images/map-type-hybrid.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Hybrid map', 'SimpleMap'); ?><br />
																<input type="radio" style="border: none;" name="map_type" id="map_type_hybrid" value="G_HYBRID_MAP" <?php checked( $map_type, 'G_HYBRID_MAP' ); ?> />
															</label>
														</div>
														
														<div class="radio-thumbnail<?php if ( 'G_PHYSICAL_MAP' == $map_type ) { echo ' radio-thumbnail-current'; } ?>">
															<label style="display: block;" for="map_type_terrain">
																<img src="<?php echo SIMPLEMAP_URL; ?>/inc/images/map-type-terrain.jpg" width="100" height="100" style="border: 1px solid #999;" /><br /><?php _e('Terrain map', 'SimpleMap'); ?><br />
																<input type="radio" style="border: none;" name="map_type" id="map_type_terrain" value="G_PHYSICAL_MAP" <?php checked( $map_type, 'G_PHYSICAL_MAP' ); ?> />
															</label>
														</div>
													</td>
												</tr>
												
												<tr valign="top">
													<td><label for="map_stylesheet"><?php _e( 'Theme', 'SimpleMap' ); ?></label></td>
													<td>
														<select name="map_stylesheet" id="map_stylesheet">
															<?php
															echo '<optgroup label="' . __( 'Default Themes', 'SimpleMap' ) . '">' . "\n";
															foreach ( $themes1 as $file => $name ) {
																$file_full = 'inc/styles/' . $file;
																echo '<option value="' . esc_attr( $file_full ) . '" ' . selected( $map_stylesheet, $file_full, false ) . '>' . esc_attr( $name ) . '</option>' . "\n";
															}
															echo '</optgroup>' . "\n";
															
															if (!empty($themes2)) {
																echo '<optgroup label="'.__('Custom Themes', 'SimpleMap').'">'."\n";
																foreach ($themes2 as $file => $name) {
																	$file_full = 'simplemap-styles/' . $file;
																	echo '<option value="' . esc_attr( $file_full ) . '" ' . selected( $map_stylesheet, $file_full, false ) . '>' . esc_attr( $name ) . '</option>' . "\n";
																}
																echo '</optgroup>'."\n";
															}
															?>
														</select><br />
														<small><em><?php printf( __( 'To add your own theme, upload your own CSS file to a new directory in your plugins folder called %s simplemap-styles%s.  To give it a name, use the following header in the top of your stylesheet:', 'SimpleMap' ), '</em><code>', '</code><em>' ); ?></em></small><br />
														<div style="margin-left: 20px;">
															<code style="color: #060; background: none;">/*<br />Theme Name: THEME_NAME_HERE<br />*/</code>
														</div>
										
													</td>
												</tr>
												
												<tr valign="middle">
													<td>
														<label for="display_search"><?php _e( 'Display Search Form', 'SimpleMap' ); ?></label>
													</td>
													<td>
														<label for="display_search_yes"><input type="radio" name="display_search" id="display_search_yes" value="show" <?php checked( $display_search, 'show' ); ?> /> <?php _e( 'Yes', 'SimpleMap' ); ?></label>&nbsp;&nbsp;
														<label for="display_search_no"><input type="radio" name="display_search" id="display_search_no" value="hide" <?php checked( $display_search, 'hide' ); ?> /> <?php _e( 'No', 'SimpleMap' ); ?></label><br />
													</td>
												</tr>
												
												<tr valign="middle">
													<td>
														<label for="powered_by"><?php _e('SimpleMap Link', 'SimpleMap'); ?></label>
													</td>
													<td>
														<label for="powered_by"><input type="checkbox" name="powered_by" id="powered_by" <?php checked( $powered_by ); ?> /> <?php _e( 'Show the "Powered by SimpleMap" link', 'SimpleMap' ); ?></label>
													</td>
												</tr>
											
											</table>
										</div> <!-- table -->
					
										<p class="submit" align="right">
											<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'SimpleMap' ) ?>" />&nbsp;&nbsp;
										</p>
										<div class="clear"></div>
										
									</div> <!-- inside -->
								</div> <!-- postbox -->
								
								<div class="postbox" >
									
									<h3><?php _e( 'Delete SimpleMap Data', 'SimpleMap' ); ?></h3>
									
									<div class="inside">
										<p class="sub"><span style="color:red";><?php _e( 'CAUTION! Uninstalling SimpleMap will completely delete all current locations, categories, tags and options. This is irreversible.' , 'SimpleMap' ); ?></span></p>
										<p style='text-align:center;'><a onClick="javascript:return confirm('<?php _e( 'Last chance! Pressing OK will delete all SimpleMap data.'); ?>')" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=simplemap&sm-action=delete-simplemap' ), 'delete-simplemap' ); ?>" ><?php _e( 'Clicking this link will remove all data from the database.'); ?></a></p>
									</div>
								</div>
								
								<?php do_action( 'sm-general-options-side-sortables-bottom' ); ?>
							
							</div> <!-- meta-box-sortables -->
						</div> <!-- postbox-container -->
					
						<?php do_action( 'sm-general-options-dash-widgets-bottom' ); ?>
					
					</div> <!-- dashboard-widgets -->
					</form>
					
					<p style='float:right;margin-right:25px;'><a href='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DTJBYXGQFSW64'>Donate via PayPal</a></p>
					
					<div class="clear">
					</div>
				</div><!-- dashboard-widgets-wrap -->
			</div> <!-- wrap -->
			<?php		
		}
		
		// Locate and list style options / location
		function read_styles( $dir ) {
			$themes = array();
			if ( $handle = opendir( $dir ) ) {
			    while ( false !== ( $file = readdir( $handle ) ) ) {
			        if ( $file != "." && $file != ".." && $file != ".svn" && $file != 'admin.css' ) {
			        	$theme_data = implode( '', file( $dir . '/' . $file ) );
			
						$name = '';
						if (preg_match( '|Theme Name:(.*)$|mi', $theme_data, $matches ) )
							$name = _cleanup_header_comment( $matches[1] );
						else
							$name = basename( $file );
							
						$themes[$file] = $name;
			        }
			    }
			    closedir( $handle );
			}
			return( $themes );
		}
	}
}
?>
