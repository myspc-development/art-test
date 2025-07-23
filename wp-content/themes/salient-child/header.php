<?php
// Load the parent theme header.
get_template_part('header');

// Output the primary navigation menu.
wp_nav_menu([
    'theme_location' => 'primary',
    'menu_class'     => 'main-menu'
]);
