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

				$defaults = array(
					'lat' => false,
					'lng' => false,
					'radius' => false,
					'namequery' => false,
					'query_type' => 'distance',
					'address' => false,
					'city' => false,
					'state' => false,
					'zip' => false,
					'onlyzip' => false,
					'country' => false,
					'limit' => false,
				);
				$input = array_filter( array_intersect_key( $_GET, $defaults ) ) + $defaults;

				$smtaxes = array();
				if ( $taxonomies = get_object_taxonomies( 'sm-location' ) ) {
					foreach ( $taxonomies as $key => $tax ) {
						$phpsafe = str_replace( '-', '_', $tax );
						$_GET += array( $phpsafe => '' );
						$smtaxes[$tax] = $_GET[$phpsafe];
					}
				}

				// Define my empty strings
				$distance_select = $distance_having = $distance_order = '';

				// We're going to do a hard limit to 5000 for now.
				if ( !$input['limit'] || $input['limit'] > 250 )
					$limit = "LIMIT 250";
				else
					$limit = 'LIMIT ' . absint( $input['limit'] );

				$limit = apply_filters( 'sm-xml-search-limit', $limit );

				// Locations within specific distance or just get them all?
				$distance_select = $wpdb->prepare( "( 3959 * ACOS( COS( RADIANS(%s) ) * COS( RADIANS( lat_tbl.meta_value ) ) * COS( RADIANS( lng_tbl.meta_value ) - RADIANS(%s) ) + SIN( RADIANS(%s) ) * SIN( RADIANS( lat_tbl.meta_value ) ) ) ) AS distance", $input['lat'], $input['lng'], $input['lat'] ) . ', ';
				$distance_order = 'distance, ';

				if ( $input['radius'] ) {
					$distance_having = $wpdb->prepare( "HAVING distance < %d", $input['radius'] );
				}

				$i = 1;
				$taxonomy_join = '';
				foreach ( array_filter( $smtaxes ) as $taxonomy => $tax_value ) {
					$term_ids = explode( ',', $tax_value );
					if ( $term_ids[0] == 'OR' ) {
						unset( $term_ids[0] );
						if ( empty( $term_ids ) ) {
							continue;
						}
						$search_values = array( "IN (" . vsprintf( '%d' . str_repeat( ',%d', count( $term_ids ) - 1 ), $term_ids ) . ")" );
					} else {
						$search_values = array();
						foreach ( $term_ids as $term_id ) {
							$search_values[] = sprintf( '= %d', $term_id );
						}
					}
					foreach ( $search_values as $search_value ) {
						$taxonomy_join .= "
							INNER JOIN
								$wpdb->term_relationships AS term_rel_$i ON posts.ID = term_rel_$i.object_id
							INNER JOIN
								$wpdb->term_taxonomy AS tax_$i ON
									term_rel_$i.term_taxonomy_id = tax_$i.term_taxonomy_id
									AND tax_$i.taxonomy = '$taxonomy'
									AND tax_$i.term_id $search_value
						";
						$i++;
					}
				}

				$sql = $wpdb->prepare( "
					SELECT
						lat_tbl.meta_value AS lat,
						lng_tbl.meta_value AS lng,
						$distance_select
						posts.ID,
						posts.post_content,
						posts.post_title
					FROM
						$wpdb->posts AS posts
					INNER JOIN
						$wpdb->postmeta lat_tbl ON lat_tbl.post_id = posts.ID AND lat_tbl.meta_key = 'location_lat'
					INNER JOIN
						$wpdb->postmeta lng_tbl ON lng_tbl.post_id = posts.ID AND lng_tbl.meta_key = 'location_lng'
						$taxonomy_join
					WHERE
						posts.post_type = 'sm-location'
						AND posts.post_status = 'publish'
					GROUP BY
						posts.ID
						$distance_having
					ORDER BY
						$distance_order posts.post_name ASC
					$limit
				" );

				$sql = apply_filters( 'sm-xml-search-locations-sql', $sql );

				// TODO: Consider using this to generate the marker node attributes in print_xml().
				$location_field_map = array(
					'location_address' => 'address',
					'location_address2' => 'address2',
					'location_city' => 'city',
					'location_state' => 'state',
					'location_zip' => 'zip',
					'location_country' => 'country',
					'location_phone' => 'phone',
					'location_fax' => 'fax',
					'location_email' => 'email',
					'location_url' => 'url',
					'location_special' => 'special',
				);

				if ( $locations = $wpdb->get_results( $sql ) ) {
					// Start looping through all locations i found in the radius
					foreach ( $locations as $key => $value ) {
						// Add postmeta data to location
						$custom_fields = get_post_custom( $value->ID );
						foreach ( $location_field_map as $key => $field ) {
							if ( isset( $custom_fields[$key][0] ) ) {
								$value->$field = $custom_fields[$key][0];
							}
							else {
								$value->$field = '';
							}
						}

						// List all terms for all taxonomies for this post
						foreach ( $smtaxes as $taxonomy => $tax_value ) {
							$phpsafe_tax = str_replace( '-', '_', $taxonomy );
							$local_taxes = $local_tax_names = '';

							// Get all taxes for this post
							if ( $loc_taxes = wp_get_object_terms( $value->ID, $taxonomy ) ) {
								$loc_tax_names = '';

								foreach( $loc_taxes as $loc_tax ) {
									$loc_tax_names[] = $loc_tax->name;
								}

								if ( isset( $loc_tax_names ) ) {
									$value->$phpsafe_tax = implode( ', ', $loc_tax_names );
								}
							} else {
								$value->$phpsafe_tax = '';
							}
						}
					}

					$locations = apply_filters( 'sm-xml-search-locations', $locations );

					$this->print_xml( $locations, $smtaxes );
				} else {
					// Print empty XML
					$this->print_xml( new stdClass(), $smtaxes );
				}
			}
		}

		// Prints the XML output
		function print_xml( $dataset, $smtaxes ) {
			header("Content-type: text/xml");

			do_action( 'sm-print-xml', $dataset, $smtaxes );

			if ( class_exists( 'DOMDocument' ) ) {
				$dom 		= new DOMDocument( "1.0" );
				$node 		= $dom->createElement( "markers" );
				$markers 	= $dom->appendChild( $node );
				$attr_func	= 'setAttribute';
			}
			elseif ( class_exists( 'SimpleXMLElement' ) ) {
				$markers	= new SimpleXMLElement( '<markers />' );
				$attr_func	= 'addAttribute';
			}

			if ( isset( $markers ) ) {
				// Loop through dataset
				foreach ( $dataset as $key => $location ) {
					if ( isset( $dom ) ) {
						$node 		= $dom->createElement( "marker" );
						$newnode 	= $markers->appendChild( $node );
					}
					else {
						$newnode 	= $markers->addChild( 'marker' );
					}

					$newnode->$attr_func( "name", apply_filters( 'the_title', $location->post_title ) );
					$newnode->$attr_func( "description", apply_filters( 'the_content', $location->post_content ) );
					$newnode->$attr_func( "lat", esc_attr( $location->lat ) );
					$newnode->$attr_func( "lng", esc_attr( $location->lng ) );
					$newnode->$attr_func( "distance", esc_attr( $location->distance ) );
					$newnode->$attr_func( "address", esc_attr( $location->address ) );
					$newnode->$attr_func( "address2", esc_attr( $location->address2 ) );
					$newnode->$attr_func( "city", esc_attr( $location->city ) );
					$newnode->$attr_func( "state", esc_attr( $location->state ) );
					$newnode->$attr_func( "zip", esc_attr( $location->zip ) );
					$newnode->$attr_func( "country", esc_attr( $location->country ) );
					$newnode->$attr_func( "phone", esc_attr( $location->phone ) );
					$newnode->$attr_func( "fax", esc_attr( $location->fax ) );
					$newnode->$attr_func( "url", esc_attr( $location->url ) );
					$newnode->$attr_func( "email", esc_attr( $location->email ) );
					$newnode->$attr_func( "special", esc_attr( $location->special ) );

					// Add all terms for this location's taxonomies
					foreach ( $smtaxes as $taxonomy => $tax_value ) {
						$phpsafe_tax = str_replace( '-', '_', $taxonomy );
						$newnode->$attr_func( $phpsafe_tax, esc_attr( $location->$phpsafe_tax ) );
					}
				}

				if ( isset( $dom ) ) {
					echo $dom->saveXML();
				}
				else {
					echo $markers->asXML();
				}
			}
			else {
				$markers = array();
				foreach ( $dataset as $key => $location ) {
					$fields = array(
						'name' => apply_filters( 'the_title', $location->post_title ),
						'description' => apply_filters( 'the_content', $location->post_content ),
						'lat' => esc_attr( $location->lat ),
						'lng' => esc_attr( $location->lng ),
						'distance' => esc_attr( $location->distance ),
						'address' => esc_attr( $location->address ),
						'address2' => esc_attr( $location->address2 ),
						'city' => esc_attr( $location->city ),
						'state' => esc_attr( $location->state ),
						'zip' => esc_attr( $location->zip ),
						'country' => esc_attr( $location->country ),
						'phone' => esc_attr( $location->phone ),
						'fax' => esc_attr( $location->fax ),
						'url' => esc_attr( $location->url ),
						'email' => esc_attr( $location->email ),
						'special' => esc_attr( $location->special ),
					);
					foreach ( $smtaxes as $taxonomy => $tax_value ) {
						$phpsafe_tax = str_replace( '-', '_', $taxonomy );
						$fields[$phpsafe_tax] = esc_attr( $location->$phpsafe_tax );
					}

					$marker = '<marker ';
					foreach ( $fields as $field => $value ) {
						$marker .= $field . '="' . $value . '" ';
					}
					$marker .= '/>';

					$markers[] = $marker;
				}

				echo '<?xml version="1.0"?>' . "\n". '<markers>' . implode( '', $markers ) . '</markers>';
			}

			die();
		}
	}
}
