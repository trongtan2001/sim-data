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

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('sim-custom-script', get_stylesheet_directory_uri() . '/script.js', array(), null, true);
});


function sim_toggle_menu_button_shortcode() {
    ob_start();
    ?>
    <button id="toggle-menu-btn" class="toggle-menu-btn" aria-label="Toggle Menu">
        <img id="toggle-menu-icon" 
             src="http://localhost/sim/wp-content/uploads/2025/10/icon-togle.svg" 
             alt="Menu Icon" 
             width="28" height="28" />
    </button>
    <?php
    return ob_get_clean();
}
add_shortcode('toggle_menu_button', 'sim_toggle_menu_button_shortcode');

function wc_login_button_shortcode() {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $avatar = get_avatar_url( $current_user->ID, array( 'size' => 64 ) );
        $myaccount_url = wc_get_page_permalink( 'myaccount' );

        ob_start();
        ?>
        <div class="wc-account-menu">
            <div class="account-trigger">
                <img class="account-avatar" src="<?php echo esc_url( $avatar ); ?>" alt="Avatar">
                <span class="account-name"><?php echo esc_html( $current_user->display_name ); ?></span>
            </div>
            <ul class="account-dropdown">
                <li><a href="<?php echo esc_url( $myaccount_url ); ?>">Tài khoản của tôi</a></li>
                <li><a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', $myaccount_url ) ); ?>">Đơn hàng</a></li>
                <li><a href="<?php echo esc_url( wc_logout_url( home_url() ) ); ?>">Đăng xuất</a></li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    } else {
        $myaccount_url = wc_get_page_permalink( 'myaccount' );
        return '<a class="wc-login-button" href="' . esc_url( $myaccount_url ) . '">Đăng nhập</a>';
    }
}
add_shortcode( 'wc_login_button', 'wc_login_button_shortcode' );

add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

require_once get_stylesheet_directory() . '/custom-post-support.php';
require_once get_stylesheet_directory() . '/custom-devices.php';
// require_once get_stylesheet_directory() . '/item-product.php';
require_once get_stylesheet_directory() . '/item-product-two.php';
require_once get_stylesheet_directory() . '/custom-field.php';
