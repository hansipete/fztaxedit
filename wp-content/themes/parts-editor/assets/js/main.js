/* Author:

*/

function selectTags( $selected_row ){
    var packages = [];

    jQuery($selected_row).find('td.lbr_packages span').each( function(){
       packages.push( jQuery(this).attr('class') );
    });

    jQuery('.tagcloud a').removeClass('selected-tag');
    jQuery('.tagcloud').find( packages.join(',') ).addClass('selected-tag');
}

function moveDown(){

	var $selected_row = jQuery('.selected-row')
                		.removeClass('selected-row')
                		.next()
                		.addClass('selected-row');

    selectTags( $selected_row );

}

function moveUp(){

	var $selected_row = jQuery('.selected-row')
		                  .removeClass('selected-row')
		                  .prev()
		                  .addClass('selected-row');

    selectTags( $selected_row );    

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

