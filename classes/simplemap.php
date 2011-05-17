<?php
if ( !class_exists( 'Simple_Map' ) ) {

	class Simple_Map {
	
		var $plugin_url;
		var $plugin_domain = 'SimpleMap';
		
		// Initialize the plugin
		function Simple_Map() {
			
			$plugin_dir = basename( SIMPLEMAP_PATH );
			load_plugin_textdomain( $this->plugin_domain, 'wp-content/plugins/' . $plugin_dir . '/lang', $plugin_dir . '/lang' );
			
			$this->plugin_url = SIMPLEMAP_URL;
						
			// Add shortcode handler
			add_shortcode( 'simplemap', array( &$this, 'display_map' ) );
						
			// Enqueue frontend scripts & styles into <head>
			add_action( 'template_redirect', array( &$this, 'enqueue_frontend_scripts_styles' ) );
			
			// Enqueue backend scripts
			add_action( 'init', array( &$this, 'enqueue_backend_scripts_styles' ) );

			// Add hook for master js file
			add_action( 'template_redirect', array( &$this, 'google_map_js_script' ) );

			// Add hook for general options js file
			add_action( 'init', array( &$this, 'general_options_js_script' ) );
			
			// Query vars
			add_filter( 'query_vars', array( &$this, 'register_query_vars' ) );
		
			// Backwards compat for core sm taxonomies
			add_filter( 'sm_category-text', array( &$this, 'backwards_compat_categories_text' ) );
			add_filter( 'sm_tag-text', array( &$this, 'backwards_compat_tags_text' ) );
		
		}
				
		// This function generates the code to display the map
		function display_map( $atts ) {

			$options = $this->get_default_options();
			
			$atts = $this->parse_shortcode_atts( $atts );

			extract( $atts );

			$to_display = '';
			
			$to_display .= $this->location_search_form( $atts );
			
			if ( $powered_by )
				$to_display .= '<div id="powered_by_simplemap">' . sprintf( __( 'Powered by %s SimpleMap', 'SimpleMap' ), '<a href="http://simplemap-plugin.com/" target="_blank">' ) . '</a></div>';

			// Hide map?
			$hidemap = $hide_map ? "display:none; " : '';

			// Hide list?
			$hidelist = $hide_list ? "display:none; " : '';
			
			// Map Width and height
			$map_width = ( '' == $map_width ) ? $options['map_width'] : $map_width;
			$map_height = ( '' == $map_height ) ? $options['map_height'] : $map_height;
			
			// Updating Div
			$to_display .= '<div id="simplemap-updating" style="display:none;position:absolute; padding:10px; background:#fff; color:#000;vertical-align:middle;text-align:center;"><img style="vertical-align:middle;text-align:center;" src="' . SIMPLEMAP_URL . '/inc/images/loading.gif" alt="Loading new locations" /></div>';
			
			$to_display .= '<div id="simplemap" style="' . $hidemap . 'width: ' . $map_width . '; height: ' . $map_height . ';"></div>';
			$to_display .= '<div id="results" style="' . $hidelist . 'width: ' . $map_width . ';"></div>';
			$to_display .= '<script type="text/javascript">';
			$to_display .= '(function($) { ';
			$to_display .= '$(document).ready(function() {';
			$to_display .= '	load_simplemap( "' . esc_js( $default_lat ) . '", "' . esc_js( $default_lng ) . '" );';

			// Load Locations
			$is_sm_search = isset( $_REQUEST['location_is_search_results'] ) ? 1 : 0;

			$to_display .= 'searchLocations( ' . absint( $is_sm_search ) . ' ); ';
			
			$to_display .= '});';
			$to_display .= '})(jQuery);';
			$to_display .= '</script>';
					
			return apply_filters( 'sm-display-map', $to_display, $atts );
		}
		
		// This function returns the location search form
		function location_search_form( $atts ) {
			global $post;
			
			// Grab default SimpleMap options
			$options = $this->get_default_options();

			// Merge default simplemap options with default shortcode options and provided shortcode options
			$atts = $this->parse_shortcode_atts( $atts );
			
			// Create individual vars for each att
			extract( $atts );

			// Array of the names for all taxonomies registered with sm-location post type
			$sm_tax_names = get_object_taxonomies( 'sm-location' );

			// Array of field names for this form (with label syntax stripped
			$form_field_names = $this->get_form_field_names_from_shortcode_atts( $search_fields );

			// Form onsubmit, action, and method values
			$on_submit = apply_filters( 'sm-location-search-onsubmit', ' onsubmit="searchLocations( 1 ); return false; "', $post->ID );
			$action = apply_filters( 'sm-locaiton-search-action', get_permalink(), $post->ID );
			$method = apply_filters( 'sm-location-search-method', 'post', $post->ID );			
			
			// Form Field Values
			$address_value 		= get_query_var( 'location_search_address' );
			$city_value 		= isset( $_REQUEST['location_search_city'] ) ? $_REQUEST['location_search_city'] : '';
			$state_value 		= isset( $_REQUEST['location_search_state'] ) ? $_REQUEST['location_search_state'] : '';
			$zip_value 			= get_query_var( 'location_search_zip' );
			$country_value 		= get_query_var( 'location_search_country' );
			$radius_value	 	= isset( $_REQUEST['location_search_distance'] ) ? $_REQUEST['location_search_distance'] : $radius;
			$limit_value		= isset( $_REQUEST['location_search_limit'] ) ? $_REQUEST['location_search_limit'] : $limit;
			$is_sm_search		= isset( $_REQUEST['location_is_search_results'] ) ? 1 : 0;

			// Normal Field inputs
			$ffi['street']		= array( 'label' => apply_filters( 'sm-search-label-street', __( 'Street: ', 'SimpleMap' ), $post ), 'input' => '<input type="text" id="location_search_address_field" name="location_search_address" value="' . esc_attr( $address_value ) . '" />' );
			$ffi['city']		= array( 'label' => apply_filters( 'sm-search-label-city', __( 'City: ', 'SimpleMap' ), $post ), 'input' => '<input type="text"  id="location_search_city_field" name="location_search_city" value="' . esc_attr( $city_value ) . '" />' );
			$ffi['state']		= array( 'label' => apply_filters( 'sm-search-label-state', __( 'State: ', 'SimpleMap' ), $post ), 'input' => '<input type="text" id="location_search_state_field" name="location_search_state" value="' . esc_attr( $state_value ) . '" />' );
			$ffi['zip']			= array( 'label' => apply_filters( 'sm-search-label-zip', __( 'Zip: ', 'SimpleMap' ), $post ), 'input' => '<input type="text" id="location_search_zip_field" name="location_search_zip" value="' . esc_attr( $zip_value ) . '" />' );
			$ffi['country']		= array( 'label' => apply_filters( 'sm-search-label-country', __( 'Country: ', 'SimpleMap' ), $post ), 'input' => '<input type="text" id="location_search_country_field" name="location_search_country" value="' . esc_attr( $country_value ) . '" />' );
			$ffi['empty']		= array( 'label' => '', 'input' => '' );
			$ffi['submit']		= array( 'label' => '', 'input' => '<input type="submit" value="' . apply_filters( 'sm-search-label-search', __('Search', 'SimpleMap'), $post ) . '" id="location_search_submit_field" class="submit" />' );
			$ffi['distance']	= $this->add_distance_field( $radius_value, $units );
			
			// Visible Taxonomy Field Inputs
			foreach ( $sm_tax_names as $tax_name ) {
			
				if ( in_array( $tax_name, $form_field_names ) && $this->show_taxonomy_filter( $atts, $tax_name ) )
					$ffi[$tax_name] = $this->add_taxonomy_fields( $atts, $tax_name );
				else
					$hidden_fields[] = '<input type="hidden" name="location_search_' . str_replace( '-', '_', $tax_name ) . '_field" value="" checked="checked" />';

			
			}

			// More Taxonomy Fields
			foreach ( $sm_tax_names as $tax_name ) {
			
				$hidden_fields[] = '<input type="hidden" id="avail_' . str_replace( '-', '_', $tax_name ) . '" value="' . $atts[str_replace( '-', '_', $tax_name )] . '" />';
			
			}
			
			// Hide search?
			$hidesearch = $hide_search ? " style='display:none;' " : '';

			$location_search  = '<div id="map_search" >';
			$location_search .= '<a id="map_top"></a>';
			$location_search .= '<form ' . $on_submit . ' name="location_search_form" id="location_search_form" action="' . $action . '" method="' . $method . '">';
						
			$location_search .= '<table class="location_search"' . $hidesearch . '>';

			$location_search .= apply_filters( 'sm-location-search-table-top', '', $post );

			$location_search .= '<tr><td colspan="' . $search_form_cols . '" class="location_search_title">' . apply_filters( 'sm-location-search-title', $search_title, $post->ID ) . '</td></tr>';
			
			// Loop through field inputs and print table
			$search_field_count = 1;
			$search_form_tr = 0;
			$search_field_td = 1;
			$search_fields = explode( '||', $search_fields);
			
			foreach( $search_fields as $field_key => $field_labelvalue ) {

				$skip_field = false;

				if ( 0 === $search_field_td ) {
					$search_field_td++;
					continue;
				}

				switch( substr( $field_labelvalue, 0, 8 ) ) {
				
					case 'labelbr_' :
					
						$field_label	= true;
						$field_br 		= '<br />';
						$field_value 	= substr( $field_labelvalue, 8 );
						break;
					
					case 'labelsp_' :
					
						$field_label	= true;
						$field_br		= '&nbsp';
						$field_value	= substr( $field_labelvalue, 8 );
						break;
							
					case 'labeltd_' :
										
						$field_label	= true;
						$field_br		= "</td>\n\t\t<td>";
						$field_value	= substr( $field_labelvalue, 8 );
						break;
							
					default :
					
						$field_label	= false;
						$field_br		= '';
						$field_value	= $field_labelvalue;
					
				}
				
				// Back compat for class names
				switch ( $field_value ) { 
					
					case 'sm-category' :
 						$class_value = 'cat';
 						break;
 					case 'sm-tag' :
 						$class_value = 'tag';
 						break;
 					case 'address' :
 						$class_value = 'street';
 						break;
 					default :
 						$class_value = $field_value;
				}
									
				// Print open TR if on column 1
				if ( 1 === $search_field_td ) {
					
					$search_form_tr_data = "\n\t<tr id='location_search_" . esc_attr( $search_form_tr ) . "_tr' >";
					$search_form_tr++;
				
				}
					
				// Print field for this position
				if ( isset( $ffi[$field_value] ) && 'empty' != $field_value ) {
					$search_form_tr_data .= "\n\t\t<td class='location_search_" . esc_attr( $class_value ) . "_cell location_search_cell'>";
					
					if ( "</td>\n\t\t<td>" == $field_br ) {
						$search_field_td++;
						$field_br = "</td>\n\t\t<td id='location_search_" . esc_attr( substr( $field_labelvalue, 8 ) ) . "_fields'>";
					}

					if ( $field_label )
						$search_form_tr_data .= isset( $ffi[$field_value]['label'] ) ? $ffi[$field_value]['label'] . $field_br : $field_br;
						
					$search_form_tr_data .= isset( $ffi[$field_value]['input'] ) ? $ffi[$field_value]['input'] . '</td>' : '</td>';
															
				} else {
				
					$search_form_tr_data .= "\n\t\t<td class='location_search_empty_cell location_search_cell'></td>";
					
				}
					
				// Print close TR if on column 3)
				if ( $search_form_cols == $search_field_td ) {
					$search_form_tr_data .= "\n\t</tr>";
					$search_field_td = 0;
					
					if ( strpos( $search_form_tr_data, 'input' ) || strpos( $search_form_tr_data, 'select' ) )
						$search_form_trs[$search_form_tr] = $search_form_tr_data;
			
				}
						
				// Bump search field count
				$search_field_count++;
				$search_field_td++;		
			
			}
			
			// Add table fields
			if ( isset( $search_form_trs ) )
				$location_search .= implode( ' ', (array) $search_form_trs );
			
			
			$location_search .= apply_filters( 'sm-location-search-before-submit', '', $post );
			
			$location_search .= '</table>';

			// Add hidden fields
			if ( isset( $hidden_fields ) )
			$location_search .= implode( ' ', (array) $hidden_fields );
			
			// Lat / Lng
			$location_search .= "<input type='hidden' id='location_search_default_lat' value='" . $default_lat . "' />";
			$location_search .= "<input type='hidden' id='location_search_default_lng' value='" . $default_lng . "' />";
			
			// Hidden value for limit
			$location_search .= "<input type='hidden' id='location_search_limit' value='" . $limit_value . "' />";
			
			// Hidden value set to true if we got here via search
			$location_search .= "<input type='hidden' id='location_is_search_results' name='sm-location-search' value='" . $is_sm_search . "' />";


			$location_search .= '</form>';
			$location_search .= '</div>'; // close map_search div
	
			return apply_filters( 'sm_location_search_form', $location_search, $atts );
			
		}
		
		// Separates form field names from label syntax attached to them when submitted via shortcode
		function get_form_field_names_from_shortcode_atts( $fields ) {

			// String to array
			$fields = explode( '||', $fields);

			foreach( $fields as $key => $field ) {

				switch( substr( $field, 0, 8 ) ) {
				
					case 'labelbr_' :
					
						$field_names[] 	= substr( $field, 8 );
						break;
					
					case 'labelsp_' :
					
						$field_names[]	= substr( $field, 8 );
						break;
							
					case 'labeltd_' :
					
						$field_names[]	= substr( $field, 8 );
						break;
							
					default :
					
						$field_names[]	= $field;
					
				}
				
			}
			
			return (array) $field_names;
		
		}
		
		// Determines if we're supposed to show this taxonomy's filter options in the form
		function show_taxonomy_filter( $atts, $tax_name ) {

			// Convert tax_name to PHP safe equiv
			$php_tax_name = str_replace( '-', '_', $tax_name );
		
			// Convert Given Taxonomy's 'show filter' into a generic one
			$key = 'show_' . $php_tax_name . '_filter';
			$show_taxes_filter = $atts[$key];

			if ( false != $show_taxes_filter && 'false' != $show_taxes_filter )
				return true;
			
			return false;
		
		}
		
		// Adds Distance field to form
		function add_distance_field( $radius_value, $units ) {
			global $post;
			
			// Distance
			$distance_input		= '<select id="location_search_distance_field" name="location_search_distance" >';
			foreach ( $this->get_search_radii() as $value ) {
				$r = (int) $value;
				$distance_input .= '<option value="' . $value . '"' . selected( $radius_value, $value, false ) . '>' . $value . ' ' . $units . "</option>\n";
			}
			$distance_input .= '</select>';
			
			return array( 'label' => apply_filters( 'sm-search-label-distance', __( 'Select a distance: ', 'SimpleMap' ), $post ), 'input' => $distance_input );
		
		
		}
		
		// Adds taxonomy fields to search form
		function add_taxonomy_fields( $atts, $taxonomy ) {
			global $post;
			
			// Get taxonomy object or return empty;
			if ( ! $tax_object = get_taxonomy( $taxonomy ) )
				return '';

			$options = $this->get_default_options();

			$atts = $this->parse_shortcode_atts( $atts );
			
			extract( $atts );
			
			$php_taxonomy = str_replace( '-', '_', $taxonomy );

			// Convert Specific Taxonomy var names and var values to Generic var names and var values
			$taxonomies 		= $atts[$php_taxonomy];
			$tax_hidden_name	= 'avail_' . $php_taxonomy;	
			$show_taxes_filter	= $atts['show_' . $php_taxonomy . '_filter'];
			$tax_field_name		= $php_taxonomy;

			// This originates at the comma separated list of taxonomy ids in the shortcode. ie: sm_category='1,3,5'
			$taxes_avail = $atts[$tax_hidden_name];
			
			// Place available taxes in array
			$taxes_avail = explode( ',', $taxes_avail );
			$taxes_array = array();
			
			// Loop through all cats and create array of available cats
			if ( $all_taxes = get_terms( $taxonomy ) ) {

				foreach ( $all_taxes as $key => $value ){
					if ( '' == $taxes_avail[0] || in_array( $value->term_id, $taxes_avail ) ) {
						$taxes_array[] = $value->term_id;
					}
				}
				
			}
			
			$taxes_avail = $taxes_array;

			// Show taxes filters if allowed
			$tax_search = '';
			$tax_label  = __( $tax_object->labels->singular_name . ': ', 'SimpleMap' );
			
			$taxes_array = apply_filters( 'sm-search-from-taxonomies', $taxes_array, $taxonomy );
			
			if ( 'checkboxes' == $taxonomy_field_type ) {
				// Print checkbox for each available cat
				foreach( $taxes_array as $key => $taxid ) {
					if( $term = get_term_by( 'id', $taxid, $taxonomy ) ) {
						$tax_checked = isset( $_REQUEST['location_search_' . $tax_field_name . '_' . esc_attr( $term->term_id ) . 'field'] ) ? ' checked="checked" ' : '';
						$tax_search .= '<label for="location_search_' . $tax_field_name . '_field_' . esc_attr( $term->term_id ) . '" class="no-linebreak"><input rel="location_search_' . $tax_field_name . '_field" type="checkbox" name="location_search_' . $tax_field_name . '_' . esc_attr( $term->term_id ) . 'field" id="location_search_' . $tax_field_name . '_field_' . esc_attr( $term->term_id ) . '" value="' . esc_attr( $term->term_id ) . '" ' . $tax_checked . '/> ' . esc_attr( $term->name ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> ';
					}
				}
			} elseif( 'select' == $taxonomy_field_type ) {
				// Print selectbox if that's what we're doing
				$tax_select  = "<select id='location_search_" . esc_attr( $tax_field_name ) . "_select' name='location_search_" . esc_attr( $tax_field_name ) . "_select' >";
				$tax_select .= "<option value=''>" . apply_filters( 'sm-search-tax-select-default', __( 'Select a value', 'SimpleMap' ), $taxonomy ) . "</option>";
				foreach( $taxes_array as $key => $taxid ) {
					if( $term = get_term_by( 'id', $taxid, $taxonomy ) ) {
						$tax_checked = isset( $_REQUEST['location_search_' . esc_attr( $tax_field_name ) . '_select' ] ) ? ' selected="selected" ' : '';
						$tax_select .= '<option rel="location_search_' . esc_attr( $tax_field_name ) . '_select_val"' . ' value="' . esc_attr( $term->term_id ) . '" ' . $tax_checked . '>' . esc_attr( $term->name ) . '</option>';
					}
				}
				$tax_select .= "</select>";
	
				if ( ! empty( $taxid ) )
					$tax_search .= $tax_select;
			}
										
			return array( 'label' => $tax_label, 'input' => $tax_search );
			
		}
		
		// This function enqueues all the javascript and stylesheets
		function enqueue_frontend_scripts_styles() {
			global $post;
			$options = $this->get_default_options();
			
			// Frontend only
			if ( ! is_admin() && is_object( $post ) ) {
			
				// Bail if we're not showing on all pages and this isn't a map page
				if ( ! in_array( $post->ID, explode( ',', $options['map_pages'] ) ) && ! in_array( 0, explode( ',', $options['map_pages'] ) ) )
					return false;
					
				// Check for use of custom stylesheet and load styles
				if ( strstr( $options['map_stylesheet'], 'simplemap-styles' ) )
					$style_url = plugins_url() . '/' . $options['map_stylesheet'];
				else
					$style_url = SIMPLEMAP_URL . '/' . $options['map_stylesheet'];
				
				// Load styles
				wp_enqueue_style( 'simplemap-map-style', $style_url );
						
				// Scripts
				wp_enqueue_script( 'simplemap-master-js', site_url() . '/?simplemap-master-js', array( 'jquery' ) );
		
				// Google API if we have a key
				if ( isset( $options['api_key'] ) && $options['api_key'] != '' )
					wp_enqueue_script( 'simplemap-google-api', 'http://maps.google' . $options['default_domain'] . '/maps?file=api&amp;v=2&amp;key=' . $options['api_key'] . '&amp;sensor=false' );
					
			}
		
		}
		
		// This function enqueues all the javascript and stylesheets
		function enqueue_backend_scripts_styles() {

			$options = $this->get_default_options();
			
			// Admin only
			if ( is_admin() ) {			
				wp_enqueue_style( 'simplemap-admin', SIMPLEMAP_URL . '/inc/styles/admin.css' );
				
				// SimpleMap General options
				if ( isset( $_GET['page'] ) && 'simplemap' == $_GET['page'] )
					wp_enqueue_script( 'simplemap-general-options-js', site_url() . '/?simplemap-general-options-js', array( 'jquery' ) );

				// Google API if we have a key
				if ( isset( $options['api_key'] ) && $options['api_key'] != '' )
					wp_enqueue_script( 'simplemap-google-api', 'http://maps.google' . $options['default_domain'] . '/maps?file=api&amp;v=2&amp;key=' . $options['api_key'] . '&amp;sensor=false' );
					
			}
		
		}			

		// JS Script for general options page
		function general_options_js_script() {

			if ( ! isset( $_GET['simplemap-general-options-js'] ) )
				return;

			header( "HTTP/1.1 200 OK" );
			header( "Content-type: application/x-javascript" );	
			$options = $this->get_default_options();
			
			do_action( 'sm-general-options-js' );
			?>
			function codeAddress() {
				// if this is modified, modify mirror function in master-js php function 
				geocoder = new GClientGeocoder();
				var d_address = document.getElementById("default_address").value;
				//alert(address);
					 geocoder.getLatLng(d_address, function(latlng) {
						document.getElementById("default_lat").value = latlng.lat();
						document.getElementById("default_lng").value = latlng.lng();
					 });
			}
			
			<?php
			die();
		}
		
		// This function prints the JS
		function google_map_js_script() {
		
			if ( ! isset( $_GET['simplemap-master-js'] ) )
				return;

			header( "HTTP/1.1 200 OK" );
			header( "Content-type: application/x-javascript" );	
			$options = $this->get_default_options();

			?>
			var default_lat 			= <?php echo esc_js( $options['default_lat'] ); ?>;
			var default_lng 			= <?php echo esc_js( $options['default_lng'] ); ?>;
			var default_radius 			= <?php echo esc_js( $options['default_radius'] ); ?>;
			var zoom_level 				= '<?php echo esc_js( $options['zoom_level'] ); ?>';
			var map_width 				= '<?php echo esc_js( $options['map_width'] ); ?>';
			var map_height 				= '<?php echo esc_js( $options['map_height'] ); ?>';
			var special_text 			= '<?php echo esc_js( $options['special_text'] ); ?>';
			var units 					= '<?php echo esc_js( $options['units'] ); ?>';
			var limit 					= '<?php echo esc_js( $options['results_limit'] ); ?>';
			var plugin_url 				= '<?php echo esc_js( SIMPLEMAP_URL ); ?>';
			var visit_website_text 		= '<?php echo apply_filters( 'sm-visit-website-text', __( 'Visit Website', 'SimpleMap' ) ); ?>';
			var get_directions_text		= '<?php echo apply_filters( 'sm-get-directions-text', __( 'Get Directions', 'SimpleMap' ) ); ?>';
			var location_tab_text		= '<?php echo apply_filters( 'sm-location-text', __( 'Location', 'SimpleMap' ) ); ?>';
			var description_tab_text	= '<?php echo apply_filters( 'sm-description-text', __( 'Description', 'SimpleMap' ) ); ?>';
			var phone_text				= '<?php echo apply_filters( 'sm-phone-text', __( 'Phone', 'SimpleMap' ) ); ?>';
			var fax_text				= '<?php echo apply_filters( 'sm-fax-text', __( 'Fax', 'SimpleMap' ) ); ?>';
			var email_text				= '<?php echo apply_filters( 'sm-email-text', __( 'Email', 'SimpleMap' ) ); ?>';
			
			<?php
			if ( $taxonomies = $this->get_sm_taxonomies( 'array', '', true, 'object' ) ) {
				
				foreach( $taxonomies as $taxonomy ) {
					?>
					var <?php echo $taxonomy->name; ?>_text		= '<?php echo apply_filters( $taxonomy->name . '-text', __( $taxonomy->labels->name, 'SimpleMap' ) ); ?>';
					<?php
				}
			}
			?>
			var noresults_text			= '<?php echo apply_filters( 'sm-no-results-found-text', __( 'No results found.', 'SimpleMap' ) ); ?>';
			var default_domain 			= '<?php echo esc_js( $options['default_domain'] ); ?>';
			var address_format 			= '<?php echo esc_js( $options['address_format'] ); ?>';
			var siteurl					= '<?php echo esc_js( site_url() ); ?>';
			var map;
			var geocoder;
			var autoload				= '<?php echo esc_js( $options['autoload'] ); ?>';

			function load_simplemap( lat, lng ) {
				<?php 
				if ( '' == $options['api_key'] ) {
					?>
					jQuery( "#simplemap" ).html( "<p style='padding:10px;'><?php printf( __( 'You must enter an API Key in <a href=\"%s\">General Settings</a> before your maps will work.', 'SimpleMap' ), admin_url( 'admin.php?page=simplemap' ) ); ?></p>" );
				<?php
				}
				
				do_action( 'sm-load-simplemap-js-top' );				
				?>
			  
				if ( lat == 0 )
					lat = '<?php echo esc_js( $options['default_lat'] ); ?>';
				
				if ( lng == 0 )
					lng = '<?php echo esc_js( $options['default_lng'] ); ?>';
				
				if ( GBrowserIsCompatible() ) {
				var latlng = new GLatLng( lat, lng );
				map = new GMap2( document.getElementById( 'simplemap') );
				map.setCenter( latlng, <?php echo esc_js( $options['zoom_level'] ); ?>, <?php echo esc_js( $options['map_type'] ); ?> );
				map.addControl( new GLargeMapControl3D() );
				map.addMapType( G_PHYSICAL_MAP );
				map.addControl( new GMenuMapTypeControl() );
				geocoder = new GClientGeocoder();
				}

				<?php
				do_action( 'sm-load-simplemap-js-bottom' );				
				?>
			}

			function codeAddress() {
				// if this is modified, modify mirror function in general-options-js php function 
				geocoder = new GClientGeocoder();
				var d_address = document.getElementById("default_address").value;
				//alert(address);
					 geocoder.getLatLng(d_address, function(latlng) {
						document.getElementById("default_lat").value = latlng.lat();
						document.getElementById("default_lng").value = latlng.lng();
					 });
			}
			
			function codeNewAddress() {
				if (document.getElementById("store_lat").value != '' && document.getElementById("store_lng").value != '') {
					document.new_location_form.submit();
				}
				else {
					geocoder = new GClientGeocoder();
					var address = '';
					var street = document.getElementById("store_address").value;
					var city = document.getElementById("store_city").value;
					var state = document.getElementById("store_state").value;
					var country = document.getElementById("store_country").value;
					
					if (street) { address += street + ', '; }
					if (city) { address += city + ', '; }
					if (state) { address += state + ', '; }
					address += country;
				
					 geocoder.getLatLng(address, function(latlng) {
						document.getElementById("store_lat").value = latlng.lat();
						document.getElementById("store_lng").value = latlng.lng();
						document.new_location_form.submit();
					 });
				}
			}
			
			function codeChangedAddress() {
				geocoder = new GClientGeocoder();
				var address = '';
				var street = document.getElementById("store_address").value;
				var city = document.getElementById("store_city").value;
				var state = document.getElementById("store_state").value;
				var country = document.getElementById("store_country").value;
				
				if (street) { address += street + ', '; }
				if (city) { address += city + ', '; }
				if (state) { address += state + ', '; }
				address += country;
			
				geocoder.getLatLng(address, function(latlng) {
					document.getElementById("store_lat").value = latlng.lat();
					document.getElementById("store_lng").value = latlng.lng();
				});
			}
			
			function searchLocations( is_search ) {

				// Set defaults for search form fields
				var address = '';
				var city 	= '';
				var state 	= ''
				var zip 	= '';
				var country = '';
				
				if ( null != document.getElementById('location_search_address_field') )
					address 	= document.getElementById('location_search_address_field').value;
				
				if ( null != document.getElementById('location_search_city_field') )
					city 		= document.getElementById('location_search_city_field').value;
				
				if ( null != document.getElementById('location_search_country_field') )
					country		= document.getElementById('location_search_country_field').value;;
				
				if ( null != document.getElementById('location_search_state_field') )
					state 		= document.getElementById('location_search_state_field').value;
				
				if ( null != document.getElementById('location_search_zip_field') )
					zip 		= document.getElementById('location_search_zip_field').value;
				
				if ( null != document.getElementById('location_search_distance_field') )
					var radius		= document.getElementById('location_search_distance_field').value;
				
				var lat 		= document.getElementById('location_search_default_lat').value;
				var lng 		= document.getElementById('location_search_default_lng').value;
				var limit		= document.getElementById('location_search_limit').value; 
				var searching	= document.getElementById('location_is_search_results').value;

				// Do SimpleMap Taxonomies
				<?php 
				if ( $taxnames = get_object_taxonomies( 'sm-location' ) ) {
				
					foreach ( $taxnames as $name ) {
						$php_name = str_replace( '-', '_', $name );
						?>

					 	// Do taxnonomy for checkboxes
					 	var <?php echo $php_name; ?> = '';
					 	var checks_found = false;
					 	jQuery( 'input[rel=location_search_<?php echo $php_name; ?>_field]' ).each( function() {
					 		checks_found = true;
					 		if ( jQuery( this ).attr( 'checked' ) && jQuery( this ).attr( 'value' ) != null ) {
					 			<?php echo $php_name; ?> += jQuery( this ).attr( 'value' ) + ',';
					 		}
					 	});

					 	// Do taxnonomy for select box if checks weren't found
						if ( false == checks_found ) {	
						 	jQuery( 'option[rel=location_search_<?php echo $php_name; ?>_select_val]' ).each( function() {
						 		if ( jQuery( this ).attr( 'selected' ) && jQuery( this ).attr( 'value' ) != null ) {
						 			<?php echo $php_name; ?> += jQuery( this ).attr( 'value' ) + ',';
						 		}
						 	});
						}
					 	
						<?php
					}
				}
				?>
				
				var query = '';
				var start = 0;
			 
				if ( address && address != '' )
			 		query += address + ', ';
			 
				if ( city && city != '' )
					query += city + ', ';
					
				if ( state && state != '' )
					query += state + ', ';
					
				if ( zip && zip != '' )
					query += zip + ', ';
					
				if ( country && country != '' )
					query += country + ', ';

				// Query
				if ( query != null )
					query = query.slice(0, -2);
				
				if ( limit == '' || limit == null )
					limit = 0;

				if ( radius == '' || radius == null )
					radius = 0;
				
				// Taxonomies
				<?php 
				if ( $taxnames = get_object_taxonomies( 'sm-location' ) ) {

					foreach ( $taxnames as $name ) {
						$php_name = str_replace( '-', '_', $name );
						?>

						if ( <?php echo $php_name; ?> != null )
							var _<?php echo $php_name; ?> = <?php echo $php_name; ?>.slice(0, -1);
						else
							var _<?php echo $php_name; ?> = '';
						
						// Append available taxes logic if no taxes are selected but limited taxes were passed through shortcode as available
						if ( '' != document.getElementById('avail_<?php echo $php_name; ?>').value && '' == _<?php echo $php_name; ?> )
							_<?php echo $php_name; ?> = 'OR,' + document.getElementById('avail_<?php echo $php_name; ?>').value;
						
						<?php
					}
				}
				?>

			 	// Load default location if query is empty
			 	if ( query == '' || query == null ) {
			 	
			 		if ( lat != 0 && lng != 0 )
			 			query = lat + ', ' + lng;
			 		else
			 			query = '<?php echo esc_js( $options['default_lat'] ); ?>, <?php echo esc_js( $options['default_lng'] ); ?>';
			 	
			 	}
			 	
			 	// Searching
			 	if ( 1 == searching || 1 == is_search ) {
			 		is_search = 1;
			 		var source = 'search';
			 	} else {
			 		is_search = 0;
			 		var source = 'initial_load';
			 	}

				geocoder.getLatLng( query, function( latlng ) {

					if ( 'none' != autoload || is_search ) {

					if ( 'all' == autoload && is_search != 1 ) {
							radius = '';
							limit = '';
						}

						if (! latlng) {
							latlng = new GLatLng( 44.9799654, -93.2638361 );

						} 
						var query_type = 'all';
						searchLocationsNear( latlng, query, source, "unlock", query_type, <?php echo $this->get_sm_taxonomies( 'string', '_', true ); ?>, address, city, state, zip, radius, limit );
					
					}
					
				});
			}
			
			function searchLocationsNear( center, homeAddress, source, mapLock, query_type, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, address, city, state, zip, radius, limit ) {

				// Radius
				if ( radius != null && radius != '' ) {
					radius = parseInt( radius );

					if ( units == 'km' ) {
					  	radius = parseInt( radius ) / 1.609344;
					}
					
				} else if ( autoload == 'all' ) {
				
					radius = '';
				
				} else {
					if ( units == 'mi' ) {
					  	radius = parseInt( default_radius );
					} else if ( units == 'km' ) {
					  	var radius = parseInt( default_radius ) / 1.609344;
					}
				}

				// Build search URL
				<?php 
				if ( $taxonomies = $this->get_sm_taxonomies( 'array', '', true ) ) {
					
					$js_tax_string = '';
					foreach( $taxonomies as $taxonomy ) {
						$js_tax_string .= "'&$taxonomy=' + $taxonomy + ";
					}

				}
				?>
				var searchUrl = siteurl + '/?sm-xml-search=1&lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius + '&namequery=' + homeAddress + '&query_type=' + query_type  + '&limit=' + limit + <?php echo $js_tax_string; ?>'&address=' + address + '&city=' + city + '&state=' + state + '&zip=' + zip;

				// Display Updating Message and hide search results
				jQuery( "#simplemap-updating" ).appendTo( map.getPane( G_MAP_FLOAT_SHADOW_PANE ) ).css( 'width', jQuery("#simplemap").width() + 'px' ).css( 'height', jQuery("#simplemap").height() + 'px' ).show();
				jQuery( "#results" ).html( '' );
				
				GDownloadUrl( searchUrl, function(data) {
					var xml = GXml.parse(data);
					var markers = xml.documentElement.getElementsByTagName('marker');
					
					// Hide Updating Message
					jQuery( "#simplemap-updating" ).hide();

					map.clearOverlays();
					
					var results = document.getElementById('results');
					results.innerHTML = '';
					if (markers.length == 0) {
						results.innerHTML = '<h3>' + noresults_text + '</h3>';
						map.setCenter( center, <?php echo esc_js( $options['zoom_level'] ); ?> );
						return;
					}
					
					var bounds = new GLatLngBounds();
					for (var i = 0; i < markers.length; i++ ) {
						var name = markers[i].getAttribute('name');
						var address = markers[i].getAttribute('address');
						var address2 = markers[i].getAttribute('address2');
						var city = markers[i].getAttribute('city');
						var state = markers[i].getAttribute('state');
						var zip = markers[i].getAttribute('zip');
						var country = markers[i].getAttribute('country');
						var distance = parseFloat(markers[i].getAttribute('distance'));
						var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')), parseFloat(markers[i].getAttribute('lng')));
						var url = markers[i].getAttribute('url');
						var phone = markers[i].getAttribute('phone');
						var fax = markers[i].getAttribute('fax');
						var email = markers[i].getAttribute('email');
						var special = markers[i].getAttribute('special');

						<?php
						if ( $jstaxes = $this->get_sm_taxonomies( 'array', '', true ) ) {
							
							foreach ( $jstaxes as $jstax ) {
								?>
								var <?php echo $jstax; ?> = markers[i].getAttribute('<?php echo $jstax; ?>');
								<?php
							}
						}
						?>
						
						var description = markers[i].getAttribute('description');
						
						var marker = createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, description);
						map.addOverlay(marker);
						var sidebarEntry = createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, email, url, special, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, description, point);
						results.appendChild(sidebarEntry);
						bounds.extend(point);
					}

					if (source == "search" || zoom_level == 0 ) {
						var myzoom = (map.getBoundsZoomLevel(bounds) );
						if ( myzoom > 18 )
							myzoom = 18;
						map.setCenter( bounds.getCenter(), myzoom );
					}
					
				});
			}
			
			function stringFilter(s) {
				filteredValues = "emnpxt%";     // Characters stripped out
				var i;
				var returnString = "";
				for (i = 0; i < s.length; i++) {  // Search through string and append to unfiltered values to returnString.
					var c = s.charAt(i);
					if (filteredValues.indexOf(c) == -1) returnString += c;
				}
				return returnString;
			}
			
			function createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, description) {
				
				// Allow plugin users to define Maker Options (including custom images)
				var markerOptions = false;
				if ( 'function' == typeof window.simplemapCustomMarkers )
					markerOptions = simplemapCustomMarkers( name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, description );
				
				if ( markerOptions )
					var marker = new GMarker( point, markerOptions );
				else
					var marker = new GMarker( point );
				
				var mapwidth = Number(stringFilter(map_width));
				var mapheight = Number(stringFilter(map_height));
				
				var maxbubblewidth = Math.round(mapwidth / 1.5);
				var maxbubbleheight = Math.round(mapheight / 2.2);
				
				var fontsize = 12;
				var lineheight = 12;

				<?php if ( in_array( 'sm_category', $jstaxes) ) : ?>
				var titleheight = 3 + Math.floor((name.length + sm_category.length) * fontsize / (maxbubblewidth * 1.5));
				<?php else : ?>
				var titleheight = 3 + Math.floor((name.length) * fontsize / (maxbubblewidth * 1.5));				
				<?php endif; ?>

				var addressheight = 2;
				if (address2 != '') {
					addressheight += 1;
				}
				if (phone != '' || fax != '') {
					addressheight += 1;
					if (phone != '') {
						addressheight += 1;
					}
					if (fax != '') {
						addressheight += 1;
					}
				}
				
				<?php 
				foreach ( $jstaxes as $jstax ) {
					echo 'var '.$jstax . 'height = 3 + Math.floor(('.$jstax.'.length) * fontsize / (maxbubblewidth * 1.5));';
				}
				?>
				var linksheight = 2;
				
				<?php // Tax heights minus categories
				$jstaxheights = '+ ';
				foreach ( $jstaxes as $jstax ) {
					if ( 'sm_category' == $jstax )
						continue;
					$jstaxheights .=  $jstax . 'height + ';
				}
				?>
				var totalheight = (titleheight + addressheight <?php echo $jstaxheights; ?>linksheight + 1) * fontsize;
					
				if (totalheight > maxbubbleheight) {
					totalheight = maxbubbleheight;
				}

				var html = '	<div class="markertext" style="height: ' + totalheight + 'px; overflow-y: auto; overflow-x: hidden;">';
				html += '		<h3 style="margin-top: 0; padding-top: 0; border-top: none;">';
				html += name;
				
				<?php if ( in_array( 'sm_category', $jstaxes) ) : ?>
				html += '<br /><span class="bubble_category">' + sm_category + '</span>';
				<?php endif; ?>
				
				html += '</h3>';
				
				html += '		<p>' + address;
								if (address2 != '') {
				html += '			<br />' + address2;
								}
								
								if (address_format == 'town, province postalcode') {
				html += '		<br />' + city + ', ' + state + ' ' + zip + '</p>';
								}
								else if (address_format == 'town province postalcode') {
				html += '		<br />' + city + ' ' + state + ' ' + zip + '</p>';
								}
								else if (address_format == 'town-province postalcode') {
				html += '		<br />' + city + '-' + state + ' ' + zip + '</p>';
								}
								else if (address_format == 'postalcode town-province') {
				html += '		<br />' + zip + ' ' + city + '-' + state + '</p>';
								}
								else if (address_format == 'postalcode town, province') {
				html += '		<br />' + zip + ' ' + city + ', ' + state + '</p>';
								}
								else if (address_format == 'postalcode town') {
				html += '		<br />' + zip + ' ' + city + '</p>';
								}
								else if (address_format == 'town postalcode') {
				html += '		<br />' + city + ' ' + zip + '</p>';
								}
								
								if (phone != '') {
				html += '			<p>' + phone_text + ': ' + phone;
									if (email != '') {
				html += '				<br />' + email_text + ': <a href="mailto:' + email + '">' + email + '</a>';
									}
									if (fax != '') {
				html += '				<br />' + fax_text + ': ' + fax;
									}
				html += '			</p>';
								}
								else if (fax != '') {
				html += '			<p>' + fax_text + ': ' + fax + '</p>';
								}
								
				html += '<p class="bubble_tags">';
				<?php
				foreach ( $jstaxes as $jstax ) {
					if ( 'sm_category' == $jstax )
						continue;
						?>		
						if (<?php echo $jstax; ?> != '') {
							html += <?php echo $jstax;?>_text + ': ' + <?php echo $jstax;?> + '<br />';
						}
					<?php
				}
				?>
				html += '</p>';

					var dir_address = point.toUrlValue(10);
					var dir_address2 = '';
					if (address) { dir_address2 += address; }
					if (city) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += city; };
					if (state) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += state; };
					if (zip) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += zip; };
					if (country) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += country; };

					if ( '' != dir_address2 ) { dir_address = point.toUrlValue(10) + '(' + escape( dir_address2 ) + ')'; };
								
				html += '		<p class="bubble_links"><a href="http://google' + default_domain + '/maps?q=' + homeAddress + ' to ' + dir_address + '" target="_blank">' + get_directions_text + '</a>';
								if (url != '') {
				html += '			&nbsp;|&nbsp;<a href="' + url + '" title="' + name + '" target="_blank">' + visit_website_text + '</a>';
								}
				html += '		</p>';
				html += '	</div>';
				
				if (description != '') {
					var numlines = Math.ceil(description.length / 40);
					var newlines = description.split('<br />').length - 1;
					var totalheight2 = 0;
					
					if ( description.indexOf('<img') == -1) {
						totalheight2 = (numlines + newlines + 1) * fontsize;
					}
					else {
						var numberindex = description.indexOf('height=') + 8;
						var numberend = description.indexOf('"', numberindex);
						var imageheight = Number(description.substring(numberindex, numberend));
						
						totalheight2 = ((numlines + newlines - 2) * fontsize) + imageheight;
					}
					
					if (totalheight2 > maxbubbleheight) {
						totalheight2 = maxbubbleheight;
					}
					
					var html2 = '	<div class="markertext" style="height: ' + totalheight2 + 'px; overflow-y: auto; overflow-x: hidden;">' + description + '</div>';
					
					GEvent.addListener(marker, 'click', function() {
						marker.openInfoWindowTabsHtml([new GInfoWindowTab(location_tab_text, html), new GInfoWindowTab(description_tab_text, html2)], {maxWidth: maxbubblewidth});
						window.location = '#map_top';
					});
				}
			
				else {
					GEvent.addListener(marker, 'click', function() {
						marker.openInfoWindowHtml(html, {maxWidth: maxbubblewidth});
						window.location = '#map_top';
					});
				}
				return marker;
			}
			
			function createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, email, url, special, <?php echo $this->get_sm_taxonomies( 'string', '', true ); ?>, description, point) {
			  var div = document.createElement('div');

			  // Beginning of result
			  var html = '<div class="result">';
			  
			  // Flagged special
			  if (special == 1 && special_text != '') {
			  	html += '<div class="special">' + special_text + '</div>';
			  }
			  
			  // Name & distance
			  html += '<div class="result_name">';
			  html += '<h3 style="margin-top: 0; padding-top: 0; border-top: none;">' + name;
			  
			  if (distance.toFixed(1) != 'NaN') {
			  	if (units == 'mi') {
				  	html+= ' <small>' + distance.toFixed(1) + ' miles</small>';
				}
			  	else if (units == 'km') {
				  	html+= ' <small>' + (distance * 1.609344).toFixed(1) + ' km</small>';
				}
			  }
			  html += '</h3></div>';
			  
			  // Address
			  html += '<div class="result_address"><address>' + address;
			  if (address2 != '') {
			  	html += '<br />' + address2;
			  }
			  
				if (address_format == 'town, province postalcode') {
					html += '<br />' + city + ', ' + state + ' ' + zip + '</address></div>';
				}
				else if (address_format == 'town province postalcode') {
					html += '<br />' + city + ' ' + state + ' ' + zip + '</address></div>';
				}
				else if (address_format == 'town-province postalcode') {
					html += '<br />' + city + '-' + state + ' ' + zip + '</address></div>';
				}
				else if (address_format == 'postalcode town-province') {
					html += '<br />' + zip + ' ' + city + '-' + state + '</address></div>';
				}
				else if (address_format == 'postalcode town, province') {
					html += '<br />' + zip + ' ' + city + ', ' + state + '</address></div>';
				}
				else if (address_format == 'postalcode town') {
					html += '<br />' + zip + ' ' + city + '</address></div>';
				}
				else if (address_format == 'town postalcode') {
					html += '<br />' + city + ' ' + zip + '</address></div>';
				}
			  
			  // Phone, email, and fax numbers
			  html += '<div class="result_phone">';
			  if (phone != '') {
			  	html += phone_text + ': ' + phone;
			  }
			  if (email != '') {
			  	html += '<br />' + email_text + ': <a href="mailto:' + email + '">' + email + '</a>';
			  }
			  if (fax != '') {
			  	html += '<br />' + fax_text + ': ' + fax;
			  }
			  html += '</div>';
			  
			  // Links section
			  html += '<div class="result_links">';
			  
			  // Visit Website link
			  html += '<div>';
			  if (url != 'http://' && url != '') {
			  	html += '<a href="' + url + '" title="' + name + '" target="_blank">' + visit_website_text + '</a>';
			  }
			  html += '</div>';
			  
			  // Get Directions link
			  if (distance.toFixed(1) != 'NaN') {

					var dir_address = point.toUrlValue(10);
					var dir_address2 = '';
					if (address) { dir_address2 += address; }
					if (city) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += city };
					if (state) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += state };
					if (zip) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += zip };
					if (country) { if ( '' != dir_address2 ) { dir_address2 += ' '; } dir_address2 += country };

					if ( '' != dir_address2 ) { dir_address += '(' + escape( dir_address2 ) + ')' };

				  html += '<a href="http://google' + default_domain + '/maps?q=' + homeAddress + ' to ' + dir_address + '" target="_blank">' + get_directions_text + '</a>';
			  }
			  html += '</div>';
			  
			  html += '<div style="clear: both;"></div>';
			  
			  // Taxonomy lists
			  <?php
			  foreach ( $jstaxes as $jstax ) {
				  ?>
				  if ( <?php echo $jstax; ?> != '' ) {
					  html += '<div class="<?php echo $jstax; ?>_list"><small><strong>' + <?php echo $jstax;?>_text + ':</strong> ' + <?php echo $jstax; ?> + '</small></div>';
				  }
			  	  <?php
			  }
			  ?>
			  
			  // End of result
			  html += '</div>';
			  
			  div.innerHTML = html;
			  div.style.cursor = 'pointer'; 
			  div.style.margin = 0;
			  GEvent.addDomListener(div, 'click', function() {
			    GEvent.trigger(marker, 'click');
			  });
			  GEvent.addDomListener(div, 'mouseover', function() {
			    //div.style.backgroundColor = '#eee';
			  });
			  GEvent.addDomListener(div, 'mouseout', function() {
			    //div.style.backgroundColor = '#fff';
			  });
			  return div;
			}
			<?php			
				
			die();
		}
		
		// This function geocodes a location
		function geocode_location( $address='', $city='', $state='', $zip='', $country='', $key='' ) {
			
			// Create URL encoded comma separated list of address elements that != ''
			$to_geocode = urlencode( implode( ', ', array_filter( compact( 'address', 'city', 'state', 'zip', 'country' ) ) ) );

			// Base URL
			$base_url = 'http://' . SIMPLEMAP_MAPS_HOST . '/maps/geo?output=json' . '&key=' . $key;
			
			// Add query
			$request_url = $base_url . "&q=" . $to_geocode;

			$response = wp_remote_get( $request_url );
			
			// TODO: Handle this situation better
			if ( ! is_wp_error( $response ) ) {

				$body = json_decode( $response['body'] );
				$status = $body->Status->code;
				
				if ( strcmp( $status, '200' ) == 0 ) {
					// Successful geocode
					//echo "<pre>";print_r( $body );die();
					$coordinates = $body->Placemark[0]->Point->coordinates;
					
					// Format: Longitude, Latitude, Altitude
					$lat = $coordinates[1];
					$lng = $coordinates[0];
				}
				
				return compact( 'body', 'status', 'lat', 'lng' );
				
			} else {
				return false;
			}

		}
		
		// Returns list of SimpleMap Taxonomies
		function get_sm_taxonomies( $format='array', $prefix='', $php_safe=false, $output='names' ) {

			$taxes = array();
		
			if ( $taxes = get_object_taxonomies( 'sm-location', $output ) ) {

				foreach( $taxes as $key => $tax ) {

					// Convert to PHP safe and add prefix
					if ( $php_safe && 'names' == $output )
						$taxes[$key] = str_replace( '-', '_', $prefix.$tax );
					elseif ( $php_safe )
						$taxes[$key]->name = str_replace( '-', '_', $prefix.$tax->name );
								
				}
						
			}	

			// Convert to string if needed
			if ( 'string' == $format )
				$taxes = implode( ', ', $taxes );

			return $taxes;
			
		}
		
		// This function returns the default SimpleMap options		
		function get_default_options() {

			$options = array(
				'map_width' => '100%',
				'map_height' => '350px',
				'default_lat' => '44.968684',
				'default_lng' => '-93.215561',
				'zoom_level' => '10',
				'default_radius' => '10',
				'map_type' => 'G_NORMAL_MAP',
				'special_text' => '',
				'default_state' => '',
				'default_country' => 'US',
				'default_domain' => '.com',
				'map_stylesheet' => 'inc/styles/light.css',
				'units' => 'mi',
				'autoload' => 'all',
				'lock_default_location' => false,
				'results_limit' => '20',
				'address_format' => 'town, province postalcode',
				'powered_by' => 0,
				'display_search' => 'show',
				'map_pages' => '0'
			);
			
			$saved = get_option( 'SimpleMap_options' );
			
			if ( !empty( $saved ) ) {
				foreach ( $saved as $key => $option )
					$options[$key] = $option;
			}
			
			if ( $saved != $options )
				update_option( 'SimpleMap_options', $options );
			return $options;
		}
		
		// Google Domains
		function get_domain_options(){
			$domains_list = array(
				'United States' => '.com',
				'Austria' => '.at',
				'Australia' => '.com.au',
				'Bosnia and Herzegovina' => '.com.ba',
				'Belgium' => '.be',
				'Brazil' => '.com.br',
				'Canada' => '.ca',
				'Switzerland' => '.ch',
				'Czech Republic' => '.cz',
				'Germany' => '.de',
				'Denmark' => '.dk',
				'Spain' => '.es',
				'Finland' => '.fi',
				'France' => '.fr',
				'Italy' => '.it',
				'Japan' => '.jp',
				'Netherlands' => '.nl',
				'Norway' => '.no',
				'New Zealand' => '.co.nz',
				'Poland' => '.pl',
				'Russia' => '.ru',
				'Sweden' => '.se',
				'Taiwan' => '.tw',
				'United Kingdom' => '.co.uk',
				'South Africa' => '.co.za'
			);
			
			return apply_filters( 'sm-domain-list', $domains_list );
		}
		
		// Country list
		function get_country_options(){
			$country_list = array(
				'US' => 'United States',
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia and Herzegowina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei Darussalam',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CD' => 'Congo, The Democratic Republic of the',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'CI' => 'Cote D\'Ivoire',
				'HR' => 'Croatia (Local Name: Hrvatska)',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'TP' => 'East Timor',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands (Malvinas)',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'FX' => 'France, Metropolitan',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard and Mc Donald Islands',
				'VA' => 'Holy See (Vatican City State)',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran (Islamic Republic of)',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KP' => 'Korea, Democratic People\'s Republic of',
				'KR' => 'Korea, Republic of',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Lao People\'s Democratic Republic',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libyan Arab Jamahiriya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macau',
				'MK' => 'Macedonia, Former Yugoslav Republic of',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia, Federated States of',
				'MD' => 'Moldova, Republic of',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'VC' => 'Saint Vincent and The Grenadines',
				'WS' => 'Samoa',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome And Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia (Slovak Republic)',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia, South Sandwich Islands',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SH' => 'St. Helena',
				'PM' => 'St. Pierre and Miquelon',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard and Jan Mayen Islands',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania, United Republic of',
				'TH' => 'Thailand',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad and Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks and Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom',
				'UM' => 'United States Minor Outlying Islands',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'VG' => 'Virgin Islands (British)',
				'VI' => 'Virgin Islands (U.S.)',
				'WF' => 'Wallis and Futuna Islands',
				'EH' => 'Western Sahara',
				'YE' => 'Yemen',
				'YU' => 'Yugoslavia',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			);
			
			return apply_filters( 'sm-country-list', $country_list );
		}
		
		// Echo the toolbar
		function show_toolbar( $title = '' ) {
			global $simple_map;
			$options = $simple_map->get_default_options();
			if ( '' == $title )
				$title = 'SimpleMap';
			?>
			<table class="sm-toolbar" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td class="sm-page-title">
						<h2><?php _e( $title, 'SimpleMap' ); ?></h2>
					</td>
					<td class="sm-toolbar-item">
						<a href="http://simplemap-plugin.com" target="_blank" title="<?php _e( 'Go to the SimpleMap Home Page', 'SimpleMap' ); ?>"><?php _e( 'SimpleMap Home Page', 'SimpleMap' ); ?></a>
					</td>
					<td class="sm-toolbar-item">
						<a href="<?php echo admin_url( 'admin.php?page=simplemap-help' ); ?>" title="<?php _e( 'Premium Support', 'SimpleMap' ); ?>"><?php _e( 'Premium Support', 'SimpleMap' ); ?></a>
					</td>
					<td class="sm-toolbar-item">
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="DTJBYXGQFSW64">
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</td>
				</tr>
			</table>

			<?php
			if ( !isset( $options['api_key'] ) || $options['api_key'] == '' )
				echo '<div class="error"><p>' . __( 'You must enter an API key for your domain.', 'SimpleMap' ).' <a href="' . admin_url( 'admin.php?page=simplemap' ) . '">' . __( 'Enter a key on the General Options page.', 'SimpleMap' ) . '</a></p></div>';	
		}
		
		// Return the available search_radii
		function get_search_radii(){
			$search_radii = array( 1, 5, 10, 25, 50, 100, 500, 1000 );
			return apply_filters( 'sm-search-radii', $search_radii );
		}
		
		// What link are we using for google's API
		function get_api_link() {
			$lo = str_replace('_', '-', get_locale());
			$l = substr($lo, 0, 2);
			switch($l) {
				case 'es':
				case 'de':
				case 'ja':
				case 'ko':
				case 'ru':
					$api_link = "http://code.google.com/intl/$l/apis/maps/signup.html";
					break;
				case 'pt':
				case 'zh':
					$api_link = "http://code.google.com/intl/$lo/apis/maps/signup.html";
					break;
				case 'en':
				default:
					$api_link = "http://code.google.com/apis/maps/signup.html";
					break;
			}
			return $api_link;
		}
		
		// Returns true if legacy tables exist in the DB
		function legacy_tables_exist() {
			global $wpdb;
			
			$sql = "SHOW TABLES LIKE '" . $wpdb->prefix . "simple_map'";
			if ( $tables = $wpdb->get_results( $sql ) ) {
				return true;
			}
			
			return false;
		}
		
		// Search form / widget query vars
		function register_query_vars( $vars ) {
				
			$vars[] = 'location_search_address';
			$vars[] = 'location_search_city';
			$vars[]	= 'location_search_state';
			$vars[] = 'location_search_zip';
			$vars[] = 'location_search_distance';
			$vars[] = 'location_search_limit';
			$vars[] = 'location_is_search_results';

			return $vars;
		}
		
		/**
		 * Parses the shortcode attributes with the default options and returns array
		 *
		 * @since 2.3
		 */
		function parse_shortcode_atts( $shortcode_atts ) {
		
			$options			= $this->get_default_options();
			$default_atts		= $this->get_default_shortcode_atts();
			$atts			 	= shortcode_atts( $default_atts, $shortcode_atts );

			// If deprecated shortcodes were used, replace with current ones
			if ( ! is_null( $atts['show_categories_filter'] ) )
				$atts['show_sm_category_filter'] = $atts['show_categories_filter'];
			if ( ! is_null( $atts['show_tags_filter'] ) )
				$atts['show_sm_tag_filter'] = $atts['show_tags_filter'];
			if ( ! is_null( $atts['categories'] ) )
				$atts['sm_category'] = $atts['categories'];			
			if ( ! is_null( $atts['tags'] ) )
				$atts['sm_tag'] = $atts['tags'];			

			// Determine if we need to hide the search form or not
			if ( '' == $atts['hide_search'] ) {
				
				// Use default value
				if ( 'show' == $options['display_search'] )
					$atts['hide_search'] = 0;
				else
					$atts['hide_search'] = 1;
			
			} 

			// Set categories and tags to available equivelants 
			$atts['avail_sm_category'] 	= $atts['sm_category'];
			$atts['avail_sm_tag'] 		= $atts['sm_tag'];
		
			// Default lat / lng from shortcode?
			if ( ! $atts['default_lat'] ) 
				$atts['default_lat'] = $options['default_lat'];
			if ( ! $atts['default_lng'] )
				$atts['default_lng'] = $options['default_lng'];
			
			// Doing powered by?
			if ( '' == $atts['powered_by'] ) {
				
				// Use default value
				$atts['powered_by'] = $options['powered_by'];
			
			} else {
				
				// Use shortcode
				if ( 0 == $atts['powered_by'] )
					$atts['powered_by'] = 0;
				else
					$atts['powered_by'] = 1;
				
			}
			
			// Default units or shortcode units?
			if ( 'km' != $atts['units'] && 'mi' != $atts['units'] )
				$atts['units'] = $options['units'];

			// Default radius or shortcode radius?
			if ( '' != $atts['radius'] && in_array( $atts['radius'], $this->get_search_radii() ) )
				$atts['radius'] = absint( $atts['radius'] );
			else
				$atts['radius'] = $options['default_radius'];
		
			//Make sure we have limit
			if ( '' == $atts['limit'] )
				$atts['limit'] = $options['results_limit'];
			
			// Clean search_field_cols
			if ( 0 === absint( $atts['search_form_cols'] ) )
				$atts['search_form_cols'] = $default_atts['search_form_cols'];
			
			// Return final array
			return $atts;
		}
		
		/**
		 * Returns default shortcode attributes
		 *
		 * @since 2.3
		 */
		function get_default_shortcode_atts() {
		
			$atts = array(
				
				'search_title' 				=> __( 'Find Locations Near:', 'SimpleMap' ), 
				'sm_category' 				=> '', 
				'sm_tag' 					=> '', 
				'search_form_type'			=> 'table', 
				'search_form_cols'			=> 3, 
				'search_fields'				=> 'labelbr_street||labelbr_city||labelbr_state||labelbr_zip||empty||empty||labeltd_distance||empty||labeltd_sm-category||empty||labeltd_sm-tag||empty||labeltd_sm-day||empty||labeltd_sm-time||empty||submit||empty||empty', 
				'show_sm_category_filter' 	=> 1, 
				'show_sm_tag_filter'		=> 1, 
				'taxonomy_field_type'		=> 'checkboxes',
				'hide_search' 				=> '', 
				'hide_map' 					=> 0, 
				'hide_list' 				=> 0, 
				'default_lat' 				=> 0, 
				'default_lng' 				=> 0, 
				'map_width' 				=> '', 
				'map_height' 				=> '', 
				'units' 					=> '',
				'radius'					=> '',
				'limit'						=> '',
				'autoload'					=> '',
				'zoom_level' 				=> '',
				'map_type'					=> '',
				'powered_by'				=> '', 
				// The following are deprecated. Don't use them.
				'categories' 				=> null, 
				'tags' 						=> null, 
				'show_categories_filter' 	=> null, 
				'show_tags_filter' 			=> null 

			);
			
			return apply_filters( 'sm-default-shortcode-atts', $atts );
		}

		// This function filters category text labels
		function backwards_compat_categories_text( $text ) {
			return __( 'Categories', 'SimpleMap' );
		}

		// This function filters category text labels
		function backwards_compat_tags_text( $text ) {
			return __( 'Tags', 'SimpleMap' );
		}

	}	
}
