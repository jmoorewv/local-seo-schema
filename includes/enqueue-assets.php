<?php
/**
 * Enqueue assets for the Local SEO Schema Plugin.
 *
 * @package Local_SEO_Schema
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The class responsible for enqueuing the stylesheets and scripts.
 */
class Local_SEO_Schema_Enqueue_Assets {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
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
     * @param      string    $version           The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_styles() {
        // Only enqueue on the plugin's settings page.
        if ( isset( $_GET['page'] ) && $_GET['page'] === $this->plugin_name ) {
            wp_enqueue_style( $this->plugin_name . '-admin-style', LSS_PLUGIN_URL . 'admin/css/local-seo-schema-admin.css', array(), $this->version, 'all' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_scripts() {
        // Only enqueue on the plugin's settings page.
        if ( isset( $_GET['page'] ) && $_GET['page'] === $this->plugin_name ) {
            wp_enqueue_script( $this->plugin_name . '-admin-script', LSS_PLUGIN_URL . 'admin/js/local-seo-schema-admin.js', array( 'jquery', 'wp-util' ), $this->version, true );

            // Localize script with data for JavaScript.
            wp_localize_script(
                $this->plugin_name . '-admin-script',
                'lssAdmin',
                array(
                    'nonce' => wp_create_nonce( 'lss_admin_nonce' ),
                    'i18n'  => array(
                        'newLocation' => esc_html__( 'New Location', 'local-seo-schema' ),
                    ),
                )
            );
        }
    }
}
