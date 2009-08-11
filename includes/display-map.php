<?php
/*
SimpleMap Plugin
display-map.php: Displays the Google Map and search results
*/
?>

<div id="map_search" style="width: <?php echo $options['map_width']; ?>;">
	<a name="map_top"></a>
	<form onsubmit="searchLocations(); return false;" name="searchForm" id="searchForm" action="<?php echo 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?>">
		<input type="text" id="addressInput" name="addressInput" class="address" />&nbsp;
		<select name="radiusSelect" id="radiusSelect">
			<?php
			$default_radius = $options['default_radius'];
			unset($selected_radius);
			$selected_radius[$default_radius] = ' selected="selected"';

			foreach ($search_radii as $value) {
				$r = (int)$value;
				echo '<option value="'.$value.'"'.$selected_radius[$r].'>'.$value.' '.$options['units']."</option>\n";
			}
			?>
		</select>&nbsp;
		<input type="submit" value="Search" id="addressSubmit" class="submit" />
	</form>
</div>

<h4><?php _e('Please enter a name, address, city or zip code in the search box above.'); ?></h4>

<?php if ($options['powered_by'] == 'show') { ?>
<div id="powered_by_simplemap"><?php _e('Powered by'); ?> <a href="http://simplemap-plugin.com/" target="_blank">SimpleMap</a></div>
<?php } ?>

<div id="map" style="width: <?php echo $options['map_width']; ?>; height: <?php echo $options['map_height']; ?>;"></div>

<div id="results" style="width: <?php echo $options['map_width']; ?>;"></div>

<script type="text/javascript">
(function($) { 
	$(document).ready(function() {
		load();
		<?php if ($options['autoload'] != '') { ?>
		document.getElementById('addressInput').value = '<?php echo $options['autoload']; ?>';
		searchLocations();
		<?php } ?>
	});
})(jQuery);
</script>