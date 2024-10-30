<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Chikkili
 * @subpackage Chikkili/admin
 * @author     Sevengits <support@sevengits.com>
 */
class Chikkili_Admin
{

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chikkili_Admin as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chikkili_Admin will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/chikkili-admin.css', array(), $this->version, 'all');

		if (!wp_style_is('sgits-admin-settings-sidebar-css', 'enqueued'))
			wp_enqueue_style('sgits-admin-settings-sidebar', plugin_dir_url(__FILE__) . 'css/settings-sidebar.css', array(), $this->version, 'all');

		if (!wp_style_is('sgits-admin-common-css', 'enqueued'))
			wp_enqueue_style('sgits-admin-common', plugin_dir_url(__FILE__) . 'css/common.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chikkili_Admin as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chikkili_Admin will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/sg-order-approval-woocommerce-pro-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 *  @since 1.0.0  
	 * 
	 */
	function sgpay_gateway_init()
	{
		/**
		 * The class responsible for google pay payment gateway.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-chikkili-payment-gateway.php';
	}
	/**
	 * @since 1.0.0  
	 * Add the gateway to WC Available Gateways
	 */

	function sgpay_wc_add_to_gateways($gateways)
	{

		$gateways[] = 'Sgpay_Gateway';
		return $gateways;
	}
	/**
	 * Enable google pay if visitor is using chrome 60+ and thorugh mobile
	 *
	 * @param [array] $available_gateways
	 * @return [array]
	 * @since 1.0.0
	 */
	function conditional_payment_gateways($available_gateways)
	{
		// Not in backend (admin)
		if (is_admin()) {
			return $available_gateways;
		}
		//return $available_gateways;

		$user_browser = $this->getBrowser();


		// check user is using chrome or not
		if ($user_browser['browser'] !== "Chrome" || $user_browser['version'] < 60 || !wp_is_mobile()) {

			unset($available_gateways['sg_gpay']); // unset google pay
			return $available_gateways;
		} else {
			return $available_gateways;
		}
	}
	public function getBrowser()
	{
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$version = '';
		$ub = 'Unknown';


		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/Chrome/i', $u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}

		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
			')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} else {
			$version = $matches['version'][0];
		}

		$user_browser_version = explode(".", $version);


		$bwsr = array(
			'name'      => $bname,
			'version'   => intval($user_browser_version[0]) ?? 0,
			'browser'      => $ub,
		);

		return $bwsr;
	}

	/**
	 * 
	 * For array of data convert array of links and merge with exists array of links
	 * 
	 * $position = "start | end" 
	 */
	public function sgcgp_merge_links($old_list, $new_list, $position = "end")
	{
		$settings = array();
		foreach ($new_list as $name => $item) {
			$target = (array_key_exists("target", $item)) ? $item['target'] : '';
			$classList = (array_key_exists("classList", $item)) ? $item['classList'] : '';
			$settings[$name] = sprintf('<a href="%s" target="' . $target . '" class="' . $classList . '">%s</a>', esc_url($item['link'], $this->plugin_name), esc_html__($item['name'], $this->plugin_name));
		}
		if ($position !== "start") {
			// push into $links array at the end
			return array_merge($old_list, $settings);
		} else {
			return array_merge($settings, $old_list);
		}
	}

	public function sgcgp_links_below_title_begin($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the begin of list

		$new_links = array(
			// 	'settings' => array(
			// 		"name" => 'Settings',
			// 		"classList" => "",
			// 		"link" => admin_url('admin.php?page=wc-settings&tab=advanced&section=')
			// 	)
		);
		if (count($new_links) > 0) {
			$links = $this->sgcgp_merge_links($links, $new_links, "start");
		}

		return $links;
	}

	public function sgcgp_links_below_title_end($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the end of list
		$new_links = array(
			'buy-pro' => array(
				"name" => 'Buy Premium',
				"classList" => "pro-purchase get-pro-link",
				"target" => '_blank',
				"link" => 'https://sevengits.com/plugin/google-pay-for-woocommerce/?utm_source=Wordpress&utm_medium=plugins-link&utm_campaign=Free-plugin'
			)
		);
		# This function is defined in pre-defined-functions/merge-arrays.php

		$links = $this->sgcgp_merge_links($links, $new_links, "end");

		return $links;
	}

	public function sgcgp_plugin_description_below_end($links, $file)
	{
		if (strpos($file, 'chikkili.php') !== false) {
			$new_links = array(
				'pro' => array(
					"name" => 'Buy Premium',
					"classList" => "pro-purchase get-pro-link",
					"target" => '_blank',
					"link" => 'https://sevengits.com/plugin/google-pay-for-woocommerce/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin'
				),
				'docs' => array(
					"name" => 'Docs',
					"target" => '_blank',
					"link" => 'https://sevengits.com/docs/google-pay-for-woocommerce/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin'
				),
				'support' => array(
					"name" => 'Free Support',
					"target" => '_blank',
					"link" => 'https://wordpress.org/support/plugin/chikkili-google-pay-for-woocommerce/'
				),

			);
			$links = $this->sgcgp_merge_links($links, $new_links, "end");
		}

		return $links;
	}
}
