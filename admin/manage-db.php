<?php
/*
SimpleMap Plugin
manage-db.php: Displays the Manage Database admin page
*/

$current_page = $_SERVER['SCRIPT_NAME'];
$current_query = '?'.$_SERVER['QUERY_STRING'];
$current_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

global $wpdb;
$db_table_name = $this->table;
$db_cat_table_name = $this->cat_table;
if (isset($_GET['paged']))
	$paged = $_GET['paged'];
else
	$paged = 1;
$start = ($paged - 1) * 15;

$wpdb->query("SET CHARACTER SET utf8");
$wpdb->query("SET NAMES utf8");

$orderby['name'] = 'name, address';
$orderby['address'] = 'country, state, city, address, name';
$orderby['phone'] = 'phone, name, country, state, city, address';
$orderby['category'] = 'category, name, country, state, city, address';

if (isset($_GET['orderby']))
	$orderbyme = $_GET['orderby'];
else
	$orderbyme = 'name';
	
$orderbyarrow[$orderbyme] = '&nbsp;&darr;';

$result = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY ".$orderby[$orderbyme]." LIMIT $start, 15", ARRAY_A);
$count = $wpdb->get_var("SELECT COUNT(*) FROM $db_table_name");
$categories = $wpdb->get_results("SELECT * FROM $db_cat_table_name ORDER BY name", ARRAY_A);
include "../wp-content/plugins/simplemap/includes/states-array.php";
?>
<script src="<?php echo $this->plugin_url; ?>js/inline-edit-stores.js" type="text/javascript"></script>
<script src="<?php bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery.form.js" type="text/javascript"></script>
<div class="wrap">
		
	<?php
	$sm_page_title = __('SimpleMap: Manage Database', 'SimpleMap');
	include "../wp-content/plugins/simplemap/includes/toolbar.php";
	?>

	<?php
	if (isset($_GET['message'])) {
		echo '<div id="message" class="updated fade"><p>'.stripslashes(urldecode($_GET['message'])).'</p></div>';
	}
	?>
	<?php if ($count > 0) { ?>
		<form action="<?php echo $this->plugin_url; ?>actions/location-process.php" method="post" style="float: left; margin: 15px 0;">
			<input type="hidden" name="action" value="delete_all" />
			<input type="submit" class="button-primary" value="<?php _e('Delete Database', 'SimpleMap'); ?>" onclick="javascript:return confirm('<?php _e('Do you really want to delete all locations in your database?'); ?>');" /> <small><?php _e('Delete all entries in database', 'SimpleMap'); ?></small>
		</form>
	<?php } else { ?>
		<div style="height: 30px;"></div>
	<?php } ?>
	
	
	<?php
	if ($start + 15 > $count)
		$end = $count;
	else
		$end = $start + 15;
		
	$dots1 = '';
	$dots2 = '';
	$number_of_pages = (int)($count / 15 + ($count % 15 == 0 ? 0 : 1));
	if ($number_of_pages > 10) {
		
		// at the beginning
		if ($paged - 5 < 1) {
			$dots2 = "&hellip;&nbsp;<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=$number_of_pages&orderby=$orderbyme'>$number_of_pages</a>\n";
			$page_numbers_start = 1;
			$page_numbers_end = 9;
		}
		// at the end
		else if ($paged + 5 > $number_of_pages) {
			$dots1 = "<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=1&orderby=$orderbyme'>1</a>&nbsp;&hellip;\n";
			$page_numbers_start = $number_of_pages - 9;
			$page_numbers_end = $number_of_pages;
		}
		// in the middle
		else {
			$dots1 = "<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=1&orderby=$orderbyme'>1</a>&nbsp;&hellip;\n";
			$dots2 = "&hellip;&nbsp;<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=$number_of_pages&orderby=$orderbyme'>$number_of_pages</a>\n";
			$page_numbers_start = $paged - 4;
			$page_numbers_end = $paged + 4;
		}
		
	}
	else {
		$page_numbers_start = 1;
		$page_numbers_end = $number_of_pages;
	}
	?>
	
	<?php if ($count > 0) { ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php _e('Displaying', 'SimpleMap'); ?> <?php echo ($start + 1); ?>&#8211;<?php echo ($end); ?> of <?php echo $count; ?></span>
				<?php
				if ($paged > 1)
					echo "<a class='prev page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=".($paged - 1)."&orderby=$orderbyme'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=$i&orderby=$orderbyme'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=".($paged + 1)."&orderby=$orderbyme'>&raquo;</a>\n";
				?>
			</div>
		</div>
	<?php } ?>
	
	
	<table class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 15%;"><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=name"><?php _e('Name', 'SimpleMap'); ?><?php echo $orderbyarrow['name']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=address"><?php _e('Address', 'SimpleMap'); ?><?php echo $orderbyarrow['address']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=phone"><?php _e('Phone/Fax/URL', 'SimpleMap'); ?><?php echo $orderbyarrow['phone']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=category"><?php _e('Category', 'SimpleMap'); ?><?php echo $orderbyarrow['category']; ?></a></th>
				<th scope="col" class="manage-column" style=""><?php _e('Tags', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Description', 'SimpleMap'); ?></th>
				
				<?php if ($options['special_text'] != '') { ?>
					<th scope="col" class="manage-column" style="width: 100px;"><?php echo $options['special_text']; ?></th>
				<?php } ?>
				
			</tr>
		</thead>

		<tfoot>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 15%;"><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=name"><?php _e('Name', 'SimpleMap'); ?><?php echo $orderbyarrow['name']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=address"><?php _e('Address', 'SimpleMap'); ?><?php echo $orderbyarrow['address']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=phone"><?php _e('Phone/Fax/URL', 'SimpleMap'); ?><?php echo $orderbyarrow['phone']; ?></a></th>
				<th scope="col" class="manage-column" style=""><a href="<?php echo $current_page; ?>?page=<?php echo urlencode(__('Manage Database', 'SimpleMap')); ?>&paged=<?php echo $paged; ?>&orderby=category"><?php _e('Category', 'SimpleMap'); ?><?php echo $orderbyarrow['category']; ?></a></th>
				<th scope="col" class="manage-column" style=""><?php _e('Tags', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Description', 'SimpleMap'); ?></th>
				
				<?php if ($options['special_text'] != '') { ?>
					<th scope="col" class="manage-column" style="width: 100px;"><?php echo $options['special_text']; ?></th>
				<?php } ?>
				
			</tr>
		</tfoot>
		
		<tbody>
		<?php
		if ($count > 0) {
			$i = 0;
			foreach ($result as $row) {
				//echo (($row['description']))."<br />\n";
				foreach ($row as $key => $value) {
					//$row[$key] = utf8_decode(stripslashes($value));
				}
				$name = stripslashes($row['name']);
				$address = stripslashes($row['address']);
				$address2 = stripslashes($row['address2']);
				$city = stripslashes($row['city']);
				$tags = stripslashes($row['tags']);
				$description = stripslashes($row['description']);
				$category_name = $wpdb->get_var("SELECT name FROM $db_cat_table_name WHERE id = '".$row['category']."'");
				$i++;
				if ($i % 2 == 0)
					$altclass = 'alternate ';
				else
					$altclass = '';
				?>
				
				<tr id='post-<?php echo $row['id']; ?>' class='<?php echo $altclass; ?>author-self status-publish iedit' valign="top">
					<!-- <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="1" /></th> -->
					
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $name; ?></span></strong>
						<div class="row-actions">
							<span class='inline hide-if-no-js'><a href="#" class="editinline" title="<?php _e('Edit this post inline', 'SimpleMap'); ?>"><?php _e('Quick Edit', 'SimpleMap'); ?></a> | </span>
							<span class='delete'><a class='submitdelete' title='Delete this location' href='<?php echo $this->plugin_url; ?>actions/location-process.php?action=delete&amp;del_id=<?php echo $row['id']; ?>' onclick="javascript:return confirm('<?php printf(__('Do you really want to delete %s ?', 'SimpleMap'), "\\'".addslashes($name)."\\'"); ?>');"><?php _e('Delete', 'SimpleMap'); ?></a></span>
						</div>
						<div class="hidden" id="inline_<?php echo $row['id']; ?>">
						<div class="store_id"><?php echo $row['id']; ?></div>
						<div class="altclass"><?php echo $altclass; ?></div>
						<div class="store_name"><?php echo $name; ?></div>
						<div class="store_address"><?php echo $address; ?></div>
						<div class="store_address2"><?php echo $address2; ?></div>
						<div class="store_city"><?php echo $city; ?></div>
						<div class="store_state"><?php echo $row['state']; ?></div>
						<div class="store_zip"><?php echo $row['zip']; ?></div>
						<div class="store_country"><?php echo $row['country']; ?></div>
						<div class="store_phone"><?php echo $row['phone']; ?></div>
						<div class="store_fax"><?php echo $row['fax']; ?></div>
						<div class="store_url"><?php echo $row['url']; ?></div>
						<div class="store_description"><?php echo $description; ?></div>
						<div class="store_category"><?php echo $row['category']; ?></div>
						<div class="store_tags"><?php echo $tags; ?></div>
						<div class="store_lat"><?php echo $row['lat']; ?></div>
						<div class="store_lng"><?php echo $row['lng']; ?></div>
						
						<?php if ($options['special_text'] != '') { ?>
							<div class="store_special"><?php echo $row['special']; ?></div></div>
						<?php } ?>
					</td>
					
					<td>
						<span class="row_address"><?php echo $address."</span>";
						if ($row['address2'])
							echo "<br /><span class='row_address2'>".$address2."</span>";
						echo "<br /><span class='row_city'>".$city."<span> ";
						if ($row['state'])
							echo "<span class='row_state'>".$row['state']."</span> ";
						echo "<span class='row_zip'>".$row['zip']."</span>";
						echo "<br /><span class='row_country'>".strtoupper($country_list[$row['country']])."</span>"; ?>
					</td>
					
					<td><span class="row_phone">
						<?php echo $row['phone']."</span>";
						if ($row['fax'])
							echo "<br/>".__('Fax:', 'SimpleMap')." <span class='row_fax'>".$row['fax']."</span>";
						if ($row['url'])
							echo "<br/><span class='row_url'>".$row['url']."</span>"; ?>
					</td>
					
					<td>
						<span class="row_category"><?php echo $category_name; ?></span>
					</td>
					
					<td>
						<span class="row_tags"><?php echo $tags; ?></span>
					</td>
					
					<td>
						<span class="row_description"><?php echo nl2br(html_entity_decode($description)); ?></span>
					</td>
					
					<?php if ($options['special_text'] != '') { ?>
						<td style="text-align: center;"><span class="row_special">
							<?php if ($row['special'] == 1) { echo "&#x2713;"; } ?>
						</span></td>
					<?php } ?>
					
				</tr>
				<?php
			}
		}
		else {
			if ($options['special_text'] != '')
				echo '<tr><td colspan="5">'.__('No records found.', 'SimpleMap').'</td></tr>';
			else
				echo '<tr><td colspan="4">'.__('No records found.', 'SimpleMap').'</td></tr>';
		}
	
	?>
		</tbody>
	</table>
	<?php if ($count > 0) { ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php _e('Displaying', 'SimpleMap'); ?> <?php echo ($start + 1); ?>&#8211;<?php echo ($end); ?> of <?php echo $count; ?></span>
				<?php
				if ($paged > 1)
					echo "<a class='prev page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=".($paged - 1)."&orderby=$orderbyme'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=$i&orderby=$orderbyme'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=".urlencode(__('Manage Database', 'SimpleMap'))."&paged=".($paged + 1)."&orderby=$orderbyme'>&raquo;</a>\n";
				?>
			</div>
		</div>
	<?php } ?>
</div>
<p></p>

<form method="get" action="">
	<table style="display: none">
		<tbody id="inlineedit">
	
			<tr id="inline-edit" class="inline-edit-row inline-edit-row-post quick-edit-row quick-edit-row-post" style="display: none;">
			<td colspan="7">
			
			<input type="hidden" name="action" value="edit" />
		
			<input type="hidden" name="store_id" value="" />
			<!--
<input type="hidden" name="store_lat" value="" />
			<input type="hidden" name="store_lng" value="" />
-->
			<input type="hidden" name="altclass" value="" />
			<input type="hidden" name="api_key" value="<?php echo $options['api_key']; ?>" />
		
			<fieldset style="width: 26%;"><div class="inline-edit-col">
				<label for="store_name">
					<span class="title" class="long"><?php _e('Name', 'SimpleMap'); ?></span><br />
				</label>
					<span class="input-text-wrap"><input type="text" id="store_name" name="store_name" value="" /></span><br /><br />
					
				<label for="store_lat" class="long"><span class="title title_long"><?php _e('Latitude', 'SimpleMap'); ?></span></label>
					<input type="text" id="store_lat" name="store_lat" size="20" value="" class="fix_width" /><br />
				
				<label for="store_lng" class="long"><span class="title title_long"><?php _e('Longitude', 'SimpleMap'); ?></span></label>
					<input type="text" id="store_lng" name="store_lng" size="20" value="" class="fix_width" /><br />
			</div></fieldset>
		
		
			<fieldset style="width: 22%;"><div class="inline-edit-col">
				<label for="store_address"><span class="title title_long"><?php _e('Address', 'SimpleMap'); ?></span></label>
					<span class="input-text-wrap"><input type="text" id="store_address" name="store_address" value="" /></span><br />
					<span class="input-text-wrap"><input type="text" id="store_address2" name="store_address2" value="" /></span><br />
					
				<label for="store_city" class="long"><span class="title title_long"><?php _e('City', 'SimpleMap'); ?></span></label>
					<input type="text" id="store_city" name="store_city" size="20" value="" class="fix_width" /><br />
				
				<label for="store_state" class="long"><span class="title title_long"><?php _e('State/Province', 'SimpleMap'); ?></span></label>
					<input type="text" id="store_state" name="store_state" size="20" value="" class="fix_width" /><br />
				<!--
<select name="store_state" id="store_state" class="fix_width">
					<option value="none">&mdash;</option>
					<optgroup label="United States">
						<?php
						foreach ($states_list as $key => $value) {
							echo '<option value="'.$key.'">'.$value.'</option>'."\n";
						}
						?>
					</optgroup>
					<optgroup label="Canada">
						<?php
						foreach ($canada_list as $key => $value) {
							echo '<option value="'.$key.'">'.$value.'</option>'."\n";
						}
						?>
					</optgroup>
					<optgroup label="Australia">
						<?php
						foreach ($australia_list as $key => $value) {
							echo '<option value="'.$key.'">'.$value.'</option>'."\n";
						}
						?>
					</optgroup>
				</select><br />
-->
				
				<label for="store_zip" class="long"><span class="title title_long"><?php _e('ZIP/Postal Code', 'SimpleMap'); ?></span></label>
					<input type="text" id="store_zip" name="store_zip" size="13" maxlength="20" value="" class="fix_width" /><br />
				
				<label for="store_country" class="long"><span class="title title_long"><?php _e('Country', 'SimpleMap'); ?></span></label>
				<select name="store_country" id="store_country" class="fix_width">
					<?php
					foreach ($country_list as $key => $value) {
						echo '<option value="'.$key.'">'.$value.'</option>'."\n";
					}
					?>
				</select>
				<p style="padding-top: 10px;"><a class="button" id="geocode_changed_address" onclick="codeChangedAddress();" href="#"><?php _e('Geocode Address', 'SimpleMap'); ?></a></p>
			</div></fieldset>
		
		
			<fieldset style="width: 22%;"><div class="inline-edit-col"><br />
				<label for="store_phone"><span class="title"><?php _e('Phone', 'SimpleMap'); ?></span></label>
					<input type="text" class="full_width" name="store_phone" size="20" maxlength="28" value="" /><br />
				<label for="store_fax"><span class="title"><?php _e('Fax', 'SimpleMap'); ?></span></label>
					<input type="text" class="full_width" name="store_fax" size="20" maxlength="28" value="" /><br />
				<label for="store_url"><span class="title"><?php _e('URL', 'SimpleMap'); ?></span></label>
					<input type="text" class="full_width" id="store_url" name="store_url" value="" /><br /><br />
			
				<label for="store_category"><span class="title"><?php _e('Category', 'SimpleMap'); ?></span></label>
					<?php
					if ($categories != null) {
					?>
						<select name="store_category" id="store_category" class="full_width">
						<?php
						foreach ($categories as $cat) {
							echo '<option value="'.$cat['id'].'">'.htmlspecialchars($cat['name']).'</option>'."\n";
						}
						?>
						</select><br />
					<?php } else { ?>
						<small><em><?php printf(__('You can add categories from the %s General Options screen.%s', 'SimpleMap'), '<a href="admin.php?page=simplemap/simplemap.php">', '</a>'); ?></em></small><br />
					<?php } ?>
					
				<label for="store_tags"><span class="title"><?php _e('Tags', 'SimpleMap'); ?></span></label>
					<input type="text" class="full_width" id="store_tags" name="store_tags" value="" /><br />
				
				<?php if ($options['special_text'] != '') { ?>
						<input type="checkbox" id="store_special" name="store_special" />&nbsp;&nbsp;<label for="store_special" style="display: inline;"><span style="width: auto; float: none; clear: none; display: inline; vertical-align: text-top; font-style: italic; font-family: Georgia;"><?php echo $options['special_text']; ?></span></label>
						<input type="hidden" name="special_text_exists" value="1" />
				<?php } ?>
				
			</div></fieldset>
		
		
			<fieldset style="width: 30%;"><div class="inline-edit-col">
				<label for="store_description" class="long"><span class="title title_long"><?php _e('Description', 'SimpleMap'); ?></span></label><br />
				<textarea style="width: 100%; clear: left;" id="store_description" name="store_description" rows="9"></textarea>
			</div></fieldset>
		
			<p class="submit inline-edit-save">
				<a accesskey="c" href="#inline-edit" title="Cancel" class="button-secondary cancel alignleft button-red"><?php _e('Cancel', 'SimpleMap'); ?></a>
				<input type="hidden" id="_inline_edit" name="_inline_edit" value="58a915a1fb" /><a accesskey="s" href="#inline-edit" title="Update" class="button-primary save alignright"><?php _e('Update Location', 'SimpleMap'); ?></a><span class="disabled-text"><?php _e('Geocode the new address to update this location.', 'SimpleMap'); ?></span>
					<img class="waiting" style="display:none;" src="images/loading.gif" alt="" />
						<input type="hidden" name="post_view" value="list" />
				<br class="clear" />
			</p>
			</td></tr>

		</tbody>
	</table>
</form>
	
<script type="text/javascript">
/* <![CDATA[ */
(function($){
	$(document).ready(function(){
		$('#doaction, #doaction2').click(function(){
			if ( $('select[name^="action"]').val() == 'delete' ) {
				var m = '<?php _e('You are about to delete the selected posts. "Cancel" to stop, "OK" to delete.', 'SimpleMap'); ?>';
				return showNotice.warn(m);
			}
		});
	});
})(jQuery);
columns.init('edit');
/* ]]> */
</script>