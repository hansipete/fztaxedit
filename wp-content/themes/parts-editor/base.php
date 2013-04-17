<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

 <?php
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      //get_template_part('templates/header-top-navbar');
    } else {
      get_template_part('templates/header');
    }
?>

<!-- <div class="container">
  <div class="row">
    <?php //sinclude roots_template_path(); ?>
  </div>
</div> -->

<div class="container">
  <div class="row">
    <div class="<?php roots_main_class(); ?>">
      <div class="row">
        <?php include roots_template_path(); ?>
      </div>
    </div>
      
      <aside id="sidebar" class="<?php roots_sidebar_class(); ?>" role="complementary">
          <?php get_template_part('templates/sidebar', 'apply'); ?>
      </aside>
  </div>
</div>

  <?php wp_footer(); ?>

</body>
</html>
