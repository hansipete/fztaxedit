<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class() ?> id="post-<?php the_ID(); ?>">
    <header>
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>

<?php

    $fz_abspath = ABSPATH . 'fritzing/';
    $fz_url = get_bloginfo('wpurl') . '/fritzing/';

    $fzp_file = $fz_abspath . 'pdb/user/' . rwmb_meta('fzpFile');
    $fzp = simplexml_load_file( $fzp_file );

          function get_svg_img($svg){
            $fz_abspath = ABSPATH . 'fritzing/';
            $fz_url = get_bloginfo( 'wpurl' ) . '/fritzing/parts/svg/';

            if( file_exists( $fz_abspath . 'parts/svg/user/' . $svg ) ){
              $img_src = $fz_url . 'user/' . $svg;
            } else {
              $img_src = $fz_url . 'core/' . $svg;
            }

            return $img_src;
          }

    $breadboard_src = get_svg_img( current( $fzp->views->breadboardView->layers['image'] ) );
    $schematic_src  = get_svg_img( current( $fzp->views->schematicView->layers['image'] ) );
    $pcb_src        = get_svg_img( current( $fzp->views->pcbView->layers['image'] ) );
    
    echo "
            <div class='svg span3'><img src='{$breadboard_src}' title='{$breadboard_src}'></div>
            <div class='svg span3'><img src='{$schematic_src}' title='{$schematic_src}'></div>
            <div class='svg span2'><img src='{$pcb_src}' title='{$pcb_src}'></div>
          \n";
  
?>

  </article>
<?php endwhile; ?>