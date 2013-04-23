<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

 <?php
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      //get_template_part('templates/header-top-navbar');
    } else {
      //get_template_part('templates/header');
    }

    //include roots_template_path();
?>


<div class="container">
  <div class="row">
    <div class="span4" data-spy="affix" data-offset-top="0">
      <div class="sidebar">
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
    <p class="muted" style="margin-top: 6px;"><small>Help: keyboard navigation with [W] / [S] to navigate up/down.</small></p>
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


    echo "<div>
            <table class='table table-hover table-condensed fzp-results' style='width: 100%'>
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
                        <td><strong>{$post->post_title}</strong></td>
                        <td>{$excerpt}</td>
                        <td>
                            <a href='{$post->post_title}'>Info</a>
                        </td>
                      </tr>\n";
                //echo "<li><a href='{$post->guid}' data-fzp-id='{$post->ID}' data-toggle='tooltip' data-placement='top' data-original-title='".htmlentities($post->post_content)."'>{$post->post_title}</a></li>";
            endwhile;

    echo "    </tbody>
            </table>
          </div>";
    ?> 
      </div><!-- sidebar -->
    </div><!-- span4 -->

    <div class="offset4">
      <!--Body content-->
      <h2>Taxonomy & Parts</h2>
      <div class='row' id='taxonomy-index'>
<?php
      // BINS
    $bins = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => 0, 'hide_empty' => 0 ) );
    foreach ( $bins as $i => $bin) {
        echo "<div class='span2 bin-container' data-term-id='{$bin->term_id}' id='bin-{$bin->term_id}'  style='border-color: {$bin->description};'>
                <h4>{$bin->name}</h4>";        

        // BINS > CATEGORIES
        $categories = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $bin->term_id, 'hide_empty' => 0 ) );
        foreach ($categories as $category) {
            echo "<div class='category' data-term-id='{$category->term_id}'>
                  <h6>{$category->name}</h6>";

                      // BINS > CATEGORIES > PART
                      $parts = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $category->term_id, 'hide_empty' => 0 ) );
                      foreach ($parts as $part) {
                          echo "<div class='part' data-term-id='{$part->term_id}' style='background: {$bin->description};'>
                                 <i class='icon-pencil'></i>&nbsp;<span class='name'>{$part->name}</span>
                                 <small class='pull-right'>(0)</small>
                                </div>";
                      }

                echo "</div>";
        }

        echo "</div>";
    }
?>
      
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // append to part
  $(".part").click( function(){
    $selected_tr = $(".fzp-results tr.selected");

    term_id = $(this).data("term-id");
    fzp_id = $selected_tr.data("post-id");

    $selected_tr.css('background', $(this).css('background'));    

    $.ajax({
            type: "POST",
            url: wpajax.url,
            data: {action: 'fz_add_fzp_to_part', part_term_id: term_id, fzp_id: fzp_id},

            success:function(data){
              $selected_tr
                .fadeOut('fast', function(){
                  $(this).removeClass('selected info')
                  .next('tr').addClass('selected info');
                })
            }
    });
    
    return false;
  });


  // inline edit of parts
  $(".part i").click( function(){
    term_id = $(this).parent('.part').data('term-id');
    part_name = $(this).text();


    $(this).parent('.part').html('<input type="text" class="input-small inline-edit-input" name="'+term_id+'" placeholder="'+part_name+'">')
    .find('input').focus();

    return false;
  });

  // on submit…
  $(".inline-edit-input").live("keypress", function(e) {

    if (e.which == 13) {
        e.preventDefault();       

        var $part_div = $(this).parent('.part');
        var new_part_name = $(this).val();
        var term_id = $part_div.data('term-id');

        $part_div.addClass('loading');
        $part_div.html('<i class="icon-pencil"></i>&nbsp;<span class="name">'+new_part_name+'</span><small class="pull-right">(0)</small>');
        
        $.ajax({
            type: "POST",
            url: wpajax.url,
            data: {action: 'fz_update_term_name', term_id: term_id, new_part_name: new_part_name},

            success:function(data){
              $part_div.removeClass('loading');
            }
        });
    }
});

  //mansonry for categories etc.
  $('#taxonomy-index').masonry({
      itemSelector: '.bin-container'
  });

  //query sidebar content

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
              case 87: skip_selected(-1); break;
              case 83: skip_selected(1); break;
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
