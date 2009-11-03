<?php
/*
SimpleMap Plugin
scripts.php: Contains scripts to insert into <head>
*/
global $wp_query;
$thisID = $wp_query->post->ID;

$pages = explode(',', $options['map_pages']);
if (in_array($thisID, $pages) || $options['map_pages'] == '0' || is_admin()) :

echo "\n".'<!-- SimpleMap version 1.2b4 ======================== -->'."\n"."\n";
echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/'.$options['map_stylesheet'].'" type="text/css" />'."\n";
echo '<link rel="stylesheet" href="'.$this->plugin_url.'includes/admin.css" type="text/css" />'."\n";
//include $this->plugin_url.'js/functions.js.php';

$r = 'plugin_url='.urlencode($this->plugin_url);

foreach ($options as $key => $value)
	$r .= '&amp;'.$key.'='.urlencode($value);
	
$r .= '&amp;visit_website_text='.urlencode(__('Visit Website', 'SimpleMap'));
$r .= '&amp;get_directions_text='.urlencode(__('Get Directions', 'SimpleMap'));
$r .= '&amp;location_tab_text='.urlencode(__('Location', 'SimpleMap'));
$r .= '&amp;description_tab_text='.urlencode(__('Description', 'SimpleMap'));
$r .= '&amp;phone_text='.urlencode(__('Phone', 'SimpleMap'));
$r .= '&amp;fax_text='.urlencode(__('Fax', 'SimpleMap'));
$r .= '&amp;tags_text='.urlencode(__('Tags', 'SimpleMap'));
$r .= '&amp;noresults_text='.urlencode(__('No results found.', 'SimpleMap'));

echo '<script type="text/javascript">
function load() {
  if (GBrowserIsCompatible()) {
    geocoder = new GClientGeocoder();
    var latlng = new GLatLng('.$options['default_lat'].','.$options['default_lng'].');
    map = new GMap2(document.getElementById(\'map\'));
    map.addControl(new GLargeMapControl3D());
    map.addControl(new GMenuMapTypeControl());
    map.addMapType(G_PHYSICAL_MAP);
    map.setCenter(latlng, '.$options['zoom_level'].', '.$options['map_type'].');
  }
}
</script>'."\n";
//echo '<script type="text/javascript" src="'.$this->plugin_url.'js/functions.js"></script>'."\n";
echo '<script type="text/javascript" src="'.$this->plugin_url.'js/functions.js.php?'.$r.'"></script>'."\n";
echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-includes/js/jquery/jquery.js"></script>'."\n";
if ($options['api_key'] != '') {
		echo '<script src="http://maps.google'.$options['default_domain'].'/maps?file=api&amp;v=2&amp;key='.$options['api_key'].'&amp;sensor=false" type="text/javascript"></script>'."\n";
}
echo "\n".'<!-- End of SimpleMap scripts ======================== -->'."\n"."\n";

endif; // in_array()

?>