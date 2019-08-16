jQuery( function($) {

	$( document ).ready( initSelect() );

	$( document ).on( 'widget-added widget-updated', function( event, widget ) {
		if( widget.context.outerHTML.indexOf( "collection_widget" ) ) {
			initSelect();
		}
	});

	function initSelect() {
		if ($('.js-post-collection-items-wrapper').length < 1) {
			return;
		}
	
		$('.js-post-collection-items-wrapper').select2({
			multiple: true,
			placeholder: "Search for an item",
			minimumInputLength: 1,
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						action: 'post_collection_search_with_terms',
						search: params.term,
						filter: $(this).closest( ".widget-content" ).find( "select.post-collection-filter-by" ).val(),
					};
				},
				processResults: function (data, page) {
					let myResults = [];
					if (data.results) {
						$.each(data.results, function (index, item) {
							var prefix = item.type === 'post_type' ? item.post_type : item.taxonomy;
							myResults.push({
								'id': item.type + '_' + item.ID,
								'text': prefix + ': ' + item.title
							});
						});
					}
					return {
						results: myResults
					};
				},
				cache: true
			},
			allowClear: true,
			initSelection: function (element, callback) {
				var selectedValues = $( element ).data( "value" );
				
				if( selectedValues && selectedValues.length !== 0 ) {
					callback( selectedValues );
				}
			}
		}).on('select2:select', function(e) {
			var itemsInput = $(this).closest( ".widget-content" ).find( "input.js-post-collection-items-input" );
			var itemIDs = $( itemsInput ).val();

			$( itemsInput ).val( ',' + itemIDs );

			$(this).closest( ".widget-content" ).find( "select.post-collection-filter-by" ).prop('selectedIndex',0);
		});
	}

});
