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
        
        add_filter( 'p2p_relationships', array( $this, 'p2p_relationship' ) );
        
        add_filter( 'rbm_cpts_available_p2p_posts', array( $this, 'p2p_query_args' ) );
        
        add_filter( 'template_include', array( $this, 'no_child_permalinks' ) );
        
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
     * Modifies WP Query Args for the P2P Relationship
     * 
     * @access      public
     * @since       0.1.0
     * 
     * @param       array $args WP Query Args
     * @return      array WP Query Args
     */
    public function p2p_query_args( $args ) {
        
        // Only show Top-Level Documentation Posts
        $args['post_parent'] = 0;
        
        return $args;
        
    }
    
    /**
     * Child Documentation Pages don't have a Single
     * 
     * @access      public
     * @since       0.1.0
     * 
     * @param       string $template Template File Path
     * @return      string Template File Path
     */
    public function no_child_permalinks( $template ) {
        
        global $post;

        if ( get_post_type() == 'documentation' ) {

            // Redirect to the appropriate Anchor within the Page if the direct URL has been used
            if ( $post->post_parent !== 0 ) {
                
                $top_level_documentation = $this->get_top_parent_page_id( $post->post_parent );

                $parent_permalink = trailingslashit( get_permalink( $top_level_documentation ) );

                $anchor = preg_replace( '/^([0-9]|[a-z])+\./i', '', $post->post_title );

                $anchor = trim( $anchor ); // Ensure all extra spaces are out before our replacements

                $anchor = str_replace( ' ', '-', strtolower( $anchor ) );
                
                wp_redirect( $parent_permalink . '#' . $anchor );
                exit;

            }

        }

        return $template;
        
    }
    
    /**
     * Gets the Top Most Post ID
     * 
     * @access      private
     * @since       0.1.0
     * 
     * @param       integer   $post_id Post ID we're checking against
     * @return      int|mixed The Top Most Post ID
     */
    public function get_top_parent_page_id( $post_id = 0 ) {
        
        global $post;
        
        if ( ! $post && is_admin() && isset( $_GET['post'] ) ) {
            $post = get_post( $_GET['post'] );
        }
        elseif ( ! $post ) {
            return false;
        }
        
        if ( $post_id !== 0 ) {
            $_post = get_post( $post_id );
        }
        else {
            $_post = $post;
        }
        
        $ancestors = $_post->ancestors;
        
        // Check if page is a child page (any level)
        if ( $ancestors ) {
            //  Grab the ID of top-level page from the tree
            return end( $ancestors );
        }
        else {
            // Page is the top level, so use it's own id
            return $_post->ID;
        }
        
    }
    
    /**
     * Error Message if dependencies aren't met
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