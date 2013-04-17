<?php
    // BINS
    $bins = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => 0, 'hide_empty' => 0 ) );
    foreach ( $bins as $i => $bin) {
        echo "<div class='span8 bin' data-term-id='{$bin->term_id}' id='bin-{$bin->term_id}'>
                <h4>{$bin->name}</h4>
                <div class='row'>";        

        // BINS > CATEGORIES
        $categories = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $bin->term_id, 'hide_empty' => 0 ) );
        foreach ($categories as $category) {
            echo "<div class='span8 category' data-term-id='{$category->term_id}' style='background: {$bin->description};'>
                  <h5>{$category->name}</h5>
                  <div class='row'>";

            // BINS > CATEGORIES > PART
            $parts = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $category->term_id, 'hide_empty' => 0 ) );
            foreach ($parts as $part) {
                echo "<div class='span2 part'>
                       {$part->name}";
                
                // BINS > CATEGORIES > PART > VARIANTS
                $variants = new WP_Query( array(
                                              'post_type' => 'fz_fzp', 
                                              'tax_query' => array(
                                                array(
                                                                    'taxonomy' => 'fz_taxonomy_2013',
                                                                    'terms' => $part->term_id,
                                                                    'field' => 'term_id'
                                                )
                )));

                echo "<ul class='fzp-list unstyled' data-part-term-id='{$part->term_id}'>";
                while ( $variants->have_posts() ) : $variants->the_post();
                    echo "<li><i class='icon-file'></i> <a href='{$post->guid}' data-fzp-id='{$post->ID}' data-toggle='tooltip' data-placement='top' data-original-title='{$post->post_content}'>{$post->post_title}</a></li>";
                endwhile;
                echo "</ul>";
                echo "</div>"; //part
            }

            echo "</div></div>"; //category
        }   
        
        echo "</div></div>"; // bin row
    }
 ?>

 <script>


    $(document).ready( function(){

        
        $fzplists = $(".fzp-list");

        // sortable for drag and drop to dock
        $fzplists.sortable({
          connectWith: ".fzp-list"
        }).disableSelection();

        /*
            ELEMENT DRAGGED OUT OF PART EVENT
        */
        $fzplists.on("sortremove", function( event, ui ) {
            
            $fzp_a = $(event.srcElement);
            $part_ul = $(event.target);
            part_term_id = $part_ul.data('part-term-id');
            
            // do nothing when the dock is left (no ajax)
            if( part_term_id ){
                
                $.ajax({ url: wpajax.url, type: 'POST',
                    data: {
                            action:         'fz_remove_fzp_from_part',
                            part_term_id:   part_term_id,
                            fzp_id:         $fzp_a.data('fzp-id')
                    },
                    success: function(data) {
                        //console.log(data);
                    }
                });               

            }

        });

        /*
            ELEMENT DRAGGED IN PART EVENT
        */
        $fzplists.on("sortreceive", function( event, ui ) {
            
            $fzp_a = $(event.srcElement);
            $part_ul = $(event.target);
            part_term_id = $part_ul.data('part-term-id');
            
            // do nothing when the dock is left (no ajax)
            if( part_term_id ){
                
                $.ajax({ url: wpajax.url, type: 'POST',
                    data: {
                            action:         'fz_add_fzp_to_part',
                            part_term_id:   part_term_id,
                            fzp_id:         $fzp_a.data('fzp-id')
                    },
                    success: function(data) {
                        //console.log(data);
                    }
                });               

            }
            else {
                $("#new-part-title").val( $fzp_a.text() );
            }

        });        


    // tool tips
    var $tooltips = $("*[data-toggle]");
    $tooltips.tooltip();

});


    

    
    
</script>