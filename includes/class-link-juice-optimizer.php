<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.fedegomez.es/
 * @since      2.0.1
 *
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.1
 * @package    Link_Juice_Optimizer
 * @subpackage Link_Juice_Optimizer/includes
 * @author     Fede Gómez <hola@fedegomez.es>
 */
class Link_Juice_Optimizer
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    2.0.1
     * @access   protected
     * @var      Link_Juice_Optimizer_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    2.0.1
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    2.0.1
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
     * @since    2.0.1
     */
    public function __construct()
    {
        if (defined('LINK_JUICE_OPTIMIZER_VERSION')) {
            $this->version = LINK_JUICE_OPTIMIZER_VERSION;
        } else {
            $this->version = '2.3';
        }
        $this->plugin_name = 'link-juice-optimizer';

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
     * - Link_Juice_Optimizer_Loader. Orchestrates the hooks of the plugin.
     * - Link_Juice_Optimizer_i18n. Defines internationalization functionality.
     * - Link_Juice_Optimizer_Admin. Defines all hooks for the admin area.
     * - Link_Juice_Optimizer_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    2.0.1
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-link-juice-optimizer-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-link-juice-optimizer-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-link-juice-optimizer-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-link-juice-optimizer-public.php';

        $this->loader = new Link_Juice_Optimizer_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Link_Juice_Optimizer_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    2.1.3
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Link_Juice_Optimizer_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    2.0.1
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Link_Juice_Optimizer_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');

        $this->loader->add_filter('in_widget_form', $plugin_admin, 'ljo_show_option_in_widget', 10, 3);
        $this->loader->add_filter('widget_update_callback', $plugin_admin, 'ljo_save_option_in_widget', 10, 2);

        $this->loader->add_action('after_wp_tiny_mce', $plugin_admin, 'ljo_add_checkbox_in_insert_link');

        $this->loader->add_action('after_setup_theme', $plugin_admin, 'ljo_crb_load');
        $this->loader->add_action('carbon_fields_register_fields', $plugin_admin, 'ljo_crb_attach_fields');

        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
        $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'ljo_add_plugin_page_settings_link');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    2.0.1
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Link_Juice_Optimizer_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_filter('the_content', $plugin_public, 'ljo_content');
        $this->loader->add_filter('dynamic_sidebar_params', $plugin_public, 'ljo_capture_sidebar');
        $this->loader->add_filter('widget_output', $plugin_public, 'ljo_widget_output_filter', 10, 3);
        $this->loader->add_filter('walker_nav_menu_start_el', $plugin_public, 'ljo_menu_item', 10, 4);
        if (did_action('elementor/loaded') > 0) {
            $this->loader->add_action('elementor/widget/render_content', $plugin_public, 'ljo_elementor_widget', 10, 2);
        }
        $this->loader->add_action('wp_head', $plugin_public, 'ljo_estilos_css');
        $this->loader->add_action('plugins_loaded', $plugin_public, 'ljo_hooks_woocommerce');

        $this->loader->add_filter('woocommerce_loop_add_to_cart_link', $plugin_public, 'ljo_add_to_cart', 10, 1);

        $priority = get_option('_ljo_hook_priority', PHP_INT_MAX);
        //$this->loader->add_action('wp_head', $plugin_public, 'ljo_buffer_start', PHP_INT_MAX);
        //$this->loader->add_action('wp_footer', $plugin_public, 'ljo_buffer_end', PHP_INT_MAX);
        $this->loader->add_action('wp_head', $plugin_public, 'ljo_buffer_start', $priority);
        $this->loader->add_action('wp_footer', $plugin_public, 'ljo_buffer_end', $priority);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    2.0.1
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     2.0.1
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     2.0.1
     * @return    Link_Juice_Optimizer_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     2.0.1
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
