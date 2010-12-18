<?php
if ( !class_exists( 'SM_Import_Export' ) ){
	class SM_Import_Export{
		
		// update options of form submission
		function sm_import_export() {
			//add_action( 'admin_init', array( &$this, 'import_csv' ) );
			add_action( 'admin_init', array( &$this, 'export_csv' ) );
			add_action( 'admin_init', array( &$this, 'export_legacy_csv' ) );
			add_action( 'admin_init', array( &$this, 'delete_legacy_tables' ) );
		}
		
		// Exports a CSV file to WordPress
		function export_csv() {
			global $simple_map, $sm_locations;
			
			if ( isset( $_POST['sm-action'] ) && 'export-csv' == $_POST['sm-action'] ) {
				
				// Grab locations
				if ( $locations = query_posts( array( 'post_status' => 'publish', 'post_type' => 'sm-location', 'posts_per_page' => -1 ) ) ) {
					
					// Include CSV library
					include_once( SIMPLEMAP_PATH . '/classes/parsecsv.lib.php' );
					
					foreach( $locations as $key => $location ) {
						$cats = $tags = '';
						
						// Do Cats		
						if ( $terms = wp_get_object_terms( $location->ID, 'sm-category', array( 'fields' => 'names' ) ) )
							$cats = implode( ',', $terms );
						
						// Do Tags		
						if ( $terms = wp_get_object_terms( $location->ID, 'sm-tag', array( 'fields' => 'names' ) ) )
							$tags = implode( ',', $terms );
						
						
						$content[] = array( 
							'name' => esc_attr( $location->post_title ), 
							'address' => esc_attr( get_post_meta( $location->ID, 'location_address', true ) ),
							'address2' => esc_attr( get_post_meta( $location->ID, 'location_address2', true ) ),
							'city' => esc_attr( get_post_meta( $location->ID, 'location_city', true ) ),
							'state' => esc_attr( get_post_meta( $location->ID, 'location_state', true ) ),
							'zip' => esc_attr( get_post_meta( $location->ID, 'location_zip', true ) ),
							'country' => esc_attr( get_post_meta( $location->ID, 'location_country', true ) ),
							'phone' => esc_attr( get_post_meta( $location->ID, 'location_phone', true ) ),
							'email' => esc_attr( get_post_meta( $location->ID, 'location_email', true ) ),
							'fax' => esc_attr( get_post_meta( $location->ID, 'location_fax', true ) ),
							'url' => esc_attr( get_post_meta( $location->ID, 'location_url', true ) ),
							'description' => esc_attr( $location->post_content ),
							'category' => esc_attr( $cats ),
							'tags' => esc_attr( $tags ),
							'special' => esc_attr( get_post_meta( $location->ID, 'location_special', true ) ),
							'lat' => esc_attr( get_post_meta( $location->ID, 'location_lat', true ) ),
							'lng' => esc_attr( get_post_meta( $location->ID, 'location_lng', true ) ),
							'dateUpdated' => esc_attr( $location->post_modified )
						);
	
					}

					$csv = new parseCSV();
					$csv->output (true, 'simplemap.csv', $content, array('name','address','address2','city','state','zip','country','phone','email', 'fax','url','description','category','tags','special','lat','lng','dateUpdated' ) );
					die();
				}
			}
			
		}

		// Exports a LEGACY SimpleMap CSV file to WordPress
		function export_legacy_csv() {
			global $simple_map, $sm_locations,$wpdb;
			
			if ( isset( $_GET['sm-action'] ) && 'export-legacy-csv' == $_GET['sm-action'] ) {

				// Include CSV library
				include_once( SIMPLEMAP_PATH . '/classes/parsecsv.lib.php' );

				// Grab Categories
				if ( $categories = $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "simple_map_cats`" ) ) {
					foreach ( $categories as $key => $value ) {
						$cats[$value->id] = $value;
					}
				}
				// Grab locations
				if ( $locations = $wpdb->get_results( "SELECT * FROM `" . $wpdb->prefix . "simple_map`" ) ) {

					foreach( $locations as $key => $location ) {

						$catnames = '';
						
						// Do Cats		
						if ( isset( $location->category ) && 0 != $location->category ) {
							if ( isset( $cats[$location->category] ) )
								$catnames = $cats[$location->category]->name;
						}						
						$content[] = array( 
							'name' => esc_attr( $location->name ), 
							'address' => esc_attr( $location->address ), 
							'address2' => esc_attr( $location->address2 ), 
							'city' => esc_attr( $location->city ), 
							'state' => esc_attr( $location->state ), 
							'zip' => esc_attr( $location->zip ), 
							'country' => esc_attr( $location->country ), 
							'phone' => esc_attr( $location->phone ), 
							'fax' => esc_attr( $location->fax ), 
							'url' => esc_attr( $location->url ), 
							'description' => esc_attr( $location->description ), 
							'category' => esc_attr( $catnames ), 
							'tags' => esc_attr( $location->tags ), 
							'special' => esc_attr( $location->special ), 
							'lat' => esc_attr( $location->lat ), 
							'lng' => esc_attr( $location->lng ), 
							'dateUpdated' => esc_attr( $location->dateUpdated ) 
						);
	
					}

					$csv = new parseCSV();
					$csv->output (true, 'simplemap.csv', $content, array('name','address','address2','city','state','zip','country','phone','fax','url','description','category','tags','special','lat','lng','dateUpdated' ) );
					die();

				} else {
					$csv = new parseCSV();
					$csv->
					$csv->output (true, 'simplemap.csv', array( array( 'You have no locations in your legacy database' ) ) );
					die();
				}
			}
			
		}
		
		// Deletes legacy tables
		function delete_legacy_tables() {
			global $wpdb, $simple_map;
			
			if ( isset( $_GET['sm-action'] )  && 'delete-legacy-simplemap' == $_GET['sm-action'] ) {
				
				// Confirm we have both permisssion to do this and we have intent to do this.
				if ( current_user_can( 'manage_options' ) && check_admin_referer( 'delete-legacy-simplemap' ) ) {
					
					$drop_sm 	= 'DROP TABLE `' . $wpdb->prefix. 'simple_map`;';
					$drop_cats 	= 'DROP TABLE `' . $wpdb->prefix . 'simple_map_cats`;';

					$wpdb->query( $drop_sm );
					$wpdb->query( $drop_cats );
					
					if ( $simple_map->legacy_tables_exist() ) {
						wp_redirect( admin_url( 'admin.php?page=simplemap-import-export&sm-msg=3' ) );
						die();
					} else {
						wp_redirect( admin_url( 'admin.php?page=simplemap-import-export&sm-msg=2' ) );
						die();					
					}
				}
			
			}
		
		}

		// Imports a CSV file to WordPress
		function import_csv() {
			global $simple_map, $sm_locations, $wpdb, $current_user;

			if ( isset( $_POST['sm-action'] ) && 'import-csv' == $_POST['sm-action'] && isset( $_POST['step'] ) && 2 == $_POST['step'] ) {
				?>
				<div class="wrap">
						
					<?php
					// Title
					$sm_page_title = apply_filters( 'sm-import-export-page-title', 'SimpleMap: Import. Step One' );
					
					// Toolbar
					$simple_map->show_toolbar( $sm_page_title );
					?>
				
					<div id="dashboard-widgets-wrap" class="clear">
				
						<div id='dashboard-widgets' class='metabox-holder'>
						
							<div class='postbox-container' >
							
								<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
								
									<div class="postbox">
						
										<h3><?php _e('CSV Import: Step Two: Importing CSV', 'SimpleMap'); ?></h3>
										
										<div class="inside" style="padding: 0 10px 10px 10px;">
										<?php

										// Include CSV library
										include_once( SIMPLEMAP_PATH . '/classes/parsecsv.lib.php' );
										if ( file_exists( $_POST['file_location'] ) && $csv = new parseCSV() ) {
											
											$csv->auto( SIMPLEMAP_PATH . '/temp-csv.csv' );
											
											if ( isset( $csv->data ) ) {
											
												echo "<ol style='list-style-type:decimal'>";

												foreach( $csv->data as $row => $location ) {
													
													// Give me 20 seconds for each location. That should be more than enough time.
													set_time_limit ( 20 );
													
													// Convert assoc to int array since I can't trust the headings from the user
													$location = array_values( $location );
													
													// Use the information the user gave me via select boxes to map columns to correct attributes
													foreach( $sm_locations->get_location_data_types() as $key => $value ) {
														
														$bang = str_replace( 'col_', '', array_search( $key, $_POST ) );
														
														if ( isset( $location[$bang] ) )
															$to_insert[$key] = trim( $location[$bang] );
														else
															$to_insert[$key] = '';
						
													}

													// Prep and insert
													if ( isset( $to_insert ) ) {
														
														$options = get_option( 'SimpleMap_options' );
														$geocoded = '';

														// Maybe geo encode
														if ( ( '' == $to_insert['lat'] || '' == $to_insert['lng'] || 0 == $to_insert['lat'] || 0 == $to_insert['lng'] ) && '' != $options['api_key'] ) {
																														
															if ( $geo = $simple_map->geocode_location( $to_insert['address'], $to_insert['city'], $to_insert['state'], $to_insert['zip'], $to_insert['country'], $options['api_key'] ) ) {
																
																$geocoded = __( 'geocoded and ', 'SimpleMap' );
																
																if ( isset( $geo['lat'] ) )
																	$to_insert['lat'] = $geo['lat'];
						
																if ( isset( $geo['lng'] ) )
																	$to_insert['lng'] = $geo['lng'];
															}
														}
														
														// Prep for WordPress function
														wp_get_current_user();
														$vars['post_title'] = $wpdb->prepare( $to_insert['name'] );
														$vars['post_author'] = $current_user->ID;
														$vars['post_type'] = 'sm-location';
														$vars['post_status'] = 'publish';
														$vars['post_content'] = $wpdb->prepare( $to_insert['description'] );
														
														if ( $id = wp_insert_post( $vars ) ) {
															update_post_meta( $id, 'location_address', $wpdb->prepare( $to_insert['address'] ) );
															update_post_meta( $id, 'location_address2', $wpdb->prepare( $to_insert['address2'] ) );
															update_post_meta( $id, 'location_city', $wpdb->prepare( $to_insert['city'] ) );
															update_post_meta( $id, 'location_state', $wpdb->prepare( $to_insert['state'] ) );
															update_post_meta( $id, 'location_zip', $wpdb->prepare( $to_insert['zip'] ) );
															update_post_meta( $id, 'location_country', $wpdb->prepare( $to_insert['country'] ) );
															update_post_meta( $id, 'location_phone', $wpdb->prepare( $to_insert['phone'] ) );
															update_post_meta( $id, 'location_fax', $wpdb->prepare( $to_insert['fax'] ) );
															update_post_meta( $id, 'location_email', $wpdb->prepare( $to_insert['email'] ) );
															update_post_meta( $id, 'location_special', $wpdb->prepare( $to_insert['special'] ) );
															update_post_meta( $id, 'location_lat', $wpdb->prepare( $to_insert['lat'] ) );
															update_post_meta( $id, 'location_lng', $wpdb->prepare( $to_insert['lng'] ) );
														
															// Do categories
															if ( isset( $to_insert['category'] ) ) {
																
																// Place comma seperatred CSV categories into array
																$cats = explode( ',', $to_insert['category'] );
																
																// Loop through array. If category exists, assoc it with the location
																// If category doesn't exist, create it and associate it.
																foreach( (array) $cats as $key => $name ) {
																	
																	// Skip it if we have bad data
																	if ( '' != $name || ! empty( $name ) ) {
																	
																		// Grab or create and grab the category ID
																		if ( ! $cat_obj = get_term_by( 'name', $name, 'sm-category' ) )
																			$category_id = wp_insert_term( $name, 'sm-category' );
																		else
																			$category_id = $cat_obj->term_id;
																		
																		
																		// This is just a failsafe. It also gives us access to vars teh WP API created rather than from the CSV
																		if ( $category = get_term( (int) $category_id, 'sm-category' ) ) {
						
	
																			if ( ! is_wp_error( $category ) ) {
																				
																				// Associate (last var appends term to rather than replaces existing terms)
																				wp_set_object_terms( $id , $category->name, 'sm-category', true );
																				unset( $category );
																			
																			}
																		
																		}
																	}
																}
															}
															
															// Do Tags
															if ( isset( $to_insert['tags'] ) ) {
																
																// Place comma seperatred CSV tags into array
																$tags = explode( ',', $to_insert['tags'] );
																
																// Loop through array. If tag exists, assoc it with the location
																// If category doesn't exist, create it and associate it.
																foreach( (array) $tags as $key => $name ) {
																	
																	// Skip it if we have bad data
																	if ( '' != $name || ! empty( $name ) ) {
																		
																		// Grab or create and grab the tag ID
																		if ( ! $tag_obj = get_term_by( 'name', $name, 'sm-tag' ) )
																			$tag_id = wp_insert_term( $name, 'sm-tag' );
																		else
																			$tag_id = $tag_obj->term_id;
																		
																		// This is just a failsafe. It also gives us access to vars teh WP API created rather than from the CSV
																		if ( $tag = get_term( (int) $tag_id, 'sm-tag' ) ) {
	
																			if ( ! is_wp_error( $tag ) ) {
																			
																				// Associate (last var appends term to rather than replaces existing terms)
																				wp_set_object_terms( $id , $tag->name, 'sm-tag', true );
																				unset( $tag );
																			
																			}
																		
																		}
																	}
																}
															}
															
															echo "<li>" . sprintf( esc_attr( $to_insert['name'] ) . __( ' was successfully %simported', 'SimpleMap' ), $geocoded ) . "</li>";
														} else {
															echo "<li>" . esc_attr( $to_insert['name'] ) . __( ' failed to import properly', 'SimpleMap' ) . "</li>";
														}

														unset( $to_insert );
														unset( $geocoded );
													}
													
												}
												
												echo "</ul>";
												echo "<h2>" . sprintf( __( 'View them <a href="%s">here</a>', 'SimpleMap' ), admin_url( 'edit.php?post_type=sm-location' ) ) . "</h2>";
											}
											
											// Import is finished, delete csv and redirect to edit locaitons page
											if ( file_exists( $_POST['file_location'] ) )
												unlink( $_POST['file_location'] );
												
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				
			}
		}
		
		// Generates the CSV Preview
		function do_csv_preview() {
			global $simple_map;
			$options = get_option( 'SimpleMap_options' );
			
			if ( !isset( $options['api_key'] ) )
				$options['api_key'] = '';
				
			extract( $options );

			?>
			<div class="wrap">
					
				<?php
				// Title
				$sm_page_title = apply_filters( 'sm-import-export-page-title', 'SimpleMap: Import. Step One' );
				
				// Toolbar
				$simple_map->show_toolbar( $sm_page_title );
				?>
			
				<div id="dashboard-widgets-wrap" class="clear">
			
					<div id='dashboard-widgets' class='metabox-holder'>
					
						<div class='postbox-container' >
						
							<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
							
								<div class="postbox">
					
									<h3><?php _e('CSV Import: Step One', 'SimpleMap'); ?></h3>
									
									<div class="inside" style="padding: 0 10px 10px 10px;">
									
										<p class='howto'><?php printf( __( 'The first step is to confirm that we importing the data correctly. %sPlease match the following sample data%s from your CSV to the correct data type by selecting an attributes form the downdown boxes.', 'SimpleMap' ), '<strong><span style="color:red;">', '</span></strong>' );?></p>

										<?php 
										// Include CSV library
										include_once( SIMPLEMAP_PATH . '/classes/parsecsv.lib.php' );

										if ( move_uploaded_file( $_FILES['simplemap-csv-upload']['tmp_name'], SIMPLEMAP_PATH . '/temp-csv.csv' ) ) {
										
											if ( $csv = new parseCSV( SIMPLEMAP_PATH . '/temp-csv.csv' ) ) {
											
												?>
												<form action='' method='post'>
												<input type='hidden' name='sm-action' value='import-csv' />
												<input type='hidden' name='step' value='2' />
												<input type='hidden' name='file_location' value='<?php echo SIMPLEMAP_PATH . '/temp-csv.csv'; ?>' />
												<p><input type='submit' class="button-primary" value='<?php _e( 'Import CSV', 'SimpleMap' ); ?>' /></p>
												<table>
													<tr>
														<?php 
														foreach( $csv->titles as $col => $title ) {
															echo "<td>" . $this->column_select( $col, $title ) . "</td>";
														}
														?>
														</form>
													</tr>
													<?php // Grab some random rows to display as a sample
													$row_count = count( $csv->data );
													if ( $row_count < 50 ) {
														foreach( $csv->data as $csv_row => $csv_row_data ) {
															?><tr><?php
															foreach ( $csv_row_data as $td => $tdv ) {
																?><td><?php echo esc_attr( $tdv ); ?></td><?php
															}
															?></tr><?php
														}
													} else {
														for( $i=0;$i<=50;$i++ ) {
															$numb = rand( 1, $row_count -1 );
															?><tr><?php
															foreach ( $csv->data[$numb] as $td => $tdv ) {
																?><td><?php echo esc_attr( $tdv ); ?></td><?php
															}
															?></tr><?php
														}
													}
													?>
												</table>
												<?php
											}
										}
										?>

									</div>
									
								</div>

							</div>
						
						</div>
					
					</div>
				
				</div>
				<?php
		}
		
		// This function creates a select box of all my data types to assign to this column
		function column_select( $col, $title ) {
			global $sm_locations;
			
			$select = "<select name='col_" . esc_attr( $col ) . "'>";
			
			$select .= "<option value='-1' >Don't Import</option>";


			foreach( $sm_locations->get_location_data_types() as $type => $label ) {
				$select .= "<option value='" . $type . "' " . selected( trim( $type ), trim( $title ), false ) . " >" . $label . "</option>";
			}
			
			$select .= "</select>";
			
			return $select;
		}
		
		// Prints the options page
		function print_page(){


			if ( isset( $_POST['sm-action'] ) && 'import-csv' == $_POST['sm-action'] ) {
			
				$step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;

				// Check for uploaded file with no errors.
				if ( ( 1 == $step && isset( $_FILES['simplemap-csv-upload'] ) && ! $_FILES['simplemap-csv-upload']['error'] > 0 ) || 2 == $step ) {

					switch( $step ) {
						case 2:
							$this->import_csv();
							break;
						case 1:
						default :
							$this->do_csv_preview();
					}
				}
			} else { 
	
	
				global $simple_map;
				$options = get_option( 'SimpleMap_options' );
				
				if ( !isset( $options['api_key'] ) )
					$options['api_key'] = '';
					
				extract( $options );
	
				
				?>
				<div class="wrap">
						
					<?php
					// Title
					$sm_page_title = apply_filters( 'sm-import-export-page-title', 'SimpleMap: Import/Export CSV' );
					
					// Toolbar
					$simple_map->show_toolbar( $sm_page_title );

					// Messages			 
					if ( isset( $_GET['sm-msg'] ) && '2' == $_GET['sm-msg'] )
						echo '<div class="updated fade"><p>'.__('Legacy SimpleMap settings deleted.', 'SimpleMap').'</p></div>';
				
					if ( isset( $_GET['sm-msg'] ) && '3' == $_GET['sm-msg'] )
						echo '<div class="error fade"><p>'.__('Legacy SimpleMap NOT settings deleted.', 'SimpleMap').'</p></div>';
					?>
				
					<div id="dashboard-widgets-wrap" class="clear">
				
						<div id='dashboard-widgets' class='metabox-holder'>
						
							<div class='postbox-container' style='max-width: 800px;'>
							
								<div id='normal-sortables' class='meta-box-sortables ui-sortable'>
								
									<div class="postbox">
						
										<h3><?php _e('Import From File', 'SimpleMap'); ?></h3>
										
										<div class="inside" style="padding: 0 10px 10px 10px;">
										
											<h4><?php _e('If your file has fewer than 100 records and does not have latitude/longitude data:', 'SimpleMap'); ?></h4>
											
											<p><?php _e('Make sure your CSV has a header row that gives the field names (in English). A good example of a header row would be as follows:', 'SimpleMap'); ?></p>
											
											<p><em style="color: #777; font: italic 1.1em Georgia;"><?php _e('Name, Address, Address Line 2, City, State/Province, ZIP/Postal Code, Country, Phone, Fax, URL, Category, Tags, Description, Special (1 or 0), Latitude, Longitude', 'SimpleMap'); ?></em></p>
											
											<p><?php _e('You can import your file with or without quotation marks around each field. However, if any of your fields contain commas, you should enclose your fields in quotation marks. Single ( \' ) or double ( " ) quotation marks will work.', 'SimpleMap') ?></p>
										
											<h4><?php _e('If your file has more than 100 records:', 'SimpleMap'); ?></h4>
											
											<p><?php _e('If you have more than 100 records to import, it is best to do one of the following:', 'SimpleMap'); ?></p>
											
											<ul style="list-style-type: disc; margin-left: 3em;">
												<li><?php _e('Geocode your own data before importing it'); ?></li>
												<li><?php _e('Split your file into multiple files with no more than 100 lines each'); ?></li>
											</ul>
											
											<p><?php printf(__('Geocoding your own data will allow you to import thousands of records very quickly. If your locations need to be geocoded by SimpleMap, any file with more than 100 records might stall your server. %s Resources for geocoding your own locations can be found here.%s', 'SimpleMap'), '<a href="http://groups.google.com/group/Google-Maps-API/web/resources-non-google-geocoders" target="_blank">', '</a>'); ?></p>
											
											<p><?php _e('If you are importing a file you exported from SimpleMap (and haven\'t changed since), be sure to check the box below since the locations are already geocoded.', 'SimpleMap'); ?></p>
										
											<form name="import_form" method="post" action="<?php echo admin_url( 'admin.php?page=simplemap-import-export' ); ?>" enctype="multipart/form-data" class="inabox">
												<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (2 * 1024 * 1024); ?>" />
												<input type="hidden" name="sm-action" value="import-csv" />
												
												<p style="margin-top: 0;"><label for="simplemap-csv-upload"><?php _e('File to import (maximum size 2MB):', 'SimpleMap'); ?></label><input type="file" style="padding-left: 10px; border: none; font-size: 0.9em;" id="simplemap-csv-upload" name="simplemap-csv-upload" />
												<br />
												</p>
												<input type="submit" class="button-primary" value="<?php _e('Import CSV File', 'SimpleMap'); ?>" /> <?php printf( __( "Warning: You still need to enter an <a href='%s'>API key</a> if you need your locaitons geocoded.", 'SimpleMap' ), admin_url( "admin.php?page=simplemap" ) ); ?>
											
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
									
											<form name="export_form" method="post" action="" >
											
												<input type="hidden" name="sm-action" value="export-csv" />
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
	
							<div class='postbox-container' style='width:49%;'>
								
								<div id='side-sortables' class='meta-box-sortables ui-sortable'>
								
								<?php do_action( 'sm-import-export-side-sortables-top' ); ?>
								
								<!-- #### PREMIUM SUPPORT #### -->
								
								<div class="postbox" >
									
									<h3 style='color:#fff;text-shadow:0 1px 0 #000;background: #fff url( <?php echo SIMPLEMAP_URL; ?>/inc/images/blue-grad.png ) top left repeat-x;'><?php _e( 'Premium Support and Features', 'SimpleMap' ); ?></h3>
									
									<div class="inside" style='padding: 0pt 10px 10px;' >
										
										<?php
										// Check for premium support status
										global $simplemap_ps;

										if ( ! url_has_ftps_for_item( $simplemap_ps ) ) : ?>
										
											<h4><?php printf( __( 'SimpleMap Premium Support Benefits', 'SimpleMap' ), esc_attr( get_option( 'siteurl' ) ) ); ?></h4>
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

								<?php if ( $simple_map->legacy_tables_exist() ) : ?>
									<!-- #### LEGACY EXPORT #### -->
									<div class="postbox" >
										
										<h3 style="background: #fff url( <?php echo SIMPLEMAP_URL; ?>/inc/images/blue-grad.png ) top left repeat-x;	color:#fff;	text-shadow:0 1px 0 #000;"><?php _e( 'Legacy Data', 'SimpleMap' ); ?></h3>
											
											<div class="inside" style="padding: 10px;">
												<p class='howto'><?php _e( 'It appears that you have location data stored in legacy SimpleMap tables that existed prior to version 2.0. What would you like to do with that data?', 'SimpleMap' ); ?></p>
												<p class='howto'><?php printf( __( 'Your choices are to export it here and reimport it on the <a href="%s">SimpleMap Import / Export page</a> or to just delete it.', 'SimpleMap' ), admin_url( 'admin.php?page=simplemap-import-export' ) ); ?></p>
												<ul  style="list-style-type: disc; margin-left: 3em;">
													<li><a href='<?php echo admin_url( 'admin.php?page=simplemap-import-export&amp;sm-action=export-legacy-csv' ); ?>'><?php _e( 'Export legacy data as a CSV file', 'SimpleMap' ); ?></a></li>
													<li><a onClick="javascript:return confirm('<?php _e( 'Last chance! Pressing OK will delete all Legacy SimpleMap data.'); ?>')" href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=simplemap-import-export&sm-action=delete-legacy-simplemap' ), 'delete-legacy-simplemap' ); ?>" ><?php _e( 'Permanently delete the legacy data and tables', 'SimpleMap' ); ?></a></li>
												</ul>
											</div>
									</div>
	
								<?php endif; ?>
							
								<?php do_action( 'sm-import-export-side-sortables-bottom' ); ?>
								
							</div>
							
						</div> <!-- dashboard-widgets -->
						
						<div class="clear">
						</div>
					</div><!-- dashboard-widgets-wrap -->
				</div> <!-- wrap -->
				<?php		
			}
		}
	}
}
?>