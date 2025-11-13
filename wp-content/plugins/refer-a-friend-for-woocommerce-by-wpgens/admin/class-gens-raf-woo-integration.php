<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Gens_RAF
 * @subpackage Gens_RAF/admin
 * @author     Your Name <email@example.com>
 */
class WPGens_Settings_RAF extends WC_Settings_Page {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $gens_raf    The ID of this plugin.
	 */
	private $gens_raf;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $gens_raf       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct() {

		$this->id    = 'gens_raf';
		$this->label = __( 'Refer A Friend', 'gens-raf');

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''         => __( 'General', 'gens-raf' ),
			'emails' => __( 'Email', 'gens-raf' ),
			'howto' => __( 'Help', 'gens-raf' ),
			'premium' => __( '💎 Get Premium', 'gens-raf' ),
			'plugins' => __( 'Recommended Plugins', 'gens-raf' )
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @param string $current_section Optional. Defaults to empty string.
	 * @return array Array of settings
	 */
	public function get_settings( $current_section = '' ) {
		$prefix = 'gens_raf_';
		switch ($current_section) {
			case 'emails':
				$settings = array(
					array(
						'name' => __( 'Email Settings', 'gens-raf' ),
						'type' => 'title',
						'desc' => __( 'Setup the look of email that will be sent to the referal together with coupon.', 'gens-raf'),
						'id'   => 'email_options',
					),
					array(
						'id'			=> $prefix.'email_subject',
						'name' 			=> __( 'Email Subject', 'gens-raf' ),
						'type' 			=> 'text',
						'desc_tip'		=> __( 'Enter the subject of email that will be sent when notifiying the user of their coupon code.', 'gens-raf'),
						'default' 		=> 'Hey there!'
					),
					array(
						'id'			=> $prefix.'email_message',
						'name' 			=> __( 'Email Message', 'gens-raf' ),
						'type' 			=> 'textarea',
						'class'         => 'input-text wide-input',
						'desc'			=> __( 'Text that will appear in email that is sent to user once they get the code. Use {{code}} to add coupon code.HTML allowed.', 'gens-raf'),
						'default' 		=> 'You referred someone! Here is your coupon code reward: {{code}} .'
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'email_options',
					),
				);
				break;
			case 'howto':
				$settings = array(
					array(
						'name' => __( 'Quick Setup Guide - Get Started in 3 Easy Steps', 'gens-raf' ),
						'type' => 'title',
						'desc' => sprintf( __( 'Thanks for using the Refer a Friend plugin! You can purchase the premium version and support us on <a href="%s" target="_blank">this page</a><br/>
							<h3>📋 SETUP GUIDE</h3>
							<ol>
								<li><strong>Configure General Settings:</strong> After installing the plugin, go to the Refer a Friend settings (this page) and click on the General tab. Set up your coupon options including discount type, amount, and expiration settings.</li>
								<li><strong>Customize Email Templates:</strong> Click on the Email tab and customize the message that will be sent to users when they receive their coupon code.</li>
								<li><strong>You\'re Done!</strong> Every user will now have a referral link in their account page. When someone makes a purchase through their referral link and the order is marked as complete, the referrer will receive a coupon in their inbox. For orders made through referrals, you can see the referrer\'s name in the order details screen, just below the customer information. <a href="%s" target="_blank">View example screenshot.</a></li>
							</ol>
							<br/>
							<strong>💡 Pro Tip:</strong> Make sure to test the referral flow with a test order to ensure everything works correctly before going live!
							', 'gens-raf' ), 'https://wpgens.com/downloads/refer-a-friend-for-woocommerce-premium/?utm_source=raf-free','https://wpgens.com/slike/referral2.png'),
						'id'   => 'plugin_options',
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'Help', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'plugin_options',
					),
				);
				break;
			case 'plugins':
				$settings = array(
					array(
						'name' => __( 'Recommended WP Gens Plugins', 'gens-raf' ),
						'type' => 'title',
						'desc' => __( 'Supercharge your WooCommerce store with these powerful plugins from WP Gens. Each plugin is carefully crafted to enhance your store\'s functionality and boost your sales.
						<br/><br/>
						<div style="margin: 20px 0;">
							
							<div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9; margin-bottom: 20px;">
								<h3 style="margin-top: 0; color: #2271b1;">💎 Refer a Friend Premium</h3>
								<p style="font-size: 14px; color: #666; margin-bottom: 15px;">Take your referral program to the next level with advanced features, detailed analytics, and powerful customization options.</p>
								<a href="https://wpgens.com/downloads/refer-a-friend-for-woocommerce-premium/?utm_source=raf-free" target="_blank" class="button-primary" style="text-decoration: none; padding: 8px 16px; border-radius: 4px; display: inline-block;">Get Premium Features</a>
							</div>
							
							<div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9; margin-bottom: 20px;">
								<h3 style="margin-top: 0; color: #2271b1;">🎯 Points & Rewards for WooCommerce</h3>
								<p style="font-size: 14px; color: #666; margin-bottom: 15px;">Create a comprehensive loyalty program that rewards customers with points for purchases, reviews, and social sharing.</p>
								<a href="https://wpgens.com/downloads/points-and-rewards-for-woocommerce/?utm_source=raf-free" target="_blank" class="button-primary" style="text-decoration: none; padding: 8px 16px; border-radius: 4px; display: inline-block;">Get Points & Rewards</a>
							</div>
							
							<div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9; margin-bottom: 20px;">
								<h3 style="margin-top: 0; color: #2271b1;">📊 UTM Tracking for WooCommerce</h3>
								<p style="font-size: 14px; color: #666; margin-bottom: 15px;">Track your marketing campaigns with precision and see exactly which channels drive the most sales to your store.</p>
								<a href="https://wpgens.com/downloads/woocommerce-utm-tracking/?utm_source=raf-free" target="_blank" class="button-primary" style="text-decoration: none; padding: 8px 16px; border-radius: 4px; display: inline-block;">Get UTM Tracking</a>
							</div>
							
						</div>
						
						<br/>
						<p style="text-align: center; padding: 20px; background: #e7f3ff; border-radius: 8px; margin: 20px 0;">
							<strong>💡 Pro Tip:</strong> All our plugins work seamlessly together to create a powerful ecommerce ecosystem. 
							<br/>Questions? <a href="mailto:goran@wpgens.com">Contact us</a> - we\'re here to help!
						</p>
						', 'gens-raf' ),
						'id'   => 'plugin_options',
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'Recommended Plugins', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'plugin_options',
					),
				);
				break;
			case 'premium':
				$settings = array(
					array(
						'name' => __( '💎 Upgrade to Premium - Supercharge Your Referral Program', 'gens-raf' ),
						'type' => 'title',
						'desc' => __( 'Take your referral program to the next level with powerful premium features that help you grow your business faster.
						<br/><br/>
						<div style="margin: 20px 0;">
							<div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9; margin-bottom: 20px;">
								<h3 style="margin-top: 0; color: #2271b1;">🚀 Enhanced Integration & Display Options:</h3>
								<ul style="margin-bottom: 15px;">
									<li>✅ <strong>Flexible Shortcodes</strong> - Simple and advanced shortcodes for any page or post</li>
									<li>✅ <strong>Product-Level Referral Tabs</strong> - Add referral sharing directly to product pages with social icons</li>
									<li>✅ <strong>Contact Form 7 Integration</strong> - Seamlessly integrate with your contact forms</li>
								</ul>
								
								<h3 style="color: #2271b1;">🎯 Advanced Targeting & Control:</h3>
								<ul style="margin-bottom: 15px;">
									<li>✅ <strong>Minimum Purchase Requirements</strong> - Set minimum order amounts for coupon generation</li>
									<li>✅ <strong>Product-Specific Coupons</strong> - Enable referral rewards for specific products only</li>
									<li>✅ <strong>Coupon Expiry Dates</strong> - Set automatic expiration dates for better control</li>
									<li>✅ <strong>Dual Rewards System</strong> - Reward both referrer and referred customer</li>
								</ul>
								
								<h3 style="color: #2271b1;">📊 Comprehensive Analytics & Tracking:</h3>
								<ul style="margin-bottom: 15px;">
									<li>✅ <strong>Individual User Statistics</strong> - Track referral performance for each customer</li>
									<li>✅ <strong>Admin Dashboard Analytics</strong> - Complete referral statistics and reporting</li>
									<li>✅ <strong>Order Integration</strong> - See referral information directly in order details</li>
								</ul>
								
								<h3 style="color: #2271b1;">🛡️ Premium Support & Updates:</h3>
								<ul style="margin-bottom: 20px;">
									<li>✅ <strong>One Year of Premium Support</strong> - Get help when you need it</li>
									<li>✅ <strong>Free Updates</strong> - Stay current with new features and improvements</li>
								</ul>
								
								<a href="https://wpgens.com/downloads/refer-a-friend-for-woocommerce-premium/?utm_source=raf-free" target="_blank" class="button-primary" style="text-decoration: none; padding: 8px 16px; border-radius: 4px; display: inline-block;">Get Premium Now</a>
							</div>
						</div>
						', 'gens-raf' ),
						'id'   => 'plugin_options',
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'Premium', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'plugin_options',
					),
				);
				break;

			default:
				$settings = array(
					array(
						'name' => __( 'General', 'gens-raf' ),
						'type' => 'title',
						'desc' => __( 'General Options, setup plugin here first.', 'gens-raf' ),
						'id'   => 'general_options',
					),
					array(
						'id'			=> $prefix.'disable',
						'name' 			=> __( 'Disable', 'gens-raf' ),
						'type' 			=> 'checkbox',
						'label' 		=> __( 'Disable Coupons', 'gens-raf' ), // checkbox only
						'desc'			=> __( 'Check to disable. Referal links wont work anymore.', 'gens-raf'),
						'default' 		=> 'no'
					),
					array(
						'id'		=> $prefix.'cookie_time',
						'name' 		=> __( 'Cookie Time', 'gens-raf' ),
						'type' 		=> 'number',
						'desc_tip'	=> __( 'As long as cookie is saved, user will recieve coupon after referal purchase product.', 'gens-raf'),
						'desc' 		=> __( 'How long to keep cookies before it expires.(In days)', 'gens-raf')
					),
					array(
						'id'		=> $prefix.'cookie_remove',
						'name' 		=> __( 'Single Purchase', 'gens-raf' ),
						'label' 		=> __( 'Single Purchase', 'gens-raf' ), // checkbox only
						'type' 			=> 'checkbox',
						'desc_tip'	=> __( 'This means that coupon is sent only the first time referral makes a purchase, as referral cookie is deleted after it.', 'gens-raf'),
						'desc' 		=> __( 'If checked, cookie will be deleted after customer makes a purchase.', 'gens-raf'),
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'general_options',
					),
					array(
						'name' => __( 'Coupon Settings', 'gens-raf' ),
						'type' => 'title',
						'desc' => __( 'General Options, setup plugin here first.', 'gens-raf'),
						'id'   => 'coupon_options',
					),
					array(
						'id'			=> $prefix.'coupon_type',
						'name' 			=> __( 'Coupon Type', 'gens-raf' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 			=> 'select',
						'class'    => 'wc-enhanced-select',
						'options'		=> array(
							'fixed_cart'	=> 'Cart Discount',
							'percent'	=> 'Cart % Discount',
//							'fixed_product'	=> 'Product Discount',
//							'percent_product'	=> 'Product % Discount'
						)
					),
					array(
						'id'		=> $prefix.'coupon_amount',
						'name' 		=> __( 'Coupon Amount', 'gens-raf' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'number',
						'desc_tip'	=> __( ' Entered without the currency unit or a percent sign as these will be added automatically, e.g., ’10’ for 10£ or 10%.', 'gens-raf'),
						'desc' 		=> __( 'Fixed value or percentage off depending on the discount type you choose.', 'gens-raf' )
					),
					/*
					array(
						'id'		=> $prefix.'coupon_duration',
						'name' 		=> __( 'Coupon Duration', 'gens-raf' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'text',
						'class'		=> 'date-picker hasDatepicker',
						'desc' 		=> 'Value is number of days beginning on the coupon creation date.'
					),
					*/
					array(
						'id'		=> $prefix.'min_order',
						'name' 		=> __( 'Minimum Order', 'gens-raf' ), // Type: fixed_cart, percent, fixed_product, percent_product
						'type' 		=> 'number',
						'desc' 		=> __( 'Define minimum order subtotal in order for coupon to work.', 'gens-raf' )
					),
					array(
						'id'		=> $prefix.'individual_use',
						'name' 		=> __( 'Individual Use', 'gens-raf' ),
						'type' 		=> 'checkbox',
						'desc' 	=> __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'gens-raf' ), // checkbox only
						'default' 	=> 'no'
					),
					array(
						'id'		=> '',
						'name' 		=> __( 'General', 'gens-raf' ),
						'type' 		=> 'sectionend',
						'desc' 		=> '',
						'id'   		=> 'coupon_options',
					),
				);
				break;
		}

		/**
		 * Filter Memberships Settings
		 *
		 * @since 1.0.0
		 * @param array $settings Array of the plugin settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

	}

	/**
	 * Output the settings
	 *
	 * @since 1.0
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
		
		// Hide save button on informational tabs
		if ( in_array( $current_section, array( 'howto', 'premium', 'plugins' ) ) ) {
			echo '<style type="text/css">.woocommerce-save-button { display: none !important; }</style>';
		}
	}


	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}

}

return new WPGens_Settings_RAF();
