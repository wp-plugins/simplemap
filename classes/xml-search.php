<?php
if ( !class_exists( 'SM_XML_Search' ) ){
	class SM_XML_Search{
		
		// Register hook to perform the search
		function sm_xml_search() {
			add_action( 'template_redirect', array( &$this, 'init_search' ) );
		}
		
		// Inits the search process. Collects default options, search options, and queries DB
		function init_search() {
			if ( isset( $_GET['sm-xml-search'] ) ) {
				global $wpdb;

				$lat		= ! empty( $_GET['lat'] ) ? $_GET['lat'] : false;
				$lng		= ! empty( $_GET['lng'] ) ? $_GET['lng'] : false;
				$radius		= ! empty( $_GET['radius'] ) ? $_GET['radius'] : false;
				$namequery	= ! empty( $_GET['namequery'] ) ? $_GET['namequery'] : false;
                $query_type = ! empty( $_GET['query_type'] ) ? $_GET['query_type'] : 'distance';
				$address	= ! empty( $_GET['address'] ) ? $_GET['address'] : false;
				$city		= ! empty( $_GET['city'] ) ? $_GET['city'] : false;
				$state		= ! empty( $_GET['state'] ) ? $_GET['state'] : false;
				$zip		= ! empty( $_GET['zip'] ) ? $_GET['zip'] : false;
				$onlyzip	= ! empty( $_GET['onlyzip'] ) ? $_GET['onlyzip'] : false;
				//$country	= ! empty( $_GET['country'] ) ? $_GET['country'] : false;
				$limit		= ! empty( $_GET['limit'] ) ? $_GET['limit'] : false;
				$cats 		= ! empty( $_GET['categories'] ) ? $_GET['categories'] : '';
				$tags		= ! empty( $_GET['tags'] ) ? $_GET['tags'] : '';

				// Define my empty strings
				$distance_select = $distance_having = $distance_order = '';

                // We're going to do a hard limit to 250 for now.
                if ( !$limit || $limit > 250 )
                    $limit = 'LIMIT 250';
				else
					$limit = 'LIMIT ' . absint( $limit );
						
                $limit = apply_filters( 'sm-xml-search-limit', $limit, $cats, $tags );
                
				// Locations within specific distance or just get them all?
                $distance_select = $wpdb->prepare( "( 3959 * ACOS( COS( RADIANS(%s) ) * COS( RADIANS( lat_tbl.meta_value ) ) * COS( RADIANS( lng_tbl.meta_value ) - RADIANS(%s) ) + SIN( RADIANS(%s) ) * SIN( RADIANS( lat_tbl.meta_value ) ) ) ) AS distance", $lat, $lng, $lat ) . ', ';
                $distance_order = 'distance, ';

				if ( $radius ) {
					$distance_having = $wpdb->prepare( "HAVING distance < %d", $radius );
				}
				
				// Build my Query		
				$sql = $wpdb->prepare( "
					SELECT 
                        lat_tbl.meta_value as lat,
                        lng_tbl.meta_value as lng,
                        $distance_select,
                        posts.ID,
                        posts.post_content,
                        posts.post_title
					FROM 
						$wpdb->posts as posts 
                    INNER JOIN
                        $wpdb->postmeta lat_tbl ON lat_tbl.post_id = posts.ID AND lat_tbl.meta_key = 'location_lat'
                    INNER JOIN
                        $wpdb->postmeta lng_tbl ON lng_tbl.post_id = posts.ID AND lng_tbl.meta_key = 'location_lng'
					WHERE 
                        posts.post_type = 'sm-location' 
                        AND posts.post_status = 'publsih' 
                        $distance_having
					ORDER BY 
						$distance_order posts.post_name ASC
					$limit
				" );

				$sql = apply_filters( 'sm-xml-search-locations-sql', $sql, $cats, $tags );

/**
* THIS IS HORRIBLY OPTIMIZED. WORKIN GON IT.
**/
                if ( $locations = $wpdb->get_results( $sql ) ) {

					// Start looping through all locations i found in the radius
					foreach ( $locations as $key => $value ) {
						
						// Not unset yet and not safe yet
						$unset = false;
						$safe = false;
						
						
						// Defaults
						$value->address = $value->address2 = $value->city = $value->state = $value->zip = $value->country = $value->phone = $value->fax = $value->email = $value->url = $value->special = $value->categories = $value->tags = '';

						// Process categories
						if ( $cats && '' != $cats ) {
	
							// Convert selected cats to an array
							$cats_array = explode( ',', $cats );
							
							// If 'OR' is the first key, we need to unset location if its not in a cat
							if ( 'OR' == $cats_array[0] ) {
							
								unset( $cats_array[0] );
								if ( isset( $locations[$key] ) && is_wp_error( is_object_in_term( $value->ID, 'sm-category', $cats_array ) ) || ! is_object_in_term( $value->ID, 'sm-category', $cats_array ) )
									unset( $locations[$key] );
							
							} else {
							
								// Loop through each selected cat and unset the location if its not in a selected cat.
								foreach( $cats_array as $selected_cat ) {
	
									// Checks to make sure location is in each selected category
									if ( isset( $locations[$key] ) && is_wp_error( is_object_in_term( $value->ID, 'sm-category', $selected_cat ) ) || ! is_object_in_term( $value->ID, 'sm-category', $selected_cat ) )
										unset( $locations[$key] );
								
								}
							
							}
							
						}

						// Process tags
						if ( $tags && '' != $tags ) {
	
							// Convert selected tags to an array
							$tags_array = explode( ',', $tags );
							
							// If 'OR' is the first key, we need to unset location if its not in a tag
							if ( 'OR' == $tags_array[0] ) {
							
								unset( $tags_array[0] );
								if ( isset( $locations[$key] ) &&  is_wp_error( is_object_in_term( $value->ID, 'sm-tag', $tags_array ) ) || ! is_object_in_term( $value->ID, 'sm-tag', $tags_array ) )
									unset( $locations[$key] );
							
							} else {
							
								// Loop through each selected tag and unset the location if its not in a selected tag.
								foreach( $tags_array as $selected_tag ) {
	
									// Checks to make sure location is in each selected category
									if ( isset( $locations[$key] ) && is_wp_error( is_object_in_term( $value->ID, 'sm-tag', $selected_tag ) ) || ! is_object_in_term( $value->ID, 'sm-tag', $selected_tag ) )
										unset( $locations[$key] );
								
								}
							}
							
						}

						// Add postmeta data to location
						if ( isset( $locations[$key] ) ) {
						
							$value->address = get_post_meta( $value->ID, 'location_address', true );
							$value->address2 = get_post_meta( $value->ID, 'location_address2', true );
							$value->city = get_post_meta( $value->ID, 'location_city', true );
							$value->state = get_post_meta( $value->ID, 'location_state', true );
							$value->zip = get_post_meta( $value->ID, 'location_zip', true );
							$value->country = get_post_meta( $value->ID, 'location_country', true );
							$value->phone = get_post_meta( $value->ID, 'location_phone', true );
							$value->fax = get_post_meta( $value->ID, 'location_fax', true );
							$value->email = get_post_meta( $value->ID, 'location_email', true );
							$value->url = get_post_meta( $value->ID, 'location_url', true );
							$value->special = get_post_meta( $value->ID, 'location_special', true );

							// Get all categories for this post
							if ( $loc_cats = wp_get_object_terms( $value->ID, 'sm-category' ) ) {
								$loc_cat_names = '';
								foreach( $loc_cats as $loc_cat ) {
									$loc_cat_names[] = $loc_cat->name;
								}
								if ( isset( $loc_cat_names ) )
									$value->categories = implode( ', ', $loc_cat_names );
							} else {
                                $value->categories = '';
                            }
							
							// Get all tags for this post
							if ( $loc_tags = wp_get_object_terms( $value->ID, 'sm-tag' ) ) {
								$loc_tag_names = '';
								foreach( $loc_tags as $loc_tag ) {
									$loc_tag_names[] = $loc_tag->name;
								}
								if ( isset( $loc_tag_names ) )
									$value->tags = implode( ', ', $loc_tag_names );
							} else {
                                $value=>tags = '';
                            }
						
						}
					}

					$locations = apply_filters( 'sm-xml-search-locations', $locations, $cats, $tags );

					$this->print_xml( $locations );
				} else {
					// Print empty XML
					$this->print_xml( new stdClass() );
				}
				
			}
		}
		
		// Prints the XML output
		function print_xml( $dataset ) {
			$dom 		= new DOMDocument( "1.0" );
			$node 		= $dom->createElement( "markers" );
			$parnode 	= $dom->appendChild( $node );

			header("Content-type: text/xml");
			
			// Loop through dataset
			foreach ( $dataset as $key => $location ) {
	
				$node 		= $dom->createElement( "marker" );
				$newnode 	= $parnode->appendChild( $node );
				
				$newnode->setAttribute( "name", apply_filters( 'the_title', $location->post_title ) );
				$newnode->setAttribute( "description", apply_filters( 'the_content', $location->post_content ) );
				$newnode->setAttribute( "address", esc_attr( $location->address ) );
				$newnode->setAttribute( "address2", esc_attr( $location->address2 ) );
				$newnode->setAttribute( "city", esc_attr( $location->city ) );
				$newnode->setAttribute( "state", esc_attr( $location->state ) );
				$newnode->setAttribute( "zip", esc_attr( $location->zip ) );
				$newnode->setAttribute( "country", esc_attr( $location->country ) );
				$newnode->setAttribute( "lat", esc_attr( $location->lat ) );
				$newnode->setAttribute( "lng", esc_attr( $location->lng ) );
				$newnode->setAttribute( "distance", esc_attr( $location->distance ) );
				$newnode->setAttribute( "phone", esc_attr( $location->phone ) );
				$newnode->setAttribute( "fax", esc_attr( $location->fax ) );
				$newnode->setAttribute( "url", esc_attr( $location->url ) );
				$newnode->setAttribute( "email", esc_attr( $location->email ) );
				$newnode->setAttribute( "special", esc_attr( $location->special ) );
				$newnode->setAttribute( "categories", esc_attr( $location->categories) );
				$newnode->setAttribute( "tags", esc_attr( $location->tags ) );
	
			}
			
			echo $dom->saveXML();
			die();
		}
	}
}
