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
	<h2>SimpleMap: Manage Database</h2>
	
	<?php
	if ($options['api_key'] == '')
		echo '<div class="error"><p>You must enter an API key for your domain. <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">Enter a key on the General Options page.</a></p></div>';
	?>
		
	<?php
	if (isset($_GET['message'])) {
		echo '<div id="message" class="updated fade">'.$_GET['message'].'</p></div>';
	}
	?>
	<div class="tablenav">
	<?php
	if ($start + 15 > $count)
		$end = $count;
	else
		$end = $start + 15;
	?>
	<div class="tablenav-pages"><span class="displaying-num">Displaying <?php echo ($start + 1); ?>&#8211;<?php echo ($end); ?> of <?php echo $count; ?></span>
	<?php
	if ($paged > 1)
		echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged - 1)."'>&laquo;</a>\n";
	for($i = 1; $i <= ($count / 15 + 1); $i++) {
			
		if ($i == $paged)
			echo "<span class='page-numbers current'>$i</span>\n";
		else
			echo "<a class='page-numbers' href='$current_page?page=Manage%20Database&paged=$i'>$i</a>\n";
	}	
	if ($paged < ($count / 15))
		echo "<a class='next page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged + 1)."'>&raquo;</a>\n";
	?>
	</div></div>
	
	
	<table class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 30%;">Name</th>
				<th scope="col" class="manage-column" style="">Address</th>
				<th scope="col" class="manage-column" style="">Phone/Fax</th>
				<th scope="col" class="manage-column" style="">URL</th>
				
				<?php if ($options['special_text'] != '') { ?>
					<th scope="col" class="manage-column" style=""><?php echo $options['special_text']; ?></th>
				<?php } ?>
				
			</tr>
		</thead>

		<tfoot>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="">Name</th>
				<th scope="col" class="manage-column" style="">Address</th>
				<th scope="col" class="manage-column" style="">Phone/Fax</th>
				<th scope="col" class="manage-column" style="">URL</th>
				
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
							<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this post inline">Quick Edit</a> | </span>
							<span class='delete'><a class='submitdelete' title='Delete this location' href='<?php echo $this->plugin_url; ?>actions/location-process.php?action=delete&amp;del_id=<?php echo $row['id']; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($name); ?>\'?');">Delete</a></span>
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
				echo '<tr><td colspan="5">No records found.</td></tr>';
			else
				echo '<tr><td colspan="4">No records found.</td></tr>';
		}
	
	?>
		</tbody>
	</table>
	<div class="tablenav">
	<?php
	if ($start + 15 > $count)
		$end = $count;
	else
		$end = $start + 15;
	?>
	<div class="tablenav-pages"><span class="displaying-num">Displaying <?php echo ($start + 1); ?>&#8211;<?php echo ($end); ?> of <?php echo $count; ?></span>
	<?php
	if ($paged > 1)
		echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged - 1)."'>&laquo;</a>\n";
	for($i = 1; $i <= ($count / 15 + 1); $i++) {
			
		if ($i == $paged)
			echo "<span class='page-numbers current'>$i</span>\n";
		else
			echo "<a class='page-numbers' href='$current_page?page=Manage%20Database&paged=$i'>$i</a>\n";
	}	
	if ($paged < ($count / 15))
		echo "<a class='next page-numbers' href='$current_page?page=Manage%20Database&paged=".($paged + 1)."'>&raquo;</a>\n";
	?>
	</div></div>
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
					<span class="title">Name</span><br />
				</label>
					<span class="input-text-wrap"><input type="text" name="store_name" class="ptitle" value="" /></span>
			</div></fieldset>
		
		
			<fieldset style="width: 22%;"><div class="inline-edit-col">
				<label>
					<span class="title">Address</span><br />
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
					<span class="title" style="display: block; float: left; width: 4em;">Phone</span>
						<input type="text" name="store_phone1" size="4" maxlength="3" value="" /><input type="text" name="store_phone2" size="4" maxlength="3" value="" /><input type="text" name="store_phone3" size="5" maxlength="4" value="" /><br />
					<span class="title" style="display: block; float: left; width: 4em;">Fax</span>
						<input type="text" name="store_fax1" size="4" maxlength="3" value="" /><input type="text" name="store_fax2" size="4" maxlength="3" value="" /><input type="text" name="store_fax3" size="5" maxlength="4" value="" />
			</div></fieldset>
		
		
			<fieldset style="width: 30%;"><div class="inline-edit-col"><br />
				<label>
					<span class="title">URL</span>
				</label>
				<span class="input-text-wrap"><input type="text" name="store_url" value="" /></span>
				
				<?php if ($options['special_text'] != '') { ?>
					<label>
						<input type="checkbox" id="store_special" name="store_special" />&nbsp;&nbsp;<span class="title" style="width: auto; float: none; display: inline; vertical-align: text-top;"><?php echo $options['special_text']; ?></span>
					</label>
				<?php } ?>
				
			</div></fieldset>
		
			<p class="submit inline-edit-save">
				<a accesskey="c" href="#inline-edit" title="Cancel" class="button-secondary cancel alignleft">Cancel</a>
				<input type="hidden" id="_inline_edit" name="_inline_edit" value="58a915a1fb" /><a accesskey="s" href="#inline-edit" title="Update" class="button-primary save alignright">Update Location</a>
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
				var m = 'You are about to delete the selected posts.\n  \'Cancel\' to stop, \'OK\' to delete.';
				return showNotice.warn(m);
			}
		});
	});
})(jQuery);
columns.init('edit');
/* ]]> */
</script>