<?php get_template_part('templates/page', 'header'); ?>

<?php

    $paged = 1;
    if ( get_query_var('paged') ) $paged = get_query_var('paged');
    if ( get_query_var('page') ) $paged = get_query_var('page');
    $i = 0;
    $loop = new WP_Query( array( 'post_type' => 'fz_fzp', 'paged' => $paged) );
    while ( $loop->have_posts() ) : $loop->the_post();
   
    $terms = wp_get_object_terms( $post->ID, 'fz_taxonomy2' );

?>

<div class="part <?php if($i++ == 0) echo 'selected'; ?> part-<?php the_ID(); ?> <?php if( !empty($terms) ) echo "success"; ?>" style="padding: 4px 8px; margin-bottom: 2px; float: left; width: 100%;">
    
    <h5><?php the_title(); ?></h5>

    <?php echo '<a href="'.get_edit_post_link().'"">Edit</a>'; ?>
    
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
    
    <div class="part-taxonomies">
        <?php echo hey_top_parents('fz_taxonomy2', $post->ID); ?>
    </div>
</div>

<?php
    endwhile; ?>
    
<div class="navigation">
    <?php
    if ( function_exists( 'wp_pagenavi' ) ) {
       
        wp_pagenavi( array( 'query' => $loop ) );
        wp_reset_postdata();
        
    }
    ?>
</div>