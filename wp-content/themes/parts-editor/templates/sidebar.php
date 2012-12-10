<div style="position: fixed;">
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

	<script>
		
		// get every last child li
		jQuery('ul#fz_bins ul li:last-child').each( function() {

			// store element
			var li_last_child = jQuery(this);

			// navigate to te parent <li> and extract cat-id
			var parent_class 	= li_last_child.parent('ul.children').parent('li').attr('class');
			var parent_cat_id 	= parent_class.substr(18);

			// append a new li with a new-tag-form field
			var new_tag_form = li_last_child.append('<li><form action="<?php echo admin_url('edit-tags.php'); ?>" method="post"><input type="hidden" name="action" value="add-tag"><input type="hidden" name="screen" value="edit-fz_taxonomy"><input type="hidden" name="taxonomy" value="fz_taxonomy"><input type="hidden" name="_wp_http_referer" value="'+window.location+'"><input type="hidden" name="post_type" value="fz_part"><input type="hidden" id="_wpnonce_add-tag" name="_wpnonce_add-tag" value="<?php echo wp_create_nonce('add-tag'); ?>"><input type="hidden" name="_wp_http_referer" value="'+window.location+'"><input name="tag-name" type="text" value="" size="40" aria-required="true"><input type="hidden" name="parent" value="'+parent_cat_id+'"><input type="submit" style="display:none;"></form></li>')

			// wp will end up in the admin area after submission – do some ajax
			new_tag_form.find('form').submit( function(e){

				// do not standard submit 
				e.preventDefault();

				// serialize form data
				form_data = jQuery(this).serialize();
     
     			// ajax request
				jQuery.post("<?php echo admin_url('edit-tags.php'); ?>", form_data)
				.complete( function(data){
					jQuery('#fz_bins').load(window.location + ' #fz_bins > *')
				});
                

			});

		});

	</script>


</div>