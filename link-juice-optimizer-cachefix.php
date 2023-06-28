<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/andrewnimmo
 * @since             2.3.1-cachefix
 * @package           Link_Juice_Optimizer_Cachefix
 *
 * @wordpress-plugin
 * Plugin Name:       Link Juice Optimizer Cachefix
 * Plugin URI:        https://github.com/andrewnimmo/link-juice-optimizer-cachefix/tree/andrewnimmo/fix-caching-with-hook-change
 * Description:       Sustituye los enlaces por una etiqueta &lt;span&gt; clicable, añade el atributo nofollow o elimina el atributo href para optimizar el link juice.
 * Version:           2.3.1-cachefix
 * Author:            Fede Gómez, Andrew David Nimmo
 * Author URI:        https://github.com/andrewnimmo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       link-juice-optimizer-cachefix
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 2.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LINK_JUICE_OPTIMIZER_CACHEFIX_VERSION', '2.3.1-cachefix');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-link-juice-optimizer-activator.php
 */
function activate_link_juice_optimizer_cachefix()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-link-juice-optimizer-activator.php';
    Link_Juice_Optimizer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-link-juice-optimizer-deactivator.php
 */
function deactivate_link_juice_optimizer_cachefix()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-link-juice-optimizer-deactivator.php';
    Link_Juice_Optimizer_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_link_juice_optimizer_cachefix');
register_deactivation_hook(__FILE__, 'deactivate_link_juice_optimizer_cachefix');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-link-juice-optimizer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.1
 */
function run_link_juice_optimizer()
{
    $plugin = new Link_Juice_Optimizer();
    $plugin->run();
}

require_once 'vendor/autoload.php';

run_link_juice_optimizer();
