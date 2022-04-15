<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.fedegomez.es/
 * @since      2.1.3
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.1.3
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/includes
 * @author     Fede Gómez <hola@fedegomez.es>
 */
class Link_Juice_Optimizer_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.1.3
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'link-juice-optimizer',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
