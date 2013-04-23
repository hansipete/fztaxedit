<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

 <?php
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      //get_template_part('templates/header-top-navbar');
    } else {
      //get_template_part('templates/header');
    }

    //include roots_template_path();
?>


<div class="container">
      <?php include roots_template_path(); ?>
</div>

  <?php wp_footer(); ?>

</body>
</html>
