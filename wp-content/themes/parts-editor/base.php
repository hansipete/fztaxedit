<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

 <?php
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      get_template_part('templates/header-top-navbar');
    } else {
      get_template_part('templates/header');
    }

    //include roots_template_path();
?>


<div class="container-fluid">
  <div class="row-fluid">
    <div class="span4">
      <h2>Single FZP Files</h2>
  
  <!-- FILTER -->

  <form class="form-search fzp-filter-form">
    <?php 
          wp_dropdown_categories( array(  'taxonomy'=>'fz_taxonomy_2013',
                                          'hierarchical' => true,
                                          'depth'=> 1,
                                          'show_option_all' => 'Uncategorized',
                                          'class' => 'input-medium')); 
    ?>
    <input type="text" name="s" class="input-medium">
    <button type="submit" class="btn">Filter</button>
  </form> 

  <!-- CONTENT -->  
  <?php
/*
    $all_term_ids = get_terms('fz_taxonomy_2013', array( 'fields' => 'ids' ));

    $args = array(
                    'post_type' => 'fz_fzp',
                    'posts_per_page' => 20,
                    //'s' => $s,
                    'tax_query' => array(
                                            array(
                                                'taxonomy' => 'fz_taxonomy_2013',
                                                'field' => 'id',
                                                'terms' => $all_term_ids,
                                                'operator' => 'NOT IN'
                                            )
                    )
            );
    
    global $wp_query;
    $merged = array_merge( $wp_query->query_vars, $args );
    //query_posts( $merged );
*/    
    global $wp_query;
    $args = array_merge( $wp_query->query_vars, array( 'post_type' => 'fz_fzp' ) );
    query_posts( $args );


    echo "<table id='sortable' class='table table-hover table-condensed fzp-results'>
              <!--<thead>
                <tr>
                  <th>Name</th>
                  <th>Descrition</th>
                  <th></th>
                </tr>
              </thead>-->
              <tbody>";

            while ( have_posts() ) : the_post();
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
    </div>
    <div class="span8">
      <!--Body content-->
      SPEASE THWA?
    </div>
  </div>
</div>

  <?php wp_footer(); ?>

</body>
</html>
