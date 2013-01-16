$(document).ready( function(){

// ajax apply taxonomy to family (its corresponding parts) on click 

$('#sidebar a.group').live('click', function(e){
        
        var group_id = getId( $(this) );
        var family_id = getId( $('.family-selected') );
        var family_selected_class = $('.family-selected').attr('class');

        $.ajax({
            url: wpajax.url,
            type: 'POST',
            data: 'action=fz_apply_tax_to_family&family_id='+family_id+'&term_id='+group_id,

            success: function(data) {
                //$('.family-selected').html(window.location + ' #groups > *);
            }
        });
        
        return false;
    } );



// scroll through family items by keyboard or clicking    

    //mark the first item
    $('.family:first')
        .addClass('family-selected');

    // click on family item
    $('.family').click( function(){
        $('.family').removeClass('family-selected');
        $(this).addClass('family-selected');

        return false;
    });

    // catch key strokes
    $(document).keydown( function(event){
        switch (event.keyCode) {
                case 38: nextFamilyItem(-1); break;
                case 40: nextFamilyItem(1); break;
        }
    });

    // up down functions
    function nextFamilyItem(dir){

        // for performance
        var $selected_family_item = $('.family-selected');
        var $family_items = $('.family');
        // position of selected element within all the elements (first = 0)
        var selected_pos = $family_items.index( $selected_family_item );
        var count_family_items = $family_items.length; //

        // previous
        if( (dir=="-1") && (selected_pos>0) ){
            $selected_family_item
                .removeClass('family-selected')
                .prev()
                .addClass('family-selected');
        }

        // next
        if( (dir=="1") && (selected_pos<count_family_items-1) ){
            $selected_family_item
                .removeClass('family-selected')
                .next()
                .addClass('family-selected');
        }

        // scroll to the div (top - offset to go a bit more to the middle of the page)
        $('html,body').animate({ scrollTop:$('.family-selected').offset().top - 100 }, 100);

    }    

});