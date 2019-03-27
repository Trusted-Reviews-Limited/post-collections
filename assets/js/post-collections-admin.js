jQuery( function($) {

	$( document ).ready( function() {
		initSelect();
	});

	$( document ).on( 'widget-added', function( event, widget ) {
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
			allowClear: true
		});
	}

});