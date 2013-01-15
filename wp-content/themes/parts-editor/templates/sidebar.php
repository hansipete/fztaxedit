<div id="bins-sidebar" style="position: fixed;">

	<ul id="fz_bins">
<?php 

	$parent_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent=0');
 
 	foreach ( $parent_terms as $term ){

 		echo "<li><h4>"  .$term->name . "</h4>";

 		$child_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent='.$term->term_id);
 		echo "<ul class=\"children\">";
 		foreach ( $child_terms as $term ){

 			$term_id = $term->term_id;

 			echo "<li class='cat-item cat-item-$term_id'><a href='#'>"  .$term->name . " (".$term->count.")</a></li>";

 		}
 		echo "</ul></li>";

 	}
?>
	</ul>

	<form id="new-tag-form" action="<?php echo admin_url('edit-tags.php'); ?>" method="post">
		<input type="hidden" name="action" value="add-tag">
		<input type="hidden" name="screen" value="edit-fz_taxonomy2">
		<input type="hidden" name="taxonomy" value="fz_taxonomy2">
		<input type="hidden" name="_wp_http_referer" value="'+window.location+'">
		<input type="hidden" name="post_type" value="fz_part">
		<input type="hidden" id="_wpnonce_add-tag" name="_wpnonce_add-tag" value="<?php echo wp_create_nonce('add-tag'); ?>">
		<input type="hidden" name="_wp_http_referer" value="'+window.location+'">

		<em class="info">New Subcategory</em>
		<p>
		<?php 

			$args = array(	'hide_empty' => 0, 
							'hide_if_empty' => false, 
							'parent' => 0,
							'name' => 'parent', 
							'orderby' => 'name', 
							'taxonomy' => 'fz_taxonomy2', 
							'hierarchical' => true
			);

			wp_dropdown_categories(	$args );

		?>
		<br>
		<input name="tag-name" id="tag-name" type="text" value="" size="40" aria-required="true">
		<input type="submit" id="submit" style="display:none;">
		</p>
	</form>

	<script>

		jQuery('#new-tag-form').live('submit', function(e){

			// do not standard submit 
			e.preventDefault();

			// serialize form data
			form_data = jQuery(this).serialize();
 
 			// ajax request
			jQuery.post("<?php echo admin_url('edit-tags.php'); ?>", form_data)
			.complete( function(data){
				//update list
				jQuery('#fz_bins').load(window.location + ' #fz_bins > *');

				//reset input field
				jQuery('input#tag-name').val('').focus();
			});
            

		});

	</script>


</div>