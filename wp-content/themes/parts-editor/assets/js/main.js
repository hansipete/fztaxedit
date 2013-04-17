$(document).ready( function(){



	$fzp_filter_form = $('.fzp-filter-form');
	$fzp_results = $('.fzp-results');

	$fzp_filter_form.on('submit', function(e){

		$.get( '/', $(this).serialize(), function( data ){

            // Place the fetched results inside the #content element
            $('.fzp-results').html( $(data).find('.fzp-results') );

        });

		return false;
	});





































}); // document ready