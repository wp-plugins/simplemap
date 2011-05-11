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
			add_action( 'init', array( &$this, 'google_map_js_script' ) );

			// Add hook for general options js file
			add_action( 'init', array( &$this, 'general_options_js_script' ) );
			
			// Query vars
			add_filter( 'query_vars', array( &$this, 'register_query_vars' ) );
		}
				
		// This function generates the code to display the map
		function display_map( $atts ) {

			$options = $this->get_default_options();
			$default_shortcode_atts = array( 'search_title' => __( 'Find Locations Near:', 'SimpleMap' ), 'categories' => '', 'tags' => '', 'show_categories_filter' => 1, 'show_tags_filter' => 1, 'hide_map' => 0, 'hide_list' => 0, 'default_lat' => 0, 'default_lng' => 0 );
			
			$atts = shortcode_atts( $default_shortcode_atts, $atts );

			extract( $atts );

			// Set categories and tags to available equivelants 
			$cats_avail = ( '' == $categories ) ? $categories : 'OR,' . $categories;
			$tags_avail = ( '' == $tags ) ? $tags : 'OR,' . $tags;

			$to_display = '';
			
			if ( $options['display_search'] == 'show' ) {
				
				$to_display .= $this->location_search_form( $atts );

			}
			
			if ( isset( $options['powered_by'] ) && 1 == $options['powered_by'] ) {
				$to_display .= '<div id="powered_by_simplemap">' . sprintf( __( 'Powered by %s SimpleMap', 'SimpleMap' ), '<a href="http://simplemap-plugin.com/" target="_blank">' ) . '</a></div>';
			}

			// Hide map?
			$hidemap = $hide_map ? "display:none; " : '';

			// Hide list?
			$hidelist = $hide_list ? "display:none; " : '';
			
			$to_display .= '<div id="simplemap" style="' . $hidemap . 'width: ' . $options['map_width'] . '; height: ' . $options['map_height'] . ';"></div>';
			$to_display .= '<div id="results" style="' . $hidelist . 'width: ' . $options['map_width'] . ';"></div>';
			$to_display .= '<script type="text/javascript">';
			$to_display .= '(function($) { ';
			$to_display .= '$(document).ready(function() {';
			$to_display .= '	load_simplemap( "' . esc_js( $default_lat ) . '", "' . esc_js( $default_lng ) . '");';

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
			
			$options = $this->get_default_options();
			$default_shortcode_atts = array( 'search_title' => __( 'Find Locations Near:', 'SimpleMap' ), 'categories' => '', 'tags' => '', 'show_categories_filter' => 1, 'show_tags_filter' => 1, 'hide_map' => 0, 'hide_list' => 0, 'default_lat' => 0, 'default_lng' => 0 );
			
			$atts = shortcode_atts( $default_shortcode_atts, $atts );

			$atts = apply_filters( 'sm-location-search-atts', $atts, $post );
			
			extract( $atts );

			// Set categories and tags to available equivelants 
			$cats_avail = $categories;
			$tags_avail = $tags;
			
			// Form onsubmit, action, and method values
			$on_submit = apply_filters( 'sm-location-search-onsubmit', ' onsubmit="searchLocations( 1 ); return false; "', $post->ID );
			$action = apply_filters( 'sm-locaiton-search-method', get_permalink(), $post->ID );
			$method = apply_filters( 'sm-location-search-method', 'post', $post->ID );			
			
			// Form Field Values
			$address_value 		= get_query_var( 'location_search_address' );
			$city_value 		= isset( $_REQUEST['location_search_city'] ) ? $_REQUEST['location_search_city'] : '';
			$state_value 		= isset( $_REQUEST['location_search_state'] ) ? $_REQUEST['location_search_state'] : '';
			$zip_value 			= get_query_var( 'location_search_zip' );
			$radius_value	 	= isset( $_REQUEST['location_search_distance'] ) ? $_REQUEST['location_search_distance'] : $options['default_radius'];
			$limit_value		= isset( $_REQUEST['location_search_limit'] ) ? $_REQUEST['location_search_limit'] : $options['results_limit'];
			$is_sm_search		= isset( $_REQUEST['location_is_search_results'] ) ? 1 : 0;
			
			$location_search  = '<div id="map_search" >';
			$location_search .= '<a name="map_top"></a>';
			$location_search .= '<form ' . $on_submit . 'name="location_search_form" id="location_search_form" action="' . $action . '" method="' . $method . '">';
			$location_search .= '<table class="location_search">';

			$location_search .= apply_filters( 'sm-location-search-table-top', '', $post );

			$location_search .= '<tr><td colspan="3" class="location_search_title">' . apply_filters( 'sm-location-search-title', $search_title, $post->ID ) . '</td></tr>';
			$location_search .= '<tr><td class="location_search_address_cell location_search_cell">' . __( 'Street', 'SimpleMap' ) . ':<br /><input type="text" id="location_search_address_field" name="location_search_address" value="' . esc_attr( $address_value ) . '" /></td>';
			$location_search .= '<td class="location_search_city_cell location_search_cell">' . __( 'City', 'SimpleMap' ) . ':<br /><input type="text"  id="location_search_city_field" name="location_search_city" value="' . esc_attr( $city_value ) . '" /></td>';
			$location_search .= '<td class="location_search_state_cell location_search_cell">' . __( 'State', 'SimpleMap' ) . ':<br /><input type="text" id="location_search_state_field" name="location_search_state" value="' . esc_attr( $state_value ) . '" /></td>';
			$location_search .= '</tr><tr>';
			$location_search .= '<td class="location_search_zip_cell location_search_cell">' . __( 'Zip', 'SimpleMap' ) . ':<br /><input type="text" id="location_search_zip_field" name="location_search_zip" value="' . esc_attr( $zip_value ) . '" /></td>';
			$location_search .= '<td colspan="2"></td>';
			$location_search .= '</tr><tr>';
			$location_search .= '<td class="location_search_distance_cell location_search_cell">' . __( 'Select a distance', 'SimpleMap' ) . ':</td><td colspan="2"><select id="location_search_distance_field" name="location_search_distance" >';
			

			foreach ( $this->get_search_radii() as $value ) {
				$r = (int) $value;
				$location_search .= '<option value="' . $value . '"' . selected( $radius_value, $value, false ) . '>' . $value . ' ' . $options['units'] . "</option>\n";
			}
		
			$location_search .= '</select></td></tr>';
			
			// Place available cats in array
			$cats_avail = explode( ',', $cats_avail );
			$cats_array = array();
			
			// Loop through all cats and create array of available cats
			if ( $all_cats = get_terms( 'sm-category' ) ) {

				foreach ( $all_cats as $key => $value ){
					if ( '' == $cats_avail[0] || in_array( $value->term_id, $cats_avail ) ) {
						$cats_array[] = $value->term_id;
					}
				}
				
			}
			
			$cats_avail = $cats_array;

			// Show category filters if allowed
			if ( false != $show_categories_filter && 'false' != $show_categories_filter && ! empty( $cats_avail ) ) {
				$cat_search = '<tr><td class="location_search_category_cell location_search_cell">' . __( 'Categories', 'SimpleMap' ) . ':</td>';
				$cat_search .= '<td colspan="2">';
				
				// Print checkbox for each available cat
				foreach( $cats_array as $key => $catid ) {
					if( $term = get_term_by( 'id', $catid, 'sm-category' ) ) {
						$cat_checked = isset( $_REQUEST['location_search_categories_' . esc_attr( $term->term_id ) . 'field'] ) ? ' checked="checked" ' : '';
						$cat_search .= '<label for="location_search_categories_field_' . esc_attr( $term->term_id ) . '" class="no-linebreak"><input rel="location_search_categories_field" type="checkbox" name="location_search_categories_' . esc_attr( $term->term_id ) . 'field" id="location_search_categories_field_' . esc_attr( $term->term_id ) . '" value="' . esc_attr( $term->term_id ) . '" ' . $cat_checked . '/> ' . esc_attr( $term->name ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> ';
					}
				}
				
				$cat_search .= '</td></tr>';
			} else {
				
				// Default cats_selected is none
				$cat_search = '<input type="hidden" name="location_search_categories_field" value="" checked="checked" />';

			}
			
			// Hidden field for available cats. We'll need this in the event that nothing is selected
			$cat_search .= '<input type="hidden" id="avail_cats" value="' . $categories . '" />';
			
			$cat_search = apply_filters( 'sm-location-cat-search', $cat_search, $post );
			$location_search .= $cat_search;
			
			// Place available tags in array
			$tags_avail = explode( ',', $tags_avail );
			$tags_array = array();
			
			// Loop through all tags and create array of available tags
			if ( $all_tags = get_terms( 'sm-tag' ) ) {

				foreach ( $all_tags as $key => $value ){
					if ( '' == $tags_avail[0] || in_array( $value->term_id, $tags_avail ) ) {
						$tags_array[] = $value->term_id;
					}
				}
				
			}
			
			$tags_avail = $tags_array;

			// Show tag filters if allowed
			if ( false != $show_tags_filter && 'false' != $show_tags_filter && $all_tags ) {
				$tag_search = '<tr><td class="location_search_tag_cell location_search_cell">' . __( 'Tags', 'SimpleMap' ) . ':</td>';
				$tag_search .= '<td colspan="2">';
				
				// Print checkbox for each available tag
				foreach( $tags_array as $key => $tagid ) {
					if( $term = get_term_by( 'id', $tagid, 'sm-tag' ) ) {
						$tag_checked = isset( $_REQUEST['location_search_tags_' . esc_attr( $term->term_id ) . 'field'] ) ? ' checked="checked" ' : '';
						$tag_search .= '<label for="location_search_tags_field_' . esc_attr( $term->term_id ) . '" class="no-linebreak"><input rel="location_search_tags_field" type="checkbox" name="location_search_tags_' . esc_attr( $term->term_id ) . 'field" id="location_search_tags_field_' . esc_attr( $term->term_id ) . '" value="' . esc_attr( $term->term_id ) . '" ' . $tag_checked . '/> ' . esc_attr( $term->name ) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label> ';
					}
				}
				
				$tag_search .= '</td></tr>';
			} else {
				
				// Default tag_selected is none
				$tag_search = '<input type="hidden" name="location_search_tags_field" value="" checked="checked" />';

			}
			
			// Hidden field for available tags. We'll need this in the event that nothing is selected
			$tag_search .= '<input type="hidden" id="avail_tags" value="' . esc_attr( $tags ) . '" />';

			$tag_search = apply_filters( 'sm-location-tag-search', $tag_search, $post );
			$location_search .= $tag_search;
			
			// Default lat / lng from shortcode?
			if ( ! $default_lat ) 
				$default_lat = $options['default_lat'];
			if ( ! $default_lng )
				$default_lng = $options['default_lng'];
			
			$location_search .= "<input type='hidden' id='location_search_default_lat' value='" . $default_lat . "' />";
			$location_search .= "<input type='hidden' id='location_search_default_lng' value='" . $default_lng . "' />";
			
			// Hidden value for limit
			$location_search .= "<input type='hidden' id='location_search_limit' value='" . $limit_value . "' />";
			
			// Hidden value set to true if we got here via search
			$location_search .= "<input type='hidden' id='location_is_search_results' name='sm-location-search' value='" . $is_sm_search . "' />";
			
			$location_search .= apply_filters( 'sm-location-search-before-submit', '', $post );
			
			$location_search .= '<tr><td colspan="3" class="location_search_submit_cell location_search_cell"> <input type="submit" value="' . __('Search', 'SimpleMap') . '" id="location_search_submit_field" class="submit" /></td>';
			$location_search .= '</tr></table>';
			$location_search .= '</form>';
			$location_search .= '</div>'; // close map_search div
	
			return apply_filters( 'sm_location_search_form', $location_search, $atts );
			
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
					$style_url = WP_CONTENT_URL . '/plugins/' . $options['map_stylesheet'];
				else
					$style_url = SIMPLEMAP_URL . '/' . $options['map_stylesheet'];
				
				// Load styles
				wp_enqueue_style( 'simplemap-map-style', $style_url );
						
				// Scripts
				wp_enqueue_script( 'simplemap-master-js', get_option( 'siteurl' ) . '/?simplemap-master-js', array( 'jquery' ) );
		
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
					wp_enqueue_script( 'simplemap-general-options-js', get_option( 'siteurl' ) . '/?simplemap-general-options-js', array( 'jquery' ) );

				// Google API if we have a key
				if ( isset( $options['api_key'] ) && $options['api_key'] != '' )
					wp_enqueue_script( 'simplemap-google-api', 'http://maps.google' . $options['default_domain'] . '/maps?file=api&amp;v=2&amp;key=' . $options['api_key'] . '&amp;sensor=false' );
					
			}
		
		}			

		// JS Script for general options page
		function general_options_js_script() {

			if ( ! isset( $_GET['simplemap-general-options-js'] ) )
				return;

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

			header( "Content-type: application/x-javascript" );	
			$options = $this->get_default_options();

			if ( ( isset( $options['autoload'] ) && 'some' == $options['autoload'] || 'all' == $options['autoload'] ) )
				$autozoom = $options['zoom_level'];
			else
				$autozoom = 'false';

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
			var visit_website_text 		= '<?php echo __( 'Visit Website', 'SimpleMap' ); ?>';
			var get_directions_text		= '<?php echo __( 'Get Directions', 'SimpleMap' ); ?>';
			var location_tab_text		= '<?php echo __( 'Location', 'SimpleMap' ); ?>';
			var description_tab_text	= '<?php echo __( 'Description', 'SimpleMap' ); ?>';
			var phone_text				= '<?php echo __( 'Phone', 'SimpleMap' ); ?>';
			var fax_text				= '<?php echo __( 'Fax', 'SimpleMap' ); ?>';
			var tags_text				= '<?php echo __( 'Tags', 'SimpleMap' ); ?>';
			var noresults_text			= '<?php echo __( 'No results found.', 'SimpleMap' ); ?>';
			var autozoom 				= <?php echo esc_js( $autozoom ); ?>;
			var default_domain 			= '<?php echo esc_js( $options['default_domain'] ); ?>';
			var address_format 			= '<?php echo esc_js( $options['address_format'] ); ?>';
			var siteurl					= '<?php echo esc_js( get_option( 'siteurl' ) ); ?>';
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
			
				var address 	= document.getElementById('location_search_address_field').value;
				var city 		= document.getElementById('location_search_city_field').value;
				//var county		= '';
				var state 		= document.getElementById('location_search_state_field').value;
				var zip 		= document.getElementById('location_search_zip_field').value;
				var radius		= document.getElementById('location_search_distance_field').value;
				var lat 		= document.getElementById('location_search_default_lat').value;
				var lng 		= document.getElementById('location_search_default_lng').value;
				var limit		= document.getElementById('location_search_limit').value; 
				var searching	= document.getElementById('location_is_search_results').value;

			 	// Do categories selected
			 	var cats = '';
			 	jQuery( 'input[rel=location_search_categories_field]' ).each( function() {
			 		if ( jQuery( this ).attr( 'checked' ) && jQuery( this ).attr( 'value' ) != null ) {
			 			cats += jQuery( this ).attr( 'value' ) + ',';
			 		}
			 	});

			 	// Do tags selected
			 	var taggers = '';
			 	jQuery( 'input[rel=location_search_tags_field]' ).each( function() {
			 		if ( jQuery( this ).attr( 'checked' ) && jQuery( this ).attr( 'value' ) != null ) {
			 			taggers += jQuery( this ).attr( 'value' ) + ',';
			 		}
			 	});

				var query = '';
				var start = 0;
			 
				if ( address != '' )
			 		query += address + ', ';
			 
				if ( city != '' )
					query += city + ', ';
					
				//if ( county != '' )
				//	query += county + ', ';

				if ( state != '' )
					query += state + ', ';
					
				if ( zip != '' )
					query += zip + ', ';
					
				// Query
				if ( query != null )
					query = query.slice(0, -2);
				
				if ( limit == '' || limit == null )
					limit = 0;

				if ( radius == '' || radius == null )
					radius = 0;
										
				// Categories
				if ( cats != null )
					categories = cats.slice(0, -1);
				else
					categories = '';
				
				// Append available cats logic if no cats are selected but limited cats were passed through shortcode as available
				if ( '' != document.getElementById('avail_cats').value && '' == categories )
					categories = 'OR,' + document.getElementById('avail_cats').value;

				// Tags
				if ( taggers != null )
					tags = taggers.slice(0, -1);
				else
					tags = '';
				
				// Append available tags logic if no tags are selected but limited tags were passed through shortcode as available
				if ( '' != document.getElementById('avail_tags').value && '' == tags )
					tags = 'OR,' + document.getElementById('avail_tags').value;

			 	// Load default location if query is empty
			 	if ( query == '' || query == null ) {
			 	
			 		if ( lat != 0 && lng != 0 )
			 			query = lat + ', ' + lng;
			 		else
			 			query = '<?php echo esc_js( $options['default_lat'] ); ?>, <?php echo esc_js( $options['default_lng'] ); ?>';
			 	
			 	}
			 	
			 	// Searching
			 	if ( 1 == searching )
			 		is_search = 1;


				geocoder.getLatLng( query, function( latlng ) {

					if ( 'none' != autoload || is_search ) {
				
						if (! latlng) {
							latlng = new GLatLng( 44.9799654, -93.2638361 );
							searchLocationsNear( latlng, query, "search", "unlock", categories, tags, address, city, state, zip, radius, limit );
						} else {
							searchLocationsNear( latlng, query, "search", "unlock", categories, tags, address, city, state, zip, radius, limit );
						}
					
					}
					
				});
			}
			
			function searchLocationsNear( center, homeAddress, source, mapLock, categories, tags, address, city, state, zip, radius, limit ) {

				// Radius
				if ( radius != null && radius != '' ) {
					radius = parseInt( radius );
					if ( units == 'km' ) {
					  	radius = parseInt( radius ) / 1.609344;
					}
					
				} else {
					if ( units == 'mi' ) {
					  	radius = parseInt( default_radius );
					} else if ( units == 'km' ) {
					  	var radius = parseInt( default_radius ) / 1.609344;
					}
				}

				// Build search URL
				var searchUrl = siteurl + '?sm-xml-search=1&lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius + '&namequery=' + homeAddress + '&limit=' + limit + '&categories=' + categories + '&tags=' + tags + '&address=' + address + '&city=' + city + '&state=' + state + '&zip=' + zip;

				GDownloadUrl( searchUrl, function(data) {
					var xml = GXml.parse(data);
					var markers = xml.documentElement.getElementsByTagName('marker');
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
						var categories = markers[i].getAttribute('categories');
						var tags = markers[i].getAttribute('tags');
						var description = markers[i].getAttribute('description');
						
						var marker = createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, categories, tags, description);
						map.addOverlay(marker);
						var sidebarEntry = createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, email, url, special, categories, tags, description);
						results.appendChild(sidebarEntry);
						bounds.extend(point);
					}
					if (source == "search") {
						var myzoom = (map.getBoundsZoomLevel(bounds) );
						if ( myzoom > 18 )
							myzoom = 18;
						map.setCenter( bounds.getCenter(), myzoom );
					} else if ( mapLock == "unlock" ) {
						map.setCenter(bounds.getCenter(), autozoom);
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
			
			function createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, categories, tags, description) {
				
				// Allow plugin users to define Maker Options (including custom images)
				var markerOptions = false;
				if ( 'function' == typeof window.simplemapCustomMarkers )
					markerOptions = simplemapCustomMarkers( name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, email, special, categories, tags, description );
				
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
				
				var titleheight = 3 + Math.floor((name.length + categories.length) * fontsize / (maxbubblewidth * 1.5));
				//var titleheight = 2;
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
				var tagsheight = 3;
				var linksheight = 2;
				var totalheight = (titleheight + addressheight + tagsheight + linksheight + 1) * fontsize;
					
				if (totalheight > maxbubbleheight) {
					totalheight = maxbubbleheight;
				}
				
				var html = '	<div class="markertext" style="height: ' + totalheight + 'px; overflow-y: auto; overflow-x: hidden;">';
				html += '		<h3 style="margin-top: 0; padding-top: 0; border-top: none;">' + name + '<br /><span class="bubble_category">' + categories + '</span></h3>';
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
									if (fax != '') {
				html += '				<br />' + fax_text + ': ' + fax;
									}
				html += '			</p>';
								}
								else if (fax != '') {
				html += '			<p>' + fax_text + ': ' + fax + '</p>';
								}
								if (tags != '') {
				html += '			<p class="bubble_tags">' + tags_text + ': ' + tags + '</p>';
								}
								var dir_address = address + ',' + city;
								if (state) { dir_address += ',' + state; }
								if (zip) { dir_address += ',' + zip; }
								if (country) { dir_address += ',' + country; }
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
			
			function createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, email, url, special, categories, tags, description) {
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
			  
			  // Phone & fax numbers
			  html += '<div class="result_phone">';
			  if (phone != '') {
			  	html += phone_text + ': ' + phone;
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
								var dir_address = address + ',' + city;
								if (state) { dir_address += ',' + state; }
								if (zip) { dir_address += ',' + zip; }
								if (country) { dir_address += ',' + country; }
				  html += '<a href="http://google' + default_domain + '/maps?q=' + homeAddress + ' to ' + dir_address + '" target="_blank">' + get_directions_text + '</a>';
			  }
			  html += '</div>';
			  
			  html += '<div style="clear: both;"></div>';
			  
			  // Categories list
			  if ( categories != '' ) {
			  		html += '<div class="categories_list"><small><strong>Categories:</strong> ' + categories + '</small></div>';
			  }
			  
			  // Tags list
			  if ( tags != '' ) {
			  		html += '<div class="tags_list"><small><strong>Tags:</strong> ' + tags + '</small></div>';
			  }

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
				'United Kingdom' => '.co.uk'
			);
			
			return $domains_list;
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
			
			return $country_list;
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
//echo "<pre>";print_r( $vars );die();
			return $vars;
		}

	}	
}
?>