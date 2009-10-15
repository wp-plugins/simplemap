<?php
/*
SimpleMap Plugin
manage-categories.php: Displays the Manage Categories admin page
*/

$current_page = $_SERVER['SCRIPT_NAME'];

global $wpdb;
$db_table_name = $this->cat_table;
if (isset($_GET['paged']))
	$paged = $_GET['paged'];
else
	$paged = 1;
$start = ($paged - 1) * 15;

$result = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY name LIMIT $start, 15", ARRAY_A);
$count = $wpdb->get_var("SELECT COUNT(*) FROM $db_table_name");
?>
<script src="<?php echo $this->plugin_url; ?>js/inline-edit-categories.js" type="text/javascript"></script>
<script src="<?php bloginfo('wpurl'); ?>/wp-includes/js/jquery/jquery.form.js" type="text/javascript"></script>
<div class="wrap">
		
	<?php
	$sm_page_title = __('SimpleMap: Manage Categories', 'SimpleMap');
	include "../wp-content/plugins/simplemap/includes/toolbar.php";
	?>
		
	<?php
	if (isset($_GET['message'])) {
		echo '<div id="message" class="updated fade"><p>'.$_GET['message'].'</p></div>';
	}
	?>
	<?php if ($count > 0) { ?>
		<form action="<?php echo $this->plugin_url; ?>actions/category-process.php" method="post" style="float: left; margin: 15px 0;">
			<input type="hidden" name="action" value="delete_all" />
			<input type="submit" class="button-primary" value="<?php _e('Delete All Categories', 'SimpleMap'); ?>" onclick="javascript:return confirm('Do you really want to delete all categories in your database?');" /> <small><?php _e('Delete all categories in database', 'SimpleMap'); ?></small>
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
		<div class="tablenav" style="width: 600px;">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php printf(__('Displaying %d&#8211;%d of %d', 'SimpleMap'), ($start + 1), $end, $count); ?></span>
				<?php
				if ($paged > 1)
					echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Categories&paged=".($paged - 1)."'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=Manage%20Categories&paged=$i'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=Manage%20Categories&paged=".($paged + 1)."'>&raquo;</a>\n";
				?>
			</div>
		</div>
	<?php } ?>
	
	
	<table class="widefat post fixed" cellspacing="0" style="width: 600px;">
		<thead>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 10%;"><?php _e('ID', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style="width: 90%;"><?php _e('Category', 'SimpleMap'); ?></th>
				
			</tr>
		</thead>

		<tfoot>
			<tr>
				<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th> -->
				<th scope="col" class="manage-column" style="width: 10%;"><?php _e('ID', 'SimpleMap'); ?></th>
				<th scope="col" class="manage-column" style="width: 90%;"><?php _e('Category', 'SimpleMap'); ?></th>
				
			</tr>
		</tfoot>
		
		<tbody>
		<?php
		if ($count > 0) {
			$i = 0;
			foreach ($result as $row) {
				$name = stripslashes($row['name']);
				$i++;
				if ($i % 2 == 0)
					$altclass = 'alternate ';
				else
					$altclass = '';
				?>
				
				<tr id='post-<?php echo $row['id']; ?>' class='<?php echo $altclass; ?>author-self status-publish iedit' valign="top">
					<!-- <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="1" /></th> -->
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $row['id']; ?></span></strong></td>
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $name; ?></span></strong>
						<div class="row-actions">
							<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this category inline"><?php _e('Quick Edit', 'SimpleMap'); ?></a> | </span>
							<span class='delete'><a class='submitdelete' title='Delete this category' href='<?php echo $this->plugin_url; ?>actions/category-process.php?action=delete&amp;del_id=<?php echo $row['id']; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($name); ?>\'?');"><?php _e('Delete', 'SimpleMap'); ?></a></span>
						</div>
						<div class="hidden" id="inline_<?php echo $row['id']; ?>">
						<div class="store_id"><?php echo $row['id']; ?></div>
						<div class="altclass"><?php echo $altclass; ?></div>
						<div class="store_name"><?php echo $name; ?></div>
						</div>
					</td>
					
				</tr>
				<?php
			}
		}
		else {
			echo '<tr><td>'.__('No records found.', 'SimpleMap').'</td></tr>';
		}
	
	?>
		</tbody>
	</table>
	<?php if ($count > 0) { ?>
		<div class="tablenav" style="width: 600px;">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php printf(__('Displaying %d&#8211;%d of %d', 'SimpleMap'), ($start + 1), $end, $count); ?></span>
				<?php
				if ($paged > 1)
					echo "<a class='prev page-numbers' href='$current_page?page=Manage%20Categories&paged=".($paged - 1)."'>&laquo;</a>\n";
					
				echo $dots1.' ';
				
				for($i = (int)$page_numbers_start; $i <= (int)$page_numbers_end; $i++) {
						
					if ($i == $paged)
						echo "<span class='page-numbers current'>$i</span>\n";
					else
						echo "<a class='page-numbers' href='$current_page?page=Manage%20Categories&paged=$i'>$i</a>\n";
				}
				
				echo $dots2.' ';
				
				if ($paged < $number_of_pages - 1)
					echo "<a class='next page-numbers' href='$current_page?page=Manage%20Categories&paged=".($paged + 1)."'>&raquo;</a>\n";
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
			<td colspan="2">
			
			<input type="hidden" name="action" value="edit" />
		
			<input type="hidden" name="store_id" value="" />
			<input type="hidden" name="altclass" value="" />
		
			<fieldset><div class="inline-edit-col">
				<label>
					<span class="title" style="width: 80%"><?php _e('Category Name', 'SimpleMap'); ?></span><br />
				</label>
					<span class="input-text-wrap"><input type="text" name="store_name" class="ptitle" value="" /></span>
			</div></fieldset>
		
			<p class="submit inline-edit-save">
				<a accesskey="c" href="#inline-edit" title="Cancel" class="button-secondary cancel alignleft"><?php _e('Cancel', 'SimpleMap'); ?></a>
				<input type="hidden" id="_inline_edit" name="_inline_edit" value="58a915a1fb" /><a accesskey="s" href="#inline-edit" title="Update" class="button-primary save alignright"><?php _e('Update Category', 'SimpleMap'); ?></a>
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
				var m = '<?php _e('You are about to delete the selected categories. "Cancel" to stop, "OK" to delete.', 'SimpleMap'); ?>';
				return showNotice.warn(m);
			}
		});
	});
})(jQuery);
columns.init('edit');
/* ]]> */
</script>

<form action="<?php echo $this->plugin_url; ?>actions/category-process.php" method="post" style="float: left; margin: 15px 0;">
	<h3>Add a New Category</h3>
	<input type="hidden" name="action" value="add" />
	<p><label for="new_store_name">Name: <input type="text" name="new_store_name" id="new_store_name" size="40" value="" /></label></p>
	<p><input type="submit" class="button-primary" value="<?php _e('Add Category', 'SimpleMap'); ?>" /></p>
</form>