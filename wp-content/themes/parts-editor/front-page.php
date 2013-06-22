      <!--Body content-->
      <h2>Taxonomy & Parts</h2>
      <div class='row' style="margin: 0">
<?php
      // BINS
    $bins = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => 0, 'hide_empty' => 0 ) );
    foreach ( $bins as $i => $bin) {
        echo "<div class='page-break'></div>";
        echo "<div class='row' data-term-id='{$bin->term_id}' id='bin-{$bin->term_id}'  style='border-color: {$bin->description};'>
                <h4>{$bin->name}</h4>";        

        // BINS > CATEGORIES
        $categories = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $bin->term_id, 'hide_empty' => 0 ) );
        foreach ($categories as $category) {
            echo "<div class='span2 category' data-term-id='{$category->term_id}' data-category-color='{$bin->description}'>
                  <h6><a href='#' data-term-id='{$category->term_id}' class='inline-editing' data-inline-edit-type='category' data-original-title='New category name'>{$category->name}</a></h6>
                  <a href='#' data-inline-edit-type='part-delete' data-term-id='{$category->term_id}' class='pull-right inline-editing-delete' data-original-title='Delete'>X</a>
                  <ul class='unstyled partslist'>";

                      // BINS > CATEGORIES > PART
                      $parts = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $category->term_id, 'hide_empty' => 0 ) );
                      foreach ($parts as $part) {
                          
                          $term = get_term( $part->term_id, 'fz_taxonomy_2013' );
                          $count = $term->count;

                          echo "<li class='part' data-term-id='{$part->term_id}' style='background: {$bin->description};'>
                                 <a href='#' data-inline-edit-type='part' data-term-id='{$part->term_id}' class='name inline-editing' data-original-title='New part name'>{$part->name}</a>
                                 <small class='pull-right'>{$count}</small>
                                </li>";
                      }

                echo "</ul></div>";
        }

        echo "</div>";

        break;   /////DEEEEEBUGGGGGGG!
    }
?>
      
      </div>

<script>
$(document).ready(function(){

  $(".part").draggable({
    revert: true,
    cursor: "move",
    opacity: 0.7
  });

  $(".part").droppable({
      accept: ".part",
      hoverClass: "ui-state-hover",
      drop: function(ev, ui) {
          ui.draggable.remove();
          var target_term_id = $(this).data('term-id');
          var term_id = ui.draggable.data('term-id');

          $.ajax({
            type: "POST",
            url: wpajax.url,
            data: {action: 'fz_merge_parts', target_term_id: target_term_id, term_id: term_id},
          }); 
      }
  });

  $('.inline-editing-delete').editable({
    type: 'checklist',
    source: {'1': 'enabled'},
    emptytext: 'disabled'
  });

  $('.inline-editing').editable({
    type: 'text',
    pk: 1,
    url: wpajax.url,
    //params: { action: 'fz_inline-editing' }
    params: function(params){
      params.type = $(this).data('inline-edit-type');
      params.id = $(this).data('term-id');
      params.action = 'fz_inline_editing';
      return params;
    }
  });

  

  // click on add category button
  $(".add-category-button").live('click', function(){

    $category_dom = "<div class='category'><input type='text' class='input-small inline-edit-input inline-edit-category-input'></div>"

    $(this).parents('.bin-container').append($category_dom)
    .find('input').focus();

    $('#taxonomy-index').masonry('reload');

    return false;

  });

  //mansonry for categories etc.
  $('#taxonomy-index').masonry({
      itemSelector: '.bin-container'
  });

  

});
</script>