<?php
/**
 * Plugin Name: RBP Documentation CPT
 * Description: Creates Documentation custom post types and its related Fields.
 * Version: 1.1.2
 * Author: Real Big Marketing
 * Author URI: http://realbigmarketing.com
 * Text Domain: rbp-documentation
 * GitHub Plugin URI: realbigplugins/rbp-documentation-cpt
 * GitHub Branch: develop
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

	private function __clone() {}

	private function __wakeup() {}

	/**
     * Get active instance
     *
     * @access      public
     * @since       0.1.0
     * @return      object self::$instance The one true CPT_Documentation_Plugin
     */
	public static function instance() {

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

		add_filter( 'template_include', array( $this, 'no_child_permalinks' ) );

		// Alter all calls to the_permalink() and get_permalink() on the Frontend and Backend
		add_filter( 'the_permalink', array( $this, 'filter_the_permalink' ) );
		add_filter( 'post_type_link', array( $this, 'filter_get_permalink' ), 10, 4 );

		add_filter( 'add_meta_boxes', array( $this, 'no_loading_select2' ) );

		//add_filter( 'preview_post_link', array( $this, 'preserve_preview_link' ), 10, 2 );
		
		add_filter( 'edd_email_tags', array( $this, 'add_documentation_link_email_tag' ), 100 );

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
		require_once __DIR__ . '/core/cpt/class-rbm-cpt-documentation.php';
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
		
		// WP Loads things so weird. I really want this function.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
        
        $plugin_data = get_plugin_data( __FILE__ );
        
        // Plugin version
        define( 'CPT_Documentation_Plugin_VER', $plugin_data['Version'] );
        
        // Plugin path
        define( 'CPT_Documentation_Plugin_DIR', plugin_dir_path( __FILE__ ) );
        
        // Plugin URL
        define( 'CPT_Documentation_Plugin_URL', plugin_dir_url( __FILE__ ) );
        
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

		if ( get_post_type() == 'documentation' && ! is_preview() ) {

			// Redirect to the appropriate Anchor within the Page if the direct URL has been used
			if ( $post->post_parent !== 0 ) {

				wp_redirect( $this->get_documentation_anchor_link( $post ) );
				exit;

			}

		}

		return $template;

	}

	/**
     * Replace the_permalink() calls on the Frontend with the Documentation Anchor
     * 
     * @access      public
     * @since       0.1.0
     * 
     * @param       string $url The Post URL
     * @return      string Modified URL
     */
	public function filter_the_permalink( $url ) {

		global $post;

		if ( get_post_type() == 'documentation' ) {

			if ( $post->post_parent !== 0 ) {
				$url = $this->get_documentation_anchor_link( $post );
			}

		}

		return $url;

	}

	/**
     * Replace get_peramlink() calls on the Frontend with the Documentation Anchor
     * 
     * @access      public
     * @since       0.1.0
     * 
     * @param       string  $url       The Post URL
     * @param       object  $post      WP Post Object
     * @param       boolean $leavename Whether to leave the Post Name
     * @param       boolean $sample    Is it a sample permalink?
     * @return      string  Modified URL
     */
	public function filter_get_permalink( $url, $post, $leavename = false, $sample = false ) {

		if ( $post->post_type == 'documentation' ) {

			if ( $post->post_parent !== 0 ) {
				$url = $this->get_documentation_anchor_link( $post );
			}

		}

		return $url;

	}

	public function preserve_preview_link( $preview_link, $post ) {

		if ( $post->post_type == 'documentation' ) {

			$top_level_documentation = $this->get_top_parent_page_id( $post->post_parent );

			$parent_permalink = get_permalink( $top_level_documentation );

			$preview_link = $parent_permalink . sanitize_title( $post->post_name );

			$query_args['preview'] = 'true';

			if ( is_preview() ) {

				if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
					$query_args['preview_id'] = wp_unslash( $_GET['preview_id'] );
					$query_args['preview_nonce'] = wp_unslash( $_GET['preview_nonce'] );
				}

			}

			$preview_link = add_query_arg( $query_args, $preview_link );

		}

		return $preview_link;

	}

	/**
     * Return Anchor Link to the Docuementation Page
     * 
     * @param  [[Type]] $post [[Description]]
     * @return [[Type]] [[Description]]
     */
	private function get_documentation_anchor_link( $post ) {

		$top_level_documentation = $this->get_top_parent_page_id( $post->post_parent );

		if ( ! $top_level_documentation ) return $url;

		$parent_permalink = get_permalink( $top_level_documentation );

		$anchor = preg_replace( '/^([0-9]|[a-z])+\./i', '', $post->post_title );

		$anchor = trim( $anchor ); // Ensure all extra spaces are out before our replacements

		$url = $parent_permalink . '#' . str_replace( ' ', '-', strtolower( $anchor ) );

		return $url;

	}

	public function no_loading_select2() {

		if ( get_post_type() == 'download' ) {

			add_filter( 'rbm_load_select2', '__return_false', 1000 );

		}

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
	private function get_top_parent_page_id( $post_id = 0 ) {

		global $post;

		if ( ! $post && is_admin() && isset( $_GET['post'] ) ) {
			$post = get_post( $_GET['post'] );
		}
		else if ( ! $post && $post_id !== 0 ) {
			$post = get_post( $post_id );
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

	public function add_documentation_link_email_tag( $email_tags ) {

		$email_tags[] = array( 
			'tag' => 'documentation_list', 
			'description' => __( 'A plain-text list of Documentation Links for each download purchased', 'rbp-documentation' ),
			'function' => 'edd_rbp_documentation_links' 
		);
		
		return $email_tags;

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
				<?php printf( __( 'To use the %s Plugin, both %s and %s must be active as either a Plugin or a Must Use Plugin!', 'rbp-documentation' ), '<strong>RBP Documentation CPT</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">RBM Field Helpers</a>', '<a href="//github.com/realbig/rbm-cpts/" target="_blank">RBM Custom Post Types</a>' ); ?>
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

		require_once __DIR__ . '/core/rbm-documentation-cpt-functions.php';

		RBMDOCUMENTATIONCPT();

	}

} );