<?php
/**
 * Plugin Name: Local SEO Schema Plugin
 * Description: Adds Local SEO Schema (JSON-LD) for multiple business locations to your WordPress site.
 * Version:     1.0.0
 * Author:      Jonathan Moore
 * Author URI:  https://jmoorewv.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: local-seo-schema
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define plugin constants.
 */
define( 'LSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LSS_PLUGIN_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
class Local_SEO_Schema_Plugin {

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
        $this->plugin_name = 'local-seo-schema';
        $this->version = LSS_PLUGIN_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * - Local_SEO_Schema_Admin. Defines all hooks for the admin area.
     * - Local_SEO_Schema_Public. Defines all hooks for the public side of the site.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Include the file that handles admin settings.
        require_once LSS_PLUGIN_DIR . 'admin/settings-page.php';

        // Include the file that handles schema generation.
        require_once LSS_PLUGIN_DIR . 'includes/schema-generator.php';

        // Include the file that handles asset enqueuing.
        require_once LSS_PLUGIN_DIR . 'includes/enqueue-assets.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $admin_settings = new Local_SEO_Schema_Admin( $this->get_plugin_name(), $this->get_version() );
        $enqueue_assets = new Local_SEO_Schema_Enqueue_Assets( $this->get_plugin_name(), $this->get_version() );

        add_action( 'admin_menu', array( $admin_settings, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $admin_settings, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $enqueue_assets, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $enqueue_assets, 'enqueue_admin_scripts' ) );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $schema_generator = new Local_SEO_Schema_Generator( $this->get_plugin_name(), $this->get_version() );

        // Add the schema to the head of the site.
        add_action( 'wp_head', array( $schema_generator, 'add_local_business_schema' ) );
    }

    /**
     * Retrieve the name of the plugin.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
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

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file means
 * that all of the interdependencies will be in place.
 *
 * @since    1.0.0
 */
function run_local_seo_schema_plugin() {
    $plugin = new Local_SEO_Schema_Plugin();
}
run_local_seo_schema_plugin();

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 */
function activate_local_seo_schema_plugin() {
    // Perform any activation tasks here if needed.
    // For example, setting default options.
    add_option( 'local_seo_schema_locations', array() );
}
register_activation_hook( __FILE__, 'activate_local_seo_schema_plugin' );

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.0
 */
function deactivate_local_seo_schema_plugin() {
    // Perform any deactivation tasks here if needed.
    // For example, cleaning up options or transient data.
    delete_option( 'local_seo_schema_locations' );
}
register_deactivation_hook( __FILE__, 'deactivate_local_seo_schema_plugin' );
