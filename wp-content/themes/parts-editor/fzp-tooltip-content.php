<?php 

  // use wordpress but not headers/footers
  require_once('../../../wp-blog-header.php');

  // paths to svg shizzle
  $fz_abspath = ABSPATH . 'fritzing/';
  $fz_url = get_bloginfo('wpurl') . '/fritzing/';
  
  // grab id from url
  $fzp_id = !empty($_GET['fzp_id']) ? $_GET['fzp_id'] : false;

  // query fzp post object
  $post = get_post($fzp_id);

  // path to sparkfun fzps
  $fzp_file = "{$fz_abspath}pdb/user/" . get_post_meta( $post->ID, 'fzpFile', true);

  // if this not exist, its a core file
  $fzp_file = file_exists($fzp_file) ? $fzp_file : "{$fz_abspath}pdb/core/" . get_post_meta( $post->ID, 'fzpFile', true);

  
  if( file_exists($fzp_file) ) {

      $fzp = simplexml_load_file( $fzp_file );

      $breadboard_src = get_svg_img( current( $fzp->views->breadboardView->layers['image'] ) );
      $schematic_src  = get_svg_img( current( $fzp->views->schematicView->layers['image'] ) );
      $pcb_src        = get_svg_img( current( $fzp->views->pcbView->layers['image'] ) );
      
      echo "<div style='width: 610px;'><img class='tooltip-graphic' src='{$breadboard_src}' title='{$breadboard_src}'>
            <img class='tooltip-graphic' src='{$schematic_src}' title='{$schematic_src}'>
            <img class='tooltip-graphic' src='{$pcb_src}' title='{$pcb_src}'></div>";

  }
  else {
    echo "<strong>Could not fetch .fzp</strong>";
  }

  // helper

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
  

?>