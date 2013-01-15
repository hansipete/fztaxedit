<?php
/*
Template Name: List part properties
*/
?>

<?php get_template_part('templates/page', 'header'); ?>

<?php

$original_bin = "core";

$properties = array(array());

// loop through all -fzp files in /pdb/core
foreach( glob('/Users/Hansi/Sites/fz-parts/pdb/'.$original_bin.'/{*.fzp}', GLOB_BRACE) as $fzp_file) 
{ 
	// FOR EACH FILE…
	
	// …load xml
	$fzp = simplexml_load_file( $fzp_file );

	// store fzp data in variables for easy access
	$fzpProperties = $fzp->properties;

	// loop through properties, store post meta
	$fzpPropertiesArray = array();

	foreach ($fzpProperties->property as $property) {
		$name = (string) $property['name'];
		$value = (string) $property;

		// property key already in array 1. level - if not set it
		if( !isset($properties[$name]) )
			$properties[$name] = array();

		// value already in 2. level? if not - set it
		if( !in_array($value, $properties[$name]) )
			array_push($properties[$name], $value);

	}
}

//little bug – remove the empty 0 entry
unset($properties['0']);

// sort array that large itmes like family show up at bottom
array_multisort(array_map('count', $properties), SORT_ASC, $properties);
?>

<div class="alert alert-success">Imported data from /core .fzp files</div>

<div class="row">
<?php $i = 0; foreach( $properties as $name => $values) : ?>
	
	<div class="span3">
		<div class="well">
		<h4><?php echo $name; ?></h4>
        
    <?php
     	foreach ($values as $value) {
			echo "<span class=\"label label-info\">$value</span>  ";
		}
	?>
		</div>
	</div>

<?php $i++; endforeach; ?>
</div>
