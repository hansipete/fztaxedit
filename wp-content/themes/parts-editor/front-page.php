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
            <table class='table table-hover table-condensed fzp-results'>
              <caption>Count: {$wp_query->found_posts}</caption>
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
                            <a href='{$post->guid}'>Info</a>
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
                <h4>{$bin->name}<a href='+category' title='Add category' class='add-button add-category-button pull-right'><i class='icon-plus'></i></a></h4>";        

        // BINS > CATEGORIES
        $categories = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $bin->term_id, 'hide_empty' => 0 ) );
        foreach ($categories as $category) {
            echo "<div class='category' data-term-id='{$category->term_id}' data-category-color='{$bin->description}'>
                  <h6>{$category->name}<a href='+part' title='Add part' class='add-button add-part-button pull-right'><i class='icon-plus'></i></a></h6>";

                      // BINS > CATEGORIES > PART
                      $parts = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $category->term_id, 'hide_empty' => 0 ) );
                      foreach ($parts as $part) {
                          
                          $term = get_term( $part->term_id, 'fz_taxonomy_2013' );
$count = $term->count;

                          echo "<div class='part' data-term-id='{$part->term_id}' style='background: {$bin->description};'>
                                 <i class='icon-pencil'></i>&nbsp;<span class='name'>{$part->name}</span>
                                 <small class='pull-right'>{$count}</small>
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

<script>
$(document).ready(function(){

  // click on add category button
  $(".add-category-button").live('click', function(){

    $category_dom = "<div class='category'><input type='text' class='input-small inline-edit-input inline-edit-category-input'></div>"

    $(this).parents('.bin-container').append($category_dom)
    .find('input').focus();

    $('#taxonomy-index').masonry('reload');

    return false;

  });

  // click on add part button
  $(".add-part-button").live('click', function(){

    $selected_tr = $(".fzp-results tr.selected");
    fzp_name = $selected_tr.find('strong').text();
    fzp_id = $selected_tr.data("post-id");
    category_color = $(this).parents('.category').data('category-color');

    $(this).parents('.category').append("<div class='part new-part' style='background: "+category_color+";'><input type='text' value='"+fzp_name+"' class='input-small inline-edit-input' onFocus='this.select()'></div>")
    .find('input').focus();

    $('#taxonomy-index').masonry('reload');

    return false;

  });



  // append to part
  $(".part").live('click', function(){
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
  $(".part i").live('click', function(){
    term_id = $(this).parent('.part').data('term-id');
    part_name = $(this).find('.name').text();


    $(this).parent('.part').html('<input type="text" class="input-small inline-edit-input" name="'+term_id+'" placeholder="'+part_name+'">')
    .find('input').focus();

    return false;
  });

  // on submit…
  $(".inline-edit-input").live("keypress", function(e) {

    if (e.which == 13 ) {

      e.preventDefault();

      // if part is edited…
      if( !$(e.currentTarget).hasClass("inline-edit-category-input") ){

        var $part_div = $(this).parent('.part');
        var new_part_name = $(this).val();
        var term_id = $part_div.data('term-id');

        $part_div.addClass('loading');
        $part_div.html('<i class="icon-pencil"></i>&nbsp;<span class="name">'+new_part_name+'</span><small class="part-count pull-right">0</small>');
        
        var data = {action: 'fz_update_term_name', term_id: term_id, new_part_name: new_part_name};

        if( $part_div.hasClass('new-part') ){
          var selected_fzp_id = $('.fzp-results tr.selected').data("post-id");
          var cat_term_id = $part_div.parents('.category').data("term-id");
          data = {action: 'fz_new_part', cat: cat_term_id, fzp_id: selected_fzp_id, new_part_name: new_part_name};
        }

        $.ajax({
            type: "POST",
            url: wpajax.url,
            data: data,

            success:function(data){
              $part_div.removeClass('loading new-part');
              $part_div.data('term-id', data);
            }
        });

      }
      // if category is edited
      else {
        var bin_term_id = $(this).parents('.bin-container').data('term-id');
        var category_name = $(this).val();
        var $category_dom = $(this).parents('.category');

        $category_dom.addClass('loading');

        $.ajax({
            type: "POST",
            url: wpajax.url,
            data: { action: 'fz_new_category_in_bin', term_id: bin_term_id, category_name: category_name },

            success:function(data){
              $category_dom.html("<h6>"+category_name+"<a href='+part' title='Add part' class='add-button add-part-button pull-right'><i class='icon-plus'></i></a></h6>");
              $category_dom.data("term-id", data)
              $category_dom.removeClass('loading');
            }
        });

      }
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

  $(document).keydown( function(e){

      var tag = e.target.tagName.toLowerCase();
      
      if ( tag != 'input' && tag != 'textarea'){
        switch (e.keyCode) {
          case 87: skip_selected(-1); break;
          case 83: skip_selected(1); break;
        }
      }
  });

  $(result_container).find('tr').click( function(e){
    $(this).addClass('selected info').siblings('tr').removeClass('selected info');
    return false;
  });

});
</script>