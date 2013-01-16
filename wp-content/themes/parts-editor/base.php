<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

 <?php
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      get_template_part('templates/header-top-navbar');
    } else {
      get_template_part('templates/header');
    }
?>

<div class="container">
  <div class="row">
    <div class="span6">
      <?php include roots_template_path(); ?>
    </div>
    <?php if (roots_sidebar() && !is_page( 'show-properties' ) ) : ?>
      <aside id="sidebar" class="<?php roots_sidebar_class(); ?>" role="complementary">
          <?php get_template_part('templates/sidebar'); ?>
      </aside>
    <?php endif; ?>
  </div>
</div>

  <?php wp_footer(); ?>

</body>
</html>
