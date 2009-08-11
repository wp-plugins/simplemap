<?php
/*
SimpleMap Plugin
display-map.php: Displays the Google Map and search results
*/
?>

<div id="map_search" style="width: <?php echo $options['map_width']; ?>;">
	<a name="map_top"></a>
	<form onsubmit="searchLocations(); return false;" name="searchForm" id="searchForm">
		<input type="text" id="addressInput" name="addressInput" class="address" />&nbsp;
		<select id="radiusSelect">
			<option value="1">1 mi</option><br />
			<option value="5">5 mi</option><br />
			<option value="10" selected="selected">10 mi</option><br />
			<option value="25">25 mi</option><br />
			<option value="50">50 mi</option><br />
			<option value="100">100 mi</option></p>
		</select>&nbsp;
		<input type="submit" value="Search" id="addressSubmit" class="submit" />
	</form>
</div>

<h4>Please enter a name, address, city or zip code in the search box above.</h4>

<div id="map" style="width: <?php echo $options['map_width']; ?>; height: <?php echo $options['map_height']; ?>;"></div>

<div id="results" style="width: <?php echo $options['map_width']; ?>;"></div>

<script type="text/javascript">
(function($) { 
	$(document).ready(function() {
		load();
	});
})(jQuery);
</script>