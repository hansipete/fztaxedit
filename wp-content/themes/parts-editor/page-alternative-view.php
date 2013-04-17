<div class="span8">
<?php

    // Query all uncategorized fpz
    

    $all_term_ids = get_terms('fz_taxonomy_2013', array( 'fields' => 'ids' ));

    $args = array(
                    'post_type' => 'fz_fzp',
                    'tax_query' => array(
                                            array(
                                                'taxonomy' => 'fz_taxonomy_2013',
                                                'field' => 'id',
                                                'terms' => $all_term_ids,
                                                'operator' => 'NOT IN'
                                            )
                    )
            );

    $uncategorized = new WP_Query( $args );


    echo "<table id='sortable' class='table table-hover table-condensed homeless-list'>
              <caption>Homeless Files</caption>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Descrition</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>";

            while ( $uncategorized->have_posts() ) : $uncategorized->the_post();
                $excerpt = strip_tags(the_excerpt_max_charlength(140));
                echo "<tr>
                        <td data-post-id='{$post->ID}'>{$post->post_title}</td>
                        <td>{$excerpt}</td>
                        <td>
                            <a href='{$post->post_title}'>Detailsa</a>
                        </td>
                      </tr>\n";
                //echo "<li><a href='{$post->guid}' data-fzp-id='{$post->ID}' data-toggle='tooltip' data-placement='top' data-original-title='".htmlentities($post->post_content)."'>{$post->post_title}</a></li>";
            endwhile;
    
    echo "    </tbody>
            </table>";        
 ?>
    <script>
        $(function() {
            // Return a helper with preserved width of cells
            var fixHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };

            $("#sortable tbody").sortable({
                helper: fixHelper
            }).disableSelection();
         });
    </script>

</div><!-- .span8 --> 

<div class="span4">
    <div class="row">
<?php    
    // Query all parts = taxonomy depth=3
    
    $parts = get_terms('fz_taxonomy_2013', array(   'hide_empty'    => true,
                                                    'orderby'       => 'id',
                                                    'fields'        => 'ids'));
    
    foreach ($parts as $part) {
        $fzps = get_posts(array(    'post_type' => 'fz_fzp',
                                    'numberposts' => -1,
                                    'tax_query' => array(
                                        array(  'taxonomy' => 'fz_taxonomy_2013',
                                                'field' => 'id',
                                                'terms' => $part))
                                ));

        $part_term = get_term($part, 'fz_taxonomy_2013');

        echo "<div class='thumbnail span1 ' style='background: #f0f0f0;'>
                <h6>{$part_term->name}</h6>";

            foreach( $fzps as $fzp ){
                setup_postdata($fzp);
                
                echo "<a href='{$fzp->guid}' title='{$fzp->post_title}'><i class='icon-file'></i></a>";
            }


        echo "</div>";
    }
?>
    </div><!-- .row -->
</div><!-- .span4 -->


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