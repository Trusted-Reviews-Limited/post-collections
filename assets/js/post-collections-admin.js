jQuery( function($) {

	$( document ).ready( initSelect() );

	$( document ).on( 'widget-added widget-updated', function( event, widget ) {
		if( widget.context.outerHTML.indexOf( "collection_widget" ) ) {
			initSelect();
		}
	});

	function initSelect() {
		$('.js-post-collection-items-wrapper').select2({
			multiple: true,
			placeholder: "Search for an item",
			minimumInputLength: 1,
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				quietMillis: 250,
				data: function (term, page) {
					return {
						action: 'post_collection_search_with_terms',
						search: term,
						filter: $(this).closest( ".widget-content" ).find( "select.post-collection-filter-by" ).val(),
					};
				},
				results: function (data, page) {
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
		}).select2('val', []).on('select2-selecting', function(e) {
			console.log(this);
			console.log( $(this).closest( ".widget-content" ).find( "input.js-post-collection-items-wrapper" ).val().indexOf( e.val ) );
			
			if( $(this).closest( ".widget-content" ).find( "input.js-post-collection-items-wrapper" ).val().indexOf( e.val ) == -1 ) {
				$(this).closest( ".widget-content" ).find( "input.js-post-collection-items-wrapper" ).val( e.val );
			}
			
			$(this).closest( ".widget-content" ).find( "select.post-collection-filter-by" ).prop('selectedIndex',0);
		});
	}

});