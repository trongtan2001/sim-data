<?php
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'hello-elementor-parent-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'sim-data-theme-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['hello-elementor-parent-style']
    );
});
