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

  <div id="wrap" class="container" role="document">
    <div id="content" class="row">

      <div id="main" class="<?php roots_main_class(); ?>" role="main">
        <?php include roots_template_path(); ?>
      </div>
      <?php if (roots_sidebar() && !is_page( 'show-properties' ) ) : ?>
      <aside id="sidebar" class="<?php roots_sidebar_class(); ?>" role="complementary">
        <?php get_template_part('templates/sidebar'); ?>
      </aside>
      <?php endif; ?>
    </div><!-- /#content -->
  </div><!-- /#wrap -->

  <?php wp_footer(); ?>

</body>
</html>
