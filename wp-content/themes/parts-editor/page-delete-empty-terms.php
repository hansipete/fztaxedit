<?php get_template_part('templates/page', 'header'); ?>
<?php
  
  /*  Query all terms (include empty)
      Delete all that are empty. Exclude parent branches (post counts are not accumulated)
  */

  $terms = get_terms('fz_taxonomy_2013', array('hide_empty'=>false));

  foreach ($terms as $term) {
    
    $children = get_term_children($term->term_id, 'fz_taxonomy_2013');

    if( count($children) == 0 && $term->count == 0 ){
      
      $return = wp_delete_term( $term->term_id, 'fz_taxonomy_2013' );
      
      if(!$return)
        echo "Error deleting: {$term->name}<br>";
      else
        echo "Deleted: {$term->name}<br>";
    }

  }
?>