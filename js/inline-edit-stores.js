
(function($) {
inlineEditStore = {

	init : function() {
		var t = this, qeRow = $('#inline-edit'), bulkRow = $('#bulk-edit');

		t.type = $('table.widefat').hasClass('page') ? 'page' : 'post';
		t.what = '#'+t.type+'-';

		// get all editable rows
		t.rows = $('tr.iedit');

		// prepare the edit rows
		qeRow.keyup(function(e) { if(e.which == 27) return inlineEditStore.revert(); });
		bulkRow.keyup(function(e) { if (e.which == 27) return inlineEditStore.revert(); });

		$('a.cancel', qeRow).click(function() { return inlineEditStore.revert(); });
		$('a.save', qeRow).click(function() { return inlineEditStore.save(this); });
		$('input, select', qeRow).keydown(function(e) { if(e.which == 13) return inlineEditStore.save(this); });

		$('a.cancel', bulkRow).click(function() { return inlineEditStore.revert(); });
		
		$("th#cb input").click(function() {
			//alert(this.checked);
			var checked_status = this.checked;
			$("th.check-column input").each(function() {
				this.checked = checked_status;
			});
		});

		$('#inline-edit .inline-edit-private input[value=private]').click( function(){
			var pw = $('input.inline-edit-password-input');
			if ( $(this).attr('checked') ) {
				pw.val('').attr('disabled', 'disabled');
			} else {
				pw.attr('disabled', '');
			}
		});

		// add events
		t.addEvents(t.rows);

		$('#bulk-title-div').parents('fieldset').after(
			$('#inline-edit fieldset.inline-edit-categories').clone()
		).siblings( 'fieldset:last' ).prepend(
//		).siblings( 'fieldset:last' ).after( '<fieldset class="inline-edit-col-bottom"><div class="inline-edit-col"></div></fieldset>' );
//		$('fieldset.inline-edit-col-bottom').prepend(
			$('#inline-edit label.inline-edit-tags').clone()
		);

		// categories expandable?
		$('span.catshow').click(function() {
			$('.inline-editor ul.cat-checklist').addClass("cat-hover");
			$('.inline-editor span.cathide').show();
			$(this).hide();
		});

		$('span.cathide').click(function() {
			$('.inline-editor ul.cat-checklist').removeClass("cat-hover");
			$('.inline-editor span.catshow').show();
			$(this).hide();
		});

		$('select[name="_status"] option[value="future"]', bulkRow).remove();

		$('#doaction, #doaction2').click(function(e){
			var n = $(this).attr('id').substr(2);
			if ( $('select[name="'+n+'"]').val() == 'edit' ) {
				e.preventDefault();
				t.setBulk();
			} else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
				t.revert();
			}
		});

		$('#post-query-submit').click(function(e){
			if ( $('form#posts-filter tr.inline-editor').length > 0 )
				t.revert();
		});

	},

	toggle : function(el) {
		var t = this;

		$(t.what+t.getId(el)).css('display') == 'none' ? t.revert() : t.edit(el);
	},

	addEvents : function(r) {
		r.each(function() {
			var row = $(this);
			$('a.editinline', row).click(function() { inlineEditStore.edit(this); return false; });
		});
	},

	setBulk : function() {
		var te = '', c = '', type = this.type;
		this.revert();

		$('#bulk-edit td').attr('colspan', $('.widefat:first thead th:visible').length);
		$('table.widefat tbody').prepend( $('#bulk-edit') );
		$('#bulk-edit').addClass('inline-editor').show();

		$('tbody th.check-column input[type="checkbox"]').each(function(i){
			if ( $(this).attr('checked') ) {
				var id = $(this).val();
				var theTitle = $('#inline_'+id+' .post_title').text() || inlineEditL10n.notitle;
				te += '<div id="ttle'+id+'"><a id="_'+id+'" class="ntdelbutton" title="'+inlineEditL10n.ntdeltitle+'">X</a>'+theTitle+'</div>';
			}
		});

		$('#bulk-titles').html(te);
		$('#bulk-titles a').click(function() {
			var id = $(this).attr('id').substr(1), r = inlineEditStore.type+'-'+id;

			$('table.widefat input[value="'+id+'"]').attr('checked', '');
			$('#ttle'+id).remove();
		});

		// enable autocomplete for tags
		if ( type == 'post' )
			$('tr.inline-editor textarea[name="tags_input"]').suggest( 'admin-ajax.php?action=ajax-tag-search', { delay: 500, minchars: 2, multiple: true, multipleSep: ", " } );
	},

	edit : function(id) {
		var t = this;
		t.revert();

		if ( typeof(id) == 'object' )
			id = t.getId(id);

		var fields = ['store_id', 'altclass', 'store_name', 'store_address', 'store_address2', 'store_city', 'store_state', 'store_zip', 'store_country', 'store_phone', 'store_fax', 'store_url', 'store_description', 'store_category', 'store_special', 'store_lat', 'store_lng'];

		// add the new blank row
		var editRow = $('#inline-edit').clone(true);
		$('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

		if ( $(t.what+id).hasClass('alternate') )
			$(editRow).addClass('alternate');
		$(t.what+id).hide().after(editRow);

		// populate the data
		var rowData = $('#inline_'+id);
		for ( var f = 0; f < fields.length; f++ ) {
			$(':input[name="'+fields[f]+'"]', editRow).val( $('.'+fields[f], rowData).text() );
		}
		if ($('.store_special', rowData).text() == '1') {
			$(':input[name="store_special"]', editRow).attr('checked', 'checked')
		}

		$(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();

	},

	save : function(id) {
		if( typeof(id) == 'object' )
			id = this.getId(id);

		$('table.widefat .inline-edit-save .waiting').show();

		var params = {
			action: 'inline-save',
			post_type: this.type,
			post_ID: id,
			edit_date: 'true'
		};

		var fields = $('#edit-'+id+' :input').fieldSerialize();
		params = fields + '&' + $.param(params);

		// make ajax request
		
		$.post('../wp-content/plugins/simplemap/actions/location-process.php', params,
			function(r) {
				$('table.widefat .inline-edit-save .waiting').hide();

				if (r) {
					if ( -1 != r.indexOf('<tr') ) {
						$(inlineEditStore.what+id).remove();
						$('#edit-'+id).before(r).remove();

						var row = $(inlineEditStore.what+id);
						row.hide();

						row.find('.hide-if-no-js').removeClass('hide-if-no-js');
						inlineEditStore.addEvents(row);
						row.fadeIn();
					} else {
						r = r.replace( /<.[^<>]*?>/g, '' );
						$('#edit-'+id+' .inline-edit-save').append('<span class="error">'+r+'</span>');
					}
				} else {
					$('#edit-'+id+' .inline-edit-save').append('<span class="error">'+inlineEditL10n.error+'</span>');
				}
			}
		, 'html');
		return false;
	},

	revert : function() {
		var id;

		if ( id = $('table.widefat tr.inline-editor').attr('id') ) {
			$('table.widefat .inline-edit-save .waiting').hide();

			if ( 'bulk-edit' == id ) {
				$('table.widefat #bulk-edit').removeClass('inline-editor').hide();
				$('#bulk-titles').html('');
				$('#inlineedit').append( $('#bulk-edit') );
			} else  {
				$('#'+id).remove();
				id = id.substr( id.lastIndexOf('-') + 1 );
				$(this.what+id).show();
			}
		}

		return false;
	},

	getId : function(o) {
		var id = o.tagName == 'TR' ? o.id : $(o).parents('tr').attr('id');
		var parts = id.split('-');
		return parts[parts.length - 1];
	}
};

$(document).ready(function(){inlineEditStore.init();});
})(jQuery);
