<?php get_template_part('templates/page', 'header'); ?>

<?php

    /*
    $paged = 1;
    if ( get_query_var('paged') ) $paged = get_query_var('paged');
    if ( get_query_var('page') ) $paged = get_query_var('page');
    $i = 0;
    $loop = new WP_Query( array( 'post_type' => 'fz_fzp', 'paged' => $paged) );
    while ( $loop->have_posts() ) : $loop->the_post();
    */

    // custom sql query to group fzp with same title
    $query_result = query_group_fzp();

    $i = 0;
    foreach ($query_result as $post) : setup_postdata($post);
?>
		
<div class="part <?php if($i++ == 0) echo 'selected'; ?> part-<?php the_ID(); ?>" style="padding: 4px 8px; margin-bottom: 2px; float: left; width: 100%;">
    
    <h5>#<?php echo $i." "; the_title(); ?></h5>
    
    <div class="info">
    	<?php the_excerpt(); ?>
    </div>

    <em class="lab">#</em>
    <div style="font-size: 11px;">
        <span class="part-id"><?php the_ID(); ?></span>
    </div>

    <em class="lab">Original bin:</em>
    <div style="font-size: 11px;">
        <?php echo get_the_term_list( $post->ID, 'fz_original_bin', null, ', '); ?>
    </div>
    
    <em class="lab">Original family:</em>
    <div style="font-size: 11px;">
        <?php echo get_the_term_list( $post->ID, 'fz_original_family'); ?>
    </div>

    <em class="lab">Original tags:</em>
    <div style="font-size: 11px;">
        <?php echo get_the_term_list( $post->ID, 'fz_original_tags', '<span class="part-tax">', '</span><span class="part-tax">', '</span>'); ?>
    </div>
    
    <em class="lab">New Fritzing bin:</em>
    <div class="part-taxonomies">
        <?php 
            /*
                $taxonomy = 0; wp_get_post_terms( $post->ID, 'fz_bins' ); 

                foreach( $taxonomy as $tax ){
                    //echo "<span class='part-tax'>".$tax->name."</span>";    
                }    
            */
        ?>
    </div>
</div>

<?php
    endforeach; ?>
    
<div class="navigation">
    <div class="previous panel"><?php previous_posts_link('&laquo; newer') ?></div>
    <div class="next panel"><?php next_posts_link('older &raquo;') ?></div>
</div>
<?php
    if ( function_exists( 'wp_pagenavi' ) ) {
       /*
        echo "navi:";
        wp_pagenavi( array( 'query' => $query_result ) );
        wp_reset_postdata();
        */
    }
?>