<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Chikkili
 * @subpackage Chikkili/includes
 * @author     Sevengits <support@sevengits.com>
 */
class Chikkili {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Chikkili_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CHIKKILI_VERSION' ) ) {
			$this->version = CHIKKILI_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'chikkili-google-pay-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Chikkili_Loader. Orchestrates the hooks of the plugin.
	 * - Chikkili_i18n. Defines internationalization functionality.
	 * - Chikkili_Admin. Defines all hooks for the admin area.
	 * - Chikkili_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chikkili-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chikkili-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chikkili-admin.php';
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-chikkili-public.php';

		$this->loader = new Chikkili_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Chikkili_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Chikkili_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Chikkili_Admin( $this->get_plugin_name(), $this->get_version() );
		
		# for enqueue js and css files
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// woo payment gateway
		$this->loader->add_action( 'plugins_loaded', $plugin_admin,'sgpay_gateway_init', 11 );
		$this->loader->add_filter( 'woocommerce_payment_gateways',$plugin_admin, 'sgpay_wc_add_to_gateways' );
		$this->loader->add_filter('woocommerce_available_payment_gateways', $plugin_admin,'conditional_payment_gateways', 10, 1);

		# below the plugin title in plugins page. add custom links at the begin of list
		$this->loader->add_filter('plugin_action_links_' . SGCGP_BASE, $plugin_admin, 'sgcgp_links_below_title_begin');

		# below the plugin title in plugins page. add custom links at the end of list
		$this->loader->add_filter('plugin_action_links_' . SGCGP_BASE, $plugin_admin, 'sgcgp_links_below_title_end');
		
		# below the plugin description in plugins page. add custom links at the end of list
		$this->loader->add_filter('plugin_row_meta', $plugin_admin, 'sgcgp_plugin_description_below_end', 10, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Chikkili_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'woocommerce_sgpay_icon', $plugin_public, 'sg_gpay_checkout_icon' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'sg_footer_script' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Chikkili_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
