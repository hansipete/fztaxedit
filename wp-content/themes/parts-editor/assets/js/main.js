/* Author:

*/

function selectTags( $selected_part ){
    var packages = [];

    jQuery($selected_part).find('td.lbr_packages span').each( function(){
       packages.push( jQuery(this).attr('class') );
    });

    jQuery('.tagcloud a').removeClass('selected-tag');
    jQuery('.tagcloud').find( packages.join(',') ).addClass('selected-tag');
}

function moveDown(){

	var $selected_part = jQuery('.selected')
                		.removeClass('selected')
                		.next()
                		.addClass('selected');

    jQuery('html, body').animate({
         scrollTop: jQuery(".selected").offset().top - 170
     }, 100);                    
                        

    selectTags( $selected_part );

}

function moveUp(){

	var $selected_part = jQuery('.selected')
		                  .removeClass('selected')
		                  .prev()
		                  .addClass('selected');

    jQuery('html, body').animate({
         scrollTop: jQuery(".selected").offset().top - 170
     }, 100); 

    selectTags( $selected_part );    

}


jQuery(document).keydown( function(event){
    
    switch (event.keyCode) {
            case 38:
                moveUp();
                break;
            case 40:
                moveDown();
                break;
    }
});

jQuery(document).ready( function(){

    // click auf taxonomy item in sidebar

    jQuery('ul#fz_bins li a').click( function(e){
        
        var bin_id = jQuery(this).parent('li.cat-item').attr('class').substr(18); // class="cat-item cat-item-142"
        var post_id = jQuery('.selected span.part-id').text();

        jQuery.ajax({
            url: wpajax.url,
            type: 'POST',
            data: 'action=fz_add_tax_to_post&post_id=' + post_id + '&tax_id=' + bin_id,

            success: function(data) {
                jQuery('.selected .part-taxonomies').html(data);
            }
        });

        
        
        return false;
    } );


});



