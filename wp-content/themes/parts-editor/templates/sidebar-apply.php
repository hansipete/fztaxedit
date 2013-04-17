<div id="bins-sidebar" data-spy="affix" data-offset-top="0">

	<h4>Dock <button class="btn btn-mini pull-right" type="button">Clear dock</button></h4>
	<div class='dock'>
		<ul class="fzp-list fzp-list-dock unstyled">
			<!-- blank by default -->
		</ul>
	</div>

<form class="form-horizontal" name='new-part-from-dock'>
  <div class="control-group">
    <label class="control-label" for="category-dropdown">Category</label>
    <div class="controls">
      <?php wp_dropdown_categories('class=input-medium&taxonomy=fz_taxonomy_2013&hierarchical=1&depth=2&orderby=name&hide_empty=0'); ?>
    </div>
    <script>
    	//$("option.level-0").attr('disabled', true);
    </script>
  </div>
  <div class="control-group">
    <label class="control-label" for="new-category-title">New Category</label>
    <div class="controls">
      <input type="text" name='new-category-title' id="new-category-title" placeholder="Category">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="new-part-title">Part title</label>
    <div class="controls">
      <input type="text" name='new-part-title' id="new-part-title" placeholder="Title" required>
    </div>
  </div>
  <div class="control-group">
    <div class="controls">
      <button type="submit" class="btn btn-primary">Add</button>
    </div>
  </div>
</form>

   <h4>Uncategorized</h4>
    <div style="background: #f0f0f0;">
<?php

    $all_term_ids = get_terms('fz_taxonomy_2013', array( 'fields' => 'ids' ));

    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';

    $args = array(
                    'post_type' => 'fz_fzp',
                    's' => $filter,
                    'posts_per_page' => 200,
                    'tax_query' => array(
                                            array(
                                                'taxonomy' => 'fz_taxonomy_2013',
                                                'field' => 'id',
                                                'terms' => $all_term_ids,
                                                'operator' => 'NOT IN'
                                            )
                    )
            );

    $uncategorized = new WP_Query( $args );

    echo "<form id='uncategorized-filter' class='form-search'>
  			<input type='text' name='filter' class='input-medium'>
  			<button type='submit' class='btn'>Filter</button>
		  </form>
    	  
    	  <small>Count: {$uncategorized->found_posts}</small>
    	  
    	  <ul class='fzp-list fzp-list-uncategorized unstyled' style='height:400px; overflow-y: scroll;'>";
            while ( $uncategorized->have_posts() ) : $uncategorized->the_post();
                echo "<li>
                        <a href='{$post->guid}' data-fzp-id='{$post->ID}' data-toggle='tooltip' data-placement='top' data-original-title='".htmlentities($post->post_content)."'>{$post->post_title}</a>
                        <a href='#to-dock' class='pull-right'>to dock</a>
                        </li>";
            endwhile;
    echo "</ul>";
?>

    </div>

<script>

  /*
    click "to dock" instead of drag and drop:
  */
  $(".fzp-list a[href='#to-dock']").on('click', function(){
    $li = $(this).parents('li')
    $('.fzp-list-dock').append($li);
    return false;
  });


	/*
		if dock-ul has elements && category-selected.class=level-1 ajax the shit out of it
	*/

	$new_part_from_dock_form = $("form[name='new-part-from-dock']");

	$("form[name='new-part-from-dock']").on('submit', function(e){
		
		var $fzp_as = $('ul.fzp-list-dock li a');
		var $cat 	= $("select#cat option:selected");
		var fzp_ids = [];

		if( ($cat.attr('class') == 'level-1' || ($cat.attr('class') == 'level-0' && $('input#new-category-title').val())) && $fzp_as.length > 0){

			// get all the fzp ids from ul>li>a[data-fzp-id]
			$fzp_as.each(function(){
				fzp_ids.push( $(this).data('fzp-id') );
			})

			// serialize form data + add fzp ids array
			var data = 'action=fz_new_part&' + $(this).serialize() + '&' + $.param({ fzp_ids: fzp_ids });

			$.ajax({ url: wpajax.url, type: 'POST', data: data,

                    success: function(data) {
                    	// clear everything
                    	$fzp_as.remove();   
						$('input#new-part-title').val('');
						$('input#new-category-title').val('');

						//reload the bin
						var bin_id = ($cat.attr('class') == 'level-0') ? $cat.attr('value') : $cat.prev('.level-0').attr('value');

						var bin_div_selector = ".bin[data-term-id='"+bin_id+"']";

						$(bin_div_selector)
							.addClass('loading')
							.load(window.location + ' ' + bin_div_selector + ' > *', function(){ $(this).removeClass('loading'); });
                    }

            }); 

		}
		
		// do not submit to browser window
		e.preventDefault();
	});

</script>
	
</div>