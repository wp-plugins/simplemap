<?php
/*
Plugin Name: SimpleMap
Version: 1.1.3
Plugin URI: http://simplemap-plugin.com/
Author: Alison Barrett
Author URI: http://alisothegeek.com/
Description: An easy-to-use international store locator plugin that uses Google Maps to display information directly on your WordPress site.
*/
	
global $wp_version;
		
$exit_msg = __('SimpleMap requires WordPress 2.8 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>', 'SimpleMap');
if (version_compare($wp_version, "2.8", "<"))
	exit($exit_msg);

// Avoid name collisions
if (!class_exists('SimpleMap')) :

class SimpleMap {

	var $plugin_url;
	var $table;
	var $cat_table;
	var $db_option = 'SimpleMap_options';
	var $plugin_domain = 'SimpleMap';
	
	// Initialize the plugin
	function SimpleMap() {
		
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( $this->plugin_domain, 'wp-content/plugins/' . $plugin_dir.'/lang', $plugin_dir.'/lang' );
		
		$this->plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
		
		global $wpdb;
		$this->table = $wpdb->prefix . 'simple_map';
		$this->cat_table = $wpdb->prefix . 'simple_map_cats';
		
		// Add shortcode handler
		add_shortcode('simplemap', array(&$this, 'sm_display'));
		
		// Create Admin menu & submenus
		add_action('admin_menu', array(&$this, 'sm_add_admin_pages'));
		
		// Inject scripts & styles into <head>
		add_action('wp_print_scripts', array(&$this, 'sm_scripts_action'));
	}
	
	// Function to call after plugin activation
	function sm_install() {
		$options = $this->sm_get_options();
		include('includes/install.php');
	}
	
	function sm_display() {
		$options = $this->sm_get_options();
		include('includes/search-radii-array.php');
		include('includes/display-map.php');
		return $to_display;
	}
	
	function sm_get_options() {
		$options = array(
			'map_width' => '100%',
			'map_height' => '350px',
			'default_lat' => '44.968684',
			'default_lng' => '-93.215561',
			'zoom_level' => '10',
			'default_radius' => '10',
			'map_type' => 'ROADMAP',
			'special_text' => '',
			'default_state' => 'none',
			'default_country' => 'US',
			'map_stylesheet' => 'simplemap/styles/light.css',
			'units' => 'mi',
			'autoload' => '1',
			'powered_by' => 'show',
			'display_search' => 'show'
		);
		
		$saved = get_option($this->db_option);
		
		if (!empty($saved)) {
			foreach ($saved as $key => $option)
				$options[$key] = $option;
		}
		
		if ($saved != $options)
			update_option($this->db_option, $options);
		return $options;
	}
	
	function sm_scripts_action() {
		$options = $this->sm_get_options();
		include 'includes/scripts.php';
	}
	
	function sm_add_admin_pages() {
		add_menu_page(__('SimpleMap Options', 'SimpleMap'), 'SimpleMap', 10, __FILE__, array(&$this, 'sm_menu_general_options'), $this->plugin_url.'icon.png');
		add_submenu_page(__FILE__, __('SimpleMap: General Options', 'SimpleMap'), __('General Options', 'SimpleMap'), 10, __FILE__, array(&$this, 'sm_menu_general_options'));
		add_submenu_page(__FILE__, __('SimpleMap: Add Location', 'SimpleMap'), __('Add Location', 'SimpleMap'), 10, __('Add Location', 'SimpleMap'), array(&$this, 'sm_menu_add_location'));
		add_submenu_page(__FILE__, __('SimpleMap: Manage Database', 'SimpleMap'), __('Manage Database', 'SimpleMap'), 10, __('Manage Database', 'SimpleMap'), array(&$this, 'sm_menu_manage_database'));
		add_submenu_page(__FILE__, __('SimpleMap: Manage Categories', 'SimpleMap'), __('Manage Categories', 'SimpleMap'), 10, __('Manage Categories', 'SimpleMap'), array(&$this, 'sm_menu_manage_categories'));
		add_submenu_page(__FILE__, __('SimpleMap: Import/Export', 'SimpleMap'), __('Import/Export', 'SimpleMap'), 10, __('Import/Export', 'SimpleMap'), array(&$this, 'sm_menu_import_export'));
	}
	
