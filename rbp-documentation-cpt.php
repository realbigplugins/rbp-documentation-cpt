<?php
/**
 * Plugin Name: RBP Documentation CPT
 * Description: Creates Documentation custom post types and its related Fields.
 * Version: 0.1.0
 * Author: Eric Defore
 * Author URI: http://realbigmarketing.com
 * Text Domain: rbp-documentation
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class CPT_Documentation_Plugin {

    /**
     * @var         Holds our CPT
     * @since       0.1.0
     */
    public $cpt;
    
    /**
     * @var         CPT_Documentation_Plugin $instance The one true CPT_Documentation_Plugin
     * @since       0.1.0
     */
    private static $instance;
    
    /**
     * @var         Used for Localization
     * @since       0.1.0
     */
    public static $plugin_id = 'rbp-documentation';

    private function __clone() {}

    private function __wakeup() {}

    /**
     * Get active instance
     *
     * @access      public
     * @since       0.1.0
     * @return      object self::$instance The one true CPT_Documentation_Plugin
     */
    public static function get_instance() {

        if ( ! self::$instance ) {
            self::$instance = new CPT_Documentation_Plugin();
            self::$instance->hooks();
            self::$instance->require_necessities();
        }
        
        return self::$instance;

    }
    
    /**
     * Run action and filter hooks
     *
     * @access      private
     * @since       0.1.0
     * @return      void
     */
    private function hooks() {
        
        // Doing these within a Hook so I have access to some other WP functions
        add_action( 'init', array( $this, 'setup_constants' ) );
        
        // Remove "Palm Tree" Post Type included in RBM CPTs
        add_action( 'init', array( $this, 'chopping_palm_trees' ) );
        
        add_filter( 'p2p_relationships', array( $this, 'p2p_relationship' ) );
        
    }
    
    /**
     * Requires necessary, base filesystem_method
     * 
     * @access      private
     * @since       0.1.0
     * @return      void
     */
    private function require_necessities() {
        
        // CPT functionality
        require_once __DIR__ . '/cpt/class-rbm-cpt-documentation.php';
        $this->cpt = new RBM_CPT_Documentation();
        
    }
    
    /**
     * Setup plugin constants
     *
     * @access      public
     * @since       0.1.0
     * @return      void
     */
    public function setup_constants() {
        
        $plugin_data = get_plugin_data( __FILE__ );
        
        // Plugin version
        define( 'CPT_Documentation_Plugin_VER', $plugin_data['Version'] );
        
        // Plugin path
        define( 'CPT_Documentation_Plugin_DIR', plugin_dir_path( __FILE__ ) );
        
        // Plugin URL
        define( 'CPT_Documentation_Plugin_URL', plugin_dir_url( __FILE__ ) );
        
    }
    
    /**
     * RBM CPTs Ships with a Post Type included with no way _currently_ to disable it.
     * 
     * @access      public
     * @since       0.1.0
     * @return      void
     */
    public function chopping_palm_trees() {
	
        global $wp_post_types;
        $post_type = 'palm-tree';

        if ( isset( $wp_post_types[ $post_type ] ) ) {
            unset( $wp_post_types[ $post_type ] );

            $slug = ( ! $slug ) ? 'edit.php?post_type=' . $post_type : $slug;
            remove_menu_page( $slug );
        }
        
    }
    
    /**
     * Create a P2P relationship from Downloads to Documentation Pages
     * 
     * @access      public
     * @since       0.1.0
     * 
     * @param       array $relationships P2P Relationships
     * @return      array $relationships
     */
    public function p2p_relationship( $relationships ) {
        
        $relationships['download'] = 'documentation';

        return $relationships;
        
    }
    
    /**
     * Error Message if dependencies aren't meta_is_product_attribute()
     * 
     * @access      public
     * @since       0.1.0
     * @return      void
     */
    public static function missing_dependencies() { ?>

        <div class="notice notice-error">
            <p>
                <?php printf( __( 'To use the %s Plugin, both %s and %s must be active as either a Plugin or a Must Use Plugin!', CPT_Documentation_Plugin::$plugin_id ), '<strong>RBP Documentation CPT</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">RBM Field Helpers</a>', '<a href="//github.com/realbig/rbm-cpts/" target="_blank">RBM Custom Post Types</a>' ); ?>
            </p>
        </div>
        
        
        <?php
    }

}

/**
 * The main function responsible for returning the one true CPT_Documentation_Plugin
 * instance to functions everywhere
 *
 * @since       0.1.0
 * @return      \CPT_Documentation_Plugin The one true CPT_Documentation_Plugin
 */
add_action( 'plugins_loaded', function() {

    if ( ! class_exists( 'RBM_CPT' ) || ! class_exists( 'RBM_FieldHelpers' ) ) {

        add_action( 'admin_notices', array( 'CPT_Documentation_Plugin', 'missing_dependencies' ) );

    }
    else {
        
        return CPT_Documentation_Plugin::get_instance();
        
    }
    
} );