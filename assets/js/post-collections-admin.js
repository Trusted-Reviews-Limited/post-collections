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
			placeholder: "Search for an item",
			minimumInputLength: 1,
			allowClear: true,
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				cache: true,
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
			},
		}).on('select2:select', function(e) {
			var itemsInput = $(this).closest( ".widget-content" ).find( "input.js-post-collection-items-input" );
			var inputVal = $( itemsInput ).val();	
			
			$( itemsInput ).val( inputVal + "," + e.params.data.id );

			$(this).closest( ".widget-content" ).find( "select.post-collection-filter-by" ).prop( "selectedIndex", 0 );
		}).on('select2:unselect', function(e) {
			var itemsInput = $(this).closest( ".widget-content" ).find( "input.js-post-collection-items-input" );
			var inputVal = $( itemsInput ).val();	

			if( inputVal.indexOf( e.params.data.id ) !== false ) {
				inputVal = inputVal.replace( e.params.data.id, "" );
				$( itemsInput ).val( inputVal.replace( ",,", "," ) );
			}
		});
	}
});
