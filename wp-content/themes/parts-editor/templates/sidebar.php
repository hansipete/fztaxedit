<div id="bins-sidebar" style="position: fixed;">
<h3>Bins</h3>

	<?php
			$args = array(
			  'taxonomy'     => 'fz_taxonomy',
			  'orderby'      => 'name',
			  'show_count'   => 0,
			  'pad_counts'   => 0,
			  'hierarchical' => 1,
			  'title_li'	 => '',
			  'hide_empty'	 => 0
			);
	?>
	<ul id="fz_bins">
		<?php wp_list_categories( $args ); ?>
	</ul>

	<form id="new-tag-form" action="<?php echo admin_url('edit-tags.php'); ?>" method="post">
		<input type="hidden" name="action" value="add-tag">
		<input type="hidden" name="screen" value="edit-fz_taxonomy">
		<input type="hidden" name="taxonomy" value="fz_taxonomy">
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
							'taxonomy' => 'fz_taxonomy', 
							'hierarchical' => true
			);

			wp_dropdown_categories(	$args );

		?>

		<input name="tag-name" type="text" value="" size="40" aria-required="true">
		<input type="submit" id="submit" style="display:none;">
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
				jQuery('#bins-sidebar').load(window.location + ' #bins-sidebar > *');
			});
            

		});

	</script>


</div>