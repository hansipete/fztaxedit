<?php /* Template Name: Family Index */ ?>

<?php get_template_part('templates/page', 'header'); ?>

<table class="table table-condensed table-bordered">
	<caption>Lists all original family records, that were stored in the fzp files. (affects mostly core.fzp)</caption>
	<thead>
		<th>#</th>
		<th>Family</th>
		<th>Parts</th>
	</thead>
	<tbody>
<?php
	$families = get_terms( 'fz_original_family', $args = 'hide_empty=true' );
	$i = 0;

	foreach ($families as $family) {
		
		echo "<tr>
				<td>{$i}</td>
				<td>{$family->name}</td>
				<td>{$family->count}</td>
			  </tr>";

		$i++;
	}
?>
	</tbody>
</table>