<div id="bins-sidebar" data-spy="affix" data-offset-top="0">
	<h3>Taxonomy</h3>

<div id="bins" class="row">
<?php 

	$parent_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent=0');
 
	global $colors;

 	$i = 0;
 	foreach ( $parent_terms as $term ){

 		echo "<div class='span2 bin bin-{$term->term_id}'>
 				<h4 style='color: {$colors[$i++]};'>{$term->name}</h4>

 				<table>";

 		$child_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent='.$term->term_id);
 		foreach ( $child_terms as $term ){

 			$term_id = $term->term_id;

 			echo "<tr>
 					<td><a href='#' class='group group-{$term->term_id}' style='color: #555;'>"  .$term->name . " (".$term->count.")</a></td>
 				  </tr>";

 		}
 		echo "	</table>
 			  </div>";

 	}
?>
</div>

<script>
	jQuery('#groups').masonry({
  		itemSelector: '.group'
	});
</script>

<div class="row">
	<div class="span6">
		<h4>Add Group to Bin</h4>

		<form id="new-tag-form" action="<?php echo admin_url('edit-tags.php'); ?>" method="post">
			<input type="hidden" name="action" value="add-tag">
			<input type="hidden" name="screen" value="edit-fz_taxonomy2">
			<input type="hidden" name="taxonomy" value="fz_taxonomy2">
			<input type="hidden" name="_wp_http_referer" value="'+window.location+'">
			<input type="hidden" name="post_type" value="fz_part">
			<input type="hidden" id="_wpnonce_add-tag" name="_wpnonce_add-tag" value="<?php echo wp_create_nonce('add-tag'); ?>">
			<input type="hidden" name="_wp_http_referer" value="'+window.location+'">

			<?php 

				$args = array(	'hide_empty' => 0, 
								'hide_if_empty' => false, 
								'parent' => 0,
								'name' => 'parent', 
								'orderby' => 'name', 
								'taxonomy' => 'fz_taxonomy2', 
								'hierarchical' => true,
								'class' => 'span2'
				);

				wp_dropdown_categories(	$args );

			?>
			<input class="span2" name="tag-name" id="tag-name" type="text" value="" size="40" aria-required="true">
			<input type="submit" id="submit" style="display:none;">
		</form>
	</div>
</div>	
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
				jQuery('#groups').load(window.location + ' #groups > *');

				//reset input field
				jQuery('input#tag-name').val('').focus();
			});
            

		});

	</script>


</div>