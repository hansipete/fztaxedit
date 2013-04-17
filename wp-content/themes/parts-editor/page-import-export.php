<?php
	/* Template Name: Import / Export */ 
?>

<h3><?php the_title(); ?></h3>

<form id="import-form" class="form-horizontal" method="post" action="<?php bloginfo('wpurl'); ?>/import">
  <fieldset>
    <legend>Import .fzp files into database</legend>
  <div class="control-group">
    <label class="control-label" for="inputPath">Path</label>
    <div class="controls">
      <input name="path" type="text" id="inputPath" placeholder="input/pdb/">
          <span class="help-block">Relative path. Terminate with slash/</span>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <input type="submit" class="btn" value="Import">
    </div>
  </div>
</fieldset>
</form>

<script>
	var form;

	$('#import-form').submit(function(e){
		//store form data
		form = this;

		$('#import-modal').modal('show');

		return false;		
	});

	$('#import-continue').click(function(){
		console.log("hell");
		$('#import-modal').modal('hide');
		//form.submit();
	})
</script>


<div id="import-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  
  <div class="modal-body">
    <p>Import files from folder. This may take some time…</p>
  </div>
  
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Abort</button>
    <button id="import-continue" class="btn btn-primary">Continue</button>
  </div>
</div>