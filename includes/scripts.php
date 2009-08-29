<?php
/*
SimpleMap Plugin
scripts.php: Contains scripts to insert into <head>
*/
echo '<!-- SimpleMap version 1.1 ======================== -->'."\n";
echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/'.$options['map_stylesheet'].'" type="text/css" />'."\n";
echo '<link rel="stylesheet" href="'.$this->plugin_url.'includes/admin.css" type="text/css" />'."\n";
//include $this->plugin_url.'js/functions.js.php';
		
echo '<script type="text/javascript">
var default_lat = '.$options['default_lat'].';
var default_lng = '.$options['default_lng'].';
var default_radius = '.$options['default_radius'].';
var zoom_level = '.$options['zoom_level'].';
var map_width = "'.$options['map_width'].'";
var map_height = "'.$options['map_height'].'";
var special_text = "'.$options['special_text'].'";
var units = "'.$options['units'].'";
var plugin_url = "'.$this->plugin_url.'";

function load() {
  if (GBrowserIsCompatible()) {
    geocoder = new GClientGeocoder();
    var latlng = new GLatLng(default_lat,default_lng);
    map = new GMap2(document.getElementById(\'map\'));
    map.addControl(new GLargeMapControl3D());
    map.addControl(new GMenuMapTypeControl());
    map.addMapType(G_PHYSICAL_MAP);
    map.setCenter(latlng, zoom_level, '.$options['map_type'].');
  }
}
</script>'."\n";
echo '<style type="text/css">
/* This is necessary for the markers and map controls to display properly. */
#map img { background: none !important; }
</style>'."\n";

echo '<script type="text/javascript" src="'.$this->plugin_url.'js/functions.js"></script>'."\n";
echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-includes/js/jquery/jquery.js"></script>'."\n";
if ($options['api_key'] != '') {
	if ($options['units'] == 'mi')
		echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$options['api_key'].'&sensor=false" type="text/javascript"></script>'."\n";
	else
		echo '<script src="http://maps.google.co.uk/maps?file=api&amp;v=2&amp;key='.$options['api_key'].'&sensor=false" type="text/javascript"></script>'."\n";
echo '<!-- End of SimpleMap scripts ======================== -->'."\n";
}
?>