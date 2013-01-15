<?php get_template_part('templates/page', 'header'); ?>

<?php

    $i = 0;
    while ( have_posts() ) : the_post();

?>
		
<div class="part <?php if($i++ == 0) echo 'selected'; ?> part-<?php the_ID(); ?>" style="padding: 4px 8px; margin-bottom: 2px; float: left; width: 100%;">
    
    <h5><?php the_title(); ?></h5>
    
    <div class="info">
    	<?php the_excerpt(); ?>
    </div>

    <em class="lab">#</em>
    <div style="font-size: 11px;">
        <span class="part-id"><?php the_ID(); ?></span>
    </div>

    <em class="lab">Library origin:</em>
    <div style="font-size: 11px;">
        <?php echo get_the_term_list( $post->ID, 'fz_lbr', null, ', '); ?>
    </div>
    
    <em class="lab">Library part name:</em>
    <div style="font-size: 11px;">
        <?php $meta_values = get_post_meta($post->ID, 'lbr_partname'); echo $meta_values[0]; ?>
    </div>

    <em class="lab">Library package:</em>
    <div class="part-packages">
    	<?php $packages = wp_get_post_terms( $post->ID, 'fz_lbr_packages' ); echo "<span class='part-pkg'>".$packages[0]->name."</span>"; ?>
    </div>
    
    <em class="lab">New Fritzing bin:</em>
    <div class="part-taxonomies">
        <?php 
                $taxonomy = wp_get_post_terms( $post->ID, 'fz_bins' ); 

                foreach( $taxonomy as $tax ){
                    echo "<span class='part-tax'>".$tax->name."</span>";    
                }    
        ?>
    </div>
</div>

<?php
    
    if ( function_exists( 'wp_pagenavi' ) ) {
        wp_pagenavi() );
    }
?>