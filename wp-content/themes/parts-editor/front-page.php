      <!--Body content-->
      <h2>Taxonomy & Parts</h2>
      <div class='row' style="margin: 0">
<?php
      // BINS
    $bins = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => 0, 'hide_empty' => 0 ) );
    foreach ( $bins as $i => $bin) {
        echo "<div class='page-break'></div>";
        echo "<div class='row' data-term-id='{$bin->term_id}' id='bin-{$bin->term_id}'  style='border-color: {$bin->description};'>
                <h4>{$bin->name}<a href='+category' title='Add category' class='add-button add-category-button pull-right'><i class='icon-plus'></i></a></h4>";        

        // BINS > CATEGORIES
        $categories = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $bin->term_id, 'hide_empty' => 0 ) );
        foreach ($categories as $category) {
            echo "<div class='span2 category' data-term-id='{$category->term_id}' data-category-color='{$bin->description}'>
                  <h6><a href='#' class='inline-editing' data-original-title='New category name'>{$category->name}</a><a href='+part' title='Add part' class='add-button add-part-button pull-right'><i class='icon-plus'></i></a></h6>
                  <ul>";

                      // BINS > CATEGORIES > PART
                      $parts = get_categories( array( 'taxonomy' => 'fz_taxonomy_2013', 'parent' => $category->term_id, 'hide_empty' => 0 ) );
                      foreach ($parts as $part) {
                          
                          $term = get_term( $part->term_id, 'fz_taxonomy_2013' );
$count = $term->count;

                          echo "<li class='part' data-term-id='{$part->term_id}' style='background: {$bin->description};'>
                                 <i class='icon-pencil'></i>&nbsp;<a href='#' data-inline-edit-type='part' data-part-id='{$part->term_id}' class='name inline-editing' data-original-title='New part name'>{$part->name}</a>
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

  $('.inline-editing').editable({
    type: 'text',
    pk: 1,
    url: wpajax.url,
    //params: { action: 'fz_inline-editing' }
    params: function(params){
      params.type = $(this).data('inline-edit-type');
      params.id = $(this).data('part-id');
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

  $(result_container).find('tr td:not(:last-child)').click( function(e){
    $(this).parents('tr').addClass('selected info').siblings('tr').removeClass('selected info');
    return false;
  });

  $('.part-graphics-popover-link').live('hover', function(){
    var t = $(this);
    t.unbind('hover');
    $.get(t.data('content-url'), function(d) {
        t.popover({content: d});
    });  
  });  

});
</script>