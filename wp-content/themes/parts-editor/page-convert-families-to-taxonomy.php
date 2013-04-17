<?php get_template_part('templates/page', 'header'); ?>
<?php

	// Query all top level elements
	$args = array(
	    'orderby'       => 'name', 
	    'order'         => 'ASC',
	    'hide_empty'    => false,  
	    'parent'        => 0,
	    'hierarchical'  => true, 
	    'child_of'      => 0
	); 

	$terms = get_terms('fz_taxonomy_2013', $args);

   foreach ($terms as $term) {

      // query all child elements
         $child_args = array(
             'orderby'       => 'name', 
             'order'         => 'ASC',
             'hide_empty'    => false,  
             'hierarchical'  => true, 
             'child_of'      => $term->term_id
      );

      // echo "bin" title
      echo "<div class='row'>
               <h3 id='{$term->slug}'>{$term->name}</h3>";

      $child_terms = get_terms('fz_taxonomy_2013', $child_args);

      foreach ($child_terms as $child_term) {

         // echo box with section title
         echo "<div class='span2 well'>
                  <h5 id='{$child_term->slug}'>{$child_term->name}</h5>"; 

         // query parts      
         $my_query = new WP_Query( array(
                                          'post_type' => 'fz_fzp', 
                                          'tax_query' => array(
                                            array(
                                                                'taxonomy' => 'fz_taxonomy_2013',
                                                                'terms' => $child_term->term_id,
                                                                'field' => 'term_id'
                                            )
         )));

         while ( $my_query->have_posts() ) : $my_query->the_post();
            
            echo "<a href=\"".get_permalink()."\">".$post->post_title.      "</a><br>";

            // family name
            $family = current( wp_get_post_terms( $post->ID, 'fz_original_family' ) );

            // check if subsubcategory with family->name exists (in this directory)
            $term = term_exists($family->name, 'fz_taxonomy_2013', $child_term->term_id);

            // if term exists
            if ($term !== 0 && $term !== null) {
              echo "<p class='info'>{$family->name}' already category exists!</p>";
            }

            // if not:
            else {
              
              wp_insert_term(
                $family->name, // the term 
                'fz_taxonomy_2013', // the taxonomy
                array(
                  'parent'=> $child_term->term_id
                )
              );

            }  

            // in every case – link the post/part to the subsubcategory 
            wp_set_object_terms( $post->ID, $family->name, 'fz_taxonomy_2013');            
            
         endwhile;

         //need to have this (bug in wp?)
         delete_option("fz_taxonomy_2013_children");

         echo "</div>";

      }   

      echo "</div>";

   }


?>
