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
      
    global $wp_query;
    $s = $wp_query->query_vars['s'];

    // to query parts that are in NONE of the terms
    $all_term_ids = get_terms('fz_taxonomy_2013', array( 'fields' => 'ids' ));

    $args = array(
                    'post_type' => 'fz_fzp',
                    'posts_per_page' => 20,
                    's' => $s,
                    'tax_query' => array(
                                            array(
                                                'taxonomy' => 'fz_taxonomy_2013',
                                                'field' => 'id',
                                                'terms' => $all_term_ids, //here
                                                'operator' => 'NOT IN'
                                            )
                    )
            );
    
    // Merge with existing query if one exists
    $args = ( $wp_query && !empty( $wp_query->query ) ) ? array_merge( $wp_query->query , $args ) : $args;
  
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
                echo "<tr data-post-id='{$post->ID}'>
                        <td>{$post->post_title}</td>
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

<script>
$(document).ready(function(){

  var result_container = ".fzp-results";

  $('.fzp-filter-form').submit( function(e){
    
        e.preventDefault();
        
        var s = $(this).find("input[name='s']").val();
        
        $.ajax({
            type: "POST",
            url: "<?php bloginfo('wpurl'); ?>/search/"+s,
            data: $(this).serialize(),

            success:function(data){
              // get results dom
              result = $(result_container, data).html();

              // update container
              $(result_container).html(result);

              //highlight first tr
              $(result_container).find('tr:first').addClass('selected info');
            }
        });   

  });


  // select active (key up down)

  // initial
  $(result_container).find('tr:first').addClass('selected info');

  function skip_selected(dir){
    var $selected_tr = $(result_container).find('tr.selected');

    if( (dir<0) && ($selected_tr.prev('tr').length > 0) ){
        $selected_tr.removeClass('selected info').prev().addClass('selected info');  
    } 

    if( (dir>0) && ($selected_tr.next('tr').length > 0) ){
        $selected_tr.removeClass('selected info').next().addClass('selected info');  
    }   
  }

  $(document).keydown( function(event){
      switch (event.keyCode) {
              case 38: skip_selected(-1); break;
              case 40: skip_selected(1); break;
      }
  });

  $(result_container).find('tr').click( function(e){
    $(this).addClass('selected info').siblings('tr').removeClass('selected info');
    return false;
  });


});
</script>

  <?php wp_footer(); ?>

</body>
</html>
