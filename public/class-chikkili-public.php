<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://sevengits.com/
 * @since      1.0.0
 *
 * @package    Chikkili
 * @subpackage Chikkili/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Chikkili
 * @subpackage Chikkili/public
 * @author     Sevengits <support@sevengits.com>
 */
class Chikkili_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	/**
	 * @since 1.0.0
	 */

	public function sg_gpay_checkout_icon(){
		return 'https://pay.google.com//about/business/static/images/logos/google-pay-logo.svg';

	}
	/**
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function custom_thankyou_text(){
		echo '<button class="gpay-button">Pay now</button>';
		echo '<button class="cancel-button">Cancel</button>';
	}
	/**
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function sg_footer_script(){
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/chikkili-public-display.php';
		
		
	}


}
