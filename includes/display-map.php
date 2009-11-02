<?php
/*
SimpleMap Plugin
display-map.php: Displays the Google Map and search results
*/
$to_display = '';

if ($options['display_search'] == 'show') {
$to_display .= '
<div id="map_search" style="width: '.$options['map_width'].';">
	<a name="map_top"></a>
	<form onsubmit="searchLocations(\''.$categories.'\'); return false;" name="searchForm" id="searchForm" action="http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'">
		<input type="text" id="addressInput" name="addressInput" class="address" />&nbsp;
		<select name="radiusSelect" id="radiusSelect">';
			
			$default_radius = $options['default_radius'];
			unset($selected_radius);
			$selected_radius[$default_radius] = ' selected="selected"';

			foreach ($search_radii as $value) {
				$r = (int)$value;
				$to_display .= '<option value="'.$value.'"'.$selected_radius[$r].'>'.$value.' '.$options['units']."</option>\n";
			}
			
$to_display .= '	
		</select>&nbsp;
		<input type="submit" value="'.__('Search', 'SimpleMap').'" id="addressSubmit" class="submit" />
		<p>'.__('Please enter an address or search term in the box above.', 'SimpleMap').'</p>
	</form>
</div>';
}
if ($options['powered_by'] == 'show') {
	$to_display .= '<div id="powered_by_simplemap">'.sprintf(__('Powered by %s SimpleMap', 'SimpleMap'),'<a href="http://simplemap-plugin.com/" target="_blank">').'</a></div>';
}

$to_display .= '
<div id="map" style="width: '.$options['map_width'].'; height: '.$options['map_height'].';"></div>

<div id="results" style="width: '.$options['map_width'].';"></div>

<script type="text/javascript">
(function($) { 
	$(document).ready(function() {
		load();';
		
		if ($options['autoload'] == 'some') {
			$to_display .= 'var autoLatLng = new GLatLng(default_lat, default_lng);
			searchLocationsNear(autoLatLng, autoLatLng.lat() + ", " + autoLatLng.lng(), "auto", "'.$options['lock_default_location'].'", "'.$categories.'");';
		}
		
		else if ($options['autoload'] == 'all') {
			$to_display .= 'var autoLatLng = new GLatLng(default_lat, default_lng);
			searchLocationsNear(autoLatLng, autoLatLng.lat() + ", " + autoLatLng.lng(), "auto_all", "'.$options['lock_default_location'].'", "'.$categories.'");';
		}
		
$to_display .= '
	});
})(jQuery);
</script>';

?>