	function sm_menu_general_options() {
		$options = $this->sm_get_options();
		$api_link = $this->sm_get_api_link();
		
		if (isset($_POST['submitted'])) {
			check_admin_referer('simplemap-nonce');
			
			$options = array();
			$options['api_key'] = $_POST['api_key'];
			$options['map_width'] = $_POST['map_width'];
			$options['map_height'] = $_POST['map_height'];
			$options['default_lat'] = $_POST['default_lat'];
			$options['default_lng'] = $_POST['default_lng'];
			$options['zoom_level'] = (int)$_POST['zoom_level'];
			$options['default_radius'] = (int)$_POST['default_radius'];
			$options['map_type'] = $_POST['map_type'];
			$options['special_text'] = $_POST['special_text'];
			$options['default_state'] = $_POST['default_state'];
			$options['default_country'] = $_POST['default_country'];
			$options['map_stylesheet'] = $_POST['map_stylesheet'];
			$options['units'] = $_POST['units'];
			if ($_POST['autoload'])
				$options['autoload'] = 1;
			else
				$options['autoload'] = 0;
				
			if ($_POST['powered_by'])
				$options['powered_by'] = 'show';
			else
				$options['powered_by'] = 'hide';
				
			if ($_POST['display_search'])
				$options['display_search'] = 'show';
			else
				$options['display_search'] = 'hide';
			
			update_option($this->db_option, $options);
			
			echo '<div class="updated fade"><p>'.__('SimpleMap settings saved.', 'SimpleMap').'</p></div>';
		}
		
		$api_key = $options['api_key'];
		$map_width = $options['map_width'];
		$map_height = $options['map_height'];
		$default_lat = $options['default_lat'];
		$default_lng = $options['default_lng'];
		
		$zoom_level = $options['zoom_level'];
		unset($selected_zoom);
		$selected_zoom[$zoom_level] = ' selected="selected"';
		
		$default_radius = $options['default_radius'];
		unset($selected_radius);
		$selected_radius[$default_radius] = ' selected="selected"';
		
		$map_type = $options['map_type'];
		unset($selected_type);
		$selected_type[$map_type] = ' checked="checked"';
		$selected_type_div[$map_type] = ' radio-thumbnail-current';
		
		$special_text = $options['special_text'];
		$map_stylesheet = $options['map_stylesheet'];
		$autoload = $options['autoload'];
		$units = $options['units'];
		$powered_by = $options['powered_by'];
		$display_search = $options['display_search'];
		
		$action_url = 'admin.php?page=simplemap/simplemap.php';
		
		include 'includes/search-radii-array.php';
		include 'includes/states-array.php';
		include 'admin/general-options.php';
	}
	
	function sm_menu_add_location() {
		$options = $this->sm_get_options();
		include 'includes/states-array.php';
		include 'admin/add-location.php';
	}
	
	function sm_menu_manage_database() {
		$options = $this->sm_get_options();
		include 'admin/manage-db.php';
	}
	
	function sm_menu_manage_categories() {
		$options = $this->sm_get_options();
		include 'admin/manage-categories.php';
	}
	
	function sm_menu_import_export() {
		$options = $this->sm_get_options();
		include 'admin/import-export.php';
	}
	
	function sm_get_api_link() {
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
	
}

else :

	exit('<p>'.__('Class SimpleMap already declared!', 'SimpleMap').'</p>');

endif;

$plugin = plugin_basename(__FILE__);

// Add info box under plugin on plugins page
add_action("after_plugin_row_$plugin", 'sm_add_plugin_row', 10, 2);
function sm_add_plugin_row($links, $file) {
	global $wp_version;
	$this_plugin = plugin_basename(__FILE__);
	if (strpos($links, $this_plugin) !== false) {
		$current = get_transient('update_plugins');
		
		if (!isset($current->response[$this_plugin]))
			return false;
		
		$columns = substr($wp_version, 0, 3) == "2.8" ? 3 : 5;
		$url = "http://alisothegeek.com/simplemap-update.txt";
		$update = wp_remote_fopen($url);
		echo '<td colspan="'.$columns.'" style="background: #fffbe4; border-top: 1px solid #dfdfdf; text-align: center;">';
		echo $update;
		echo '</td>';
	}
}
	
// Add settings link on plugins page
function sm_settings_link($links) {
	$plugin = plugin_basename(__FILE__);
	$settings_link = sprintf('<a href="admin.php?page=%s">%s</a>', $plugin, __('Settings'));
	array_unshift($links, $settings_link);
	return $links;
}
add_filter("plugin_action_links_$plugin", 'sm_settings_link');

// Create a new instance of the class
$SimpleMap = new SimpleMap();

if (isset($SimpleMap)) {
	register_activation_hook(__FILE__, array(&$SimpleMap, 'sm_install'));
}
?>