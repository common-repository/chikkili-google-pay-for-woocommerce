<?php

/**
 * @link              https://sevengits.com/plugin/chikkili-google-pay-for-woocommerce-pro/
 * @since             1.0.0
 * @package           Chikkili
 *
 * @wordpress-plugin
 * Plugin Name:       Chikkili- Google Pay India for Woocommerce
 * Plugin URI:        https://wordpress.org/plugins/chikkili-google-pay-for-woocommerce/
 * Description:       This plugin help shop owners to accept payments through the Google Pay payment gateway.
 * Version:           1.0.6
 * Author:            Sevengits
 * Author URI:        http://sevengits.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chikkili
 * Domain Path:       /languages
 * WC requires at least: 4.0
 * WC tested up to: 	 8.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}


if (!function_exists('get_plugin_data')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if (!defined('CHIKKILI_VERSION'))
define('CHIKKILI_VERSION', get_plugin_data(__FILE__)['Version']);

if (!defined('SGCGP_BASE'))
define('SGCGP_BASE', plugin_basename( __FILE__ ));

if( ! defined( 'SGCGP_PLUGIN_PATH' ) ) 
	define( 'SGCGP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chikkili-activator.php
 */
function sgcgp_activate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-chikkili-activator.php';
	Chikkili_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chikkili-deactivator.php
 */
function sgcgp_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-chikkili-deactivator.php';
	Chikkili_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'sgcgp_activate');
register_deactivation_hook(__FILE__, 'sgcgp_deactivate');


require SGCGP_PLUGIN_PATH . 'plugin-deactivation-survey/deactivate-feedback-form.php';
add_filter('sgits_deactivate_feedback_form_plugins', 'sgcgp_deactivate_feedback');
function sgcgp_deactivate_feedback($plugins)
{
	$plugins[] = (object)array(
		'slug'		=> 'chikkili-google-pay-india-for-woocommerce',
		'version'	=> CHIKKILI_VERSION
	);
	return $plugins;
}


if (!class_exists('\SGCGP\Reviews\Notice')) {
	require plugin_dir_path(__FILE__) . 'includes/packages/plugin-review/notice.php';
}
function sgcgp_notice() {
	

	/**
	 * check dependency plugins are activated
	 */
	$depended_plugins = array(
		array(
			'plugins' => array(
				'WooCommerce' => 'woocommerce/woocommerce.php'
			), 'links' => array(
				'free' => 'https://wordpress.org/plugins/woocommerce/'
			)
		)

	);
	$message = __('The following plugins are required for <b>' . get_plugin_data(__FILE__)['Name'] . '</b> plugin to work. Please ensure that they are activated: ', 'chikkili-google-pay-for-woocommerce');
	$is_disabled = false;
	foreach ($depended_plugins as $key => $dependency) {
		$dep_plugin_name = array_keys($dependency['plugins']);
		$dep_plugin = array_values($dependency['plugins']);
		if (count($dep_plugin) > 1) {
			if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins'))) && !in_array($dep_plugin[1], apply_filters('active_plugins', get_option('active_plugins')))) {
				$class = 'notice notice-error is-dismissible';
				$is_disabled = true;
				if (isset($dependency['links'])) {
					if (array_key_exists('free', $dependency['links'])) {
						$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a>';
					}
					if (array_key_exists('pro', $dependency['links'])) {

						$message .= ' Or <a href="' . $dependency['links']['pro'] . '" target="_blank" ><b>' . $dep_plugin_name[1] . '</b></a>';
					}
				} else {
					$message .= "<br/> <b> $dep_plugin_name[0] </b> Or <b> $dep_plugin_name[1] . </b>";
				}
			}
		} else {
			if (!in_array($dep_plugin[0], apply_filters('active_plugins', get_option('active_plugins')))) {
				$class = 'notice notice-error is-dismissible';
				$is_disabled = true;
				if (isset($dependency['links'])) {
					$message .= '<br/> <a href="' . $dependency['links']['free'] . '" target="_blank" ><b>' . $dep_plugin_name[0] . '</b></a>';
				} else {
					$message .= "<br/><b>$dep_plugin_name[0]</b>";
				}
			}
		}
	}
	if ($is_disabled) {
		if (!defined('DLMP_DISABLED')) {
			define('DLMP_DISABLED', true);
		}
		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
	}

	/**
	 * review notice
	 */

	 // delete_site_option('prefix_reviews_time'); // FOR testing purpose only. this helps to show message always
	$message = sprintf(__("Hello! Seems like you have been using %s for a while – that’s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress? This would boost our motivation and help us spread the word.", 'chikkili-google-pay-for-woocommerce'), "<b>" . get_plugin_data(__FILE__)['Name'] . "</b>");
	$actions = array(
		'review'  => __('Ok, you deserve it', 'chikkili-google-pay-for-woocommerce'),
		'later'   => __('Nope, maybe later I', 'chikkili-google-pay-for-woocommerce'),
		'dismiss' => __('already did', 'chikkili-google-pay-for-woocommerce'),
	);
	$notice = \SGCGP\Reviews\Notice::get(
		'chikkili-google-pay-for-woocommerce',
		get_plugin_data(__FILE__)['Name'],
		array(
			'days'          => 7,
			'message'       => $message,
			'action_labels' => $actions,
			'prefix' => "sgcgp"
		)
	);

	// Render notice.
	$notice->render();
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-chikkili.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function sgcgp_run()
{

	$plugin = new Chikkili();
	$plugin->run();
}
/**
 * make sure that woocommerce is running 
 */
$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if (in_array('woocommerce/woocommerce.php', $active_plugins)) {
	sgcgp_run();
}
