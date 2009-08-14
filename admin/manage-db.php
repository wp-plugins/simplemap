<?php
/*
SimpleMap Plugin
manage-db.php: Displays the Manage Database admin page
*/

$current_page = $_SERVER['SCRIPT_NAME'];

global $wpdb;
$db_table_name = $this->table;
if (isset($_GET['paged']))
	$paged = $_GET['paged'];
else
	$paged = 1;
$start = ($paged - 1) * 15;

$result = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY name LIMIT $start, 15", ARRAY_A);
$count = $wpdb->get_var("SELECT COUNT(*) FROM $db_table_name");
?>
<script src="<?php echo $this->plugin_url; ?>js/inline-edit-stores.js" type="text/javascript"></script>
<script src="<?php bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery.form.js" type="text/javascript"></script>
<div class="wrap">
	<h2><?php _e('SimpleMap: Manage Database', 'SimpleMap'); ?></h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.', 'SimpleMap').'</a></p></div>';
	?>
		
	<?php
	if (isset($_GET['message'])) {
		echo '<div id="message" class="updated fade"><p>'.$_GET['message'].'</p></div>';
	}
	?>
	<?php if ($count > 0) { ?>
		<form action="<?php echo $this->plugin_url; ?>actions/location-process.php" method="post" style="float: left; margin: 15px 0;">
			<input type="hidden" name="action" value="delete_all" />
			<input type="submit" class="button-primary" value="<?php _e('Delete Database', 'SimpleMap'); ?>" onclick="javascript:return confirm('Do you really want to delete all locations in your database?');" /> <small><?php _e('Delete all entries in database', 'SimpleMap'); ?></small>
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
	$number_of_pages = $count / 15 + 1;
	if ($number_of_pages > 10) {
		
		// at the beginning
		if ($paged - 5 < 1) {
			$dots2 = '&hellip;';
			$page_numbers_start = 1;
			$page_numbers_end = 9;
		}
		// at the end
		else if ($paged + 5 > $number_of_pages) {
			$dots1 = '&hellip;';
			$page_numbers_start = $number_of_pages - 9;
			$page_numbers_end = $number_of_pages;
		}
		// in the middle
		else {
			$dots1 = '&hellip;';
			$dots2 = '&hellip;';
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
					echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged - 1)."'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=Manage%20Database&paged=$i'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged + 1)."'>&raquo;</a>\n";
				?>
			</div>
		</div>
	<?php } ?>
	
	
	<table class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 30%;"><?php _e('Name', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Address', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Phone/Fax', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('URL', 'SimpleMap'); ?></th>
				
				<?php if ($options['special_text'] != '') { ?>
					<th scope="col" class="manage-column" style=""><?php echo $options['special_text']; ?></th>
				<?php } ?>
				
			</tr>
		</thead>

		<tfoot>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 30%;"><?php _e('Name', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Address', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('Phone/Fax', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style=""><?php _e('URL', 'SimpleMap'); ?></th>
				
				<?php if ($options['special_text'] != '') { ?>
					<th scope="col" class="manage-column" style=""><?php echo $options['special_text']; ?></th>
				<?php } ?>
				
			</tr>
		</tfoot>
		
		<tbody>
		<?php
		if ($count > 0) {
			$i = 0;
			foreach ($result as $row) {
				$name = stripslashes($row['name']);
				$address = stripslashes($row['address']);
				$address2 = stripslashes($row['address2']);
				$city = stripslashes($row['city']);
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
							<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this post inline"><?php _e('Quick Edit', 'SimpleMap'); ?></a> | </span>
							<span class='delete'><a class='submitdelete' title='Delete this location' href='<?php echo $this->plugin_url; ?>actions/location-process.php?action=delete&amp;del_id=<?php echo $row['id']; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($name); ?>\'?');"><?php _e('Delete', 'SimpleMap'); ?></a></span>
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
						<div class="store_phone1"><?php echo substr($row['phone'], 1, 3); ?></div>
						<div class="store_phone2"><?php echo substr($row['phone'], 6, 3); ?></div>
						<div class="store_phone3"><?php echo substr($row['phone'], -4); ?></div>
						<div class="store_fax1"><?php echo substr($row['fax'], 1, 3); ?></div>
						<div class="store_fax2"><?php echo substr($row['fax'], 6, 3); ?></div>
						<div class="store_fax3"><?php echo substr($row['fax'], -4); ?></div>
						<div class="store_url"><?php echo $row['url']; ?></div>
						
						<?php if ($options['special_text'] != '') { ?>
							<div class="store_special"><?php echo $row['special']; ?></div></div>
						<?php } ?>
					</td>
					<td>
						<span class="row_address"><?php echo $row['address']."</span>";
						if ($row['address2'])
							echo "<br /><span class='row_address2'>".$row['address2']."</span>";
						echo "<br /><span class='row_city'>{$row['city']}<span>, 
						<span class='row_state'>{$row['state']}</span> 
						<span class='row_zip'>{$row['zip']}</span>"; ?>
					</td>
					<td><span class="row_phone">
						<?php echo $row['phone']."</span>";
						if ($row['fax'])
							echo "<br/>Fax: <span class='row_fax'>".$row['fax']."</span>"; ?>
					</td>
					<td><span class="row_url"><?php echo $row['url']; ?></span></td>
					
					<?php if ($options['special_text'] != '') { ?>
						<td><span class="row_special">
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
					echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged - 1)."'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=Manage%20Database&paged=$i'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged + 1)."'>&raquo;</a>\n";
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
			<td colspan="6">
			
			<input type="hidden" name="action" value="edit" />
		
			<input type="hidden" name="store_id" value="" />
			<input type="hidden" name="altclass" value="" />
		
			<fieldset style="width: 26%;"><div class="inline-edit-col">
				<label>
					<span class="title"><?php _e('Name', 'SimpleMap'); ?></span><br />
				</label>
					<span class="input-text-wrap"><input type="text" name="store_name" class="ptitle" value="" /></span>
			</div></fieldset>
		
		
			<fieldset style="width: 22%;"><div class="inline-edit-col">
				<label>
					<span class="title"><?php _e('Address', 'SimpleMap'); ?></span><br />
				</label>
					<span class="input-text-wrap"><input type="text" name="store_address" value="" /></span><br />
					<span class="input-text-wrap"><input type="text" name="store_address2" value="" /></span><br />
					<span class=""><input type="text" name="store_city" size="13" value="" />
					<select class="" name="store_state">
					<?php
					include ("../wp-content/plugins/simplemap/includes/states-array.php");
					foreach ($state_list as $key => $value) {
						echo "<option value='$key'>$key</option>\n";
					}
					?>
					</select>
					<input type="text" name="store_zip" size="6" maxlength="5" value="" /></span>
			</div></fieldset>
		
		
			<fieldset style="width: 22%;"><div class="inline-edit-col"><br />
					<span class="title" style="display: block; float: left; width: 4em;"><?php _e('Phone', 'SimpleMap'); ?></span>
						<input type="text" name="store_phone1" size="4" maxlength="3" value="" /><input type="text" name="store_phone2" size="4" maxlength="3" value="" /><input type="text" name="store_phone3" size="5" maxlength="4" value="" /><br />
					<span class="title" style="display: block; float: left; width: 4em;"><?php _e('Fax', 'SimpleMap'); ?></span>
						<input type="text" name="store_fax1" size="4" maxlength="3" value="" /><input type="text" name="store_fax2" size="4" maxlength="3" value="" /><input type="text" name="store_fax3" size="5" maxlength="4" value="" />
			</div></fieldset>
		
		
			<fieldset style="width: 30%;"><div class="inline-edit-col"><br />
				<label>
					<span class="title"><?php _e('URL', 'SimpleMap'); ?></span>
				</label>
				<span class="input-text-wrap"><input type="text" name="store_url" value="" /></span>
				
				<?php if ($options['special_text'] != '') { ?>
					<label>
						<input type="checkbox" id="store_special" name="store_special" />&nbsp;&nbsp;<span class="title" style="width: auto; float: none; display: inline; vertical-align: text-top;"><?php echo $options['special_text']; ?></span>
					</label>
				<?php } ?>
				
			</div></fieldset>
		
			<p class="submit inline-edit-save">
				<a accesskey="c" href="#inline-edit" title="Cancel" class="button-secondary cancel alignleft"><?php _e('Cancel', 'SimpleMap'); ?></a>
				<input type="hidden" id="_inline_edit" name="_inline_edit" value="58a915a1fb" /><a accesskey="s" href="#inline-edit" title="Update" class="button-primary save alignright"><?php _e('Update Location', 'SimpleMap'); ?></a>
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