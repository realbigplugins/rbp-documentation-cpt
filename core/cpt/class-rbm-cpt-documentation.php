<?php

/**
 * Class RBM_CPT_Documentation
 *
 * Creates the post type.
 *
 * @since 1.0.0
 */
class RBM_CPT_Documentation extends RBM_CPT {

	public $post_type = 'documentation';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'welcome-write-blog';
	public $p2p = 'download';
	public $post_args = array(
		'hierarchical' => true,
		'supports'     => array( 'title', 'editor', 'author', 'page-attributes', 'thumbnail' ),
		'has_archive'  => false,
		'rewrite'      => array(
			'slug'       => 'docs',
			'with_front' => false,
			'feeds'      => false,
			'pages'      => true
		),
		'capability_type' => 'documentation',
	);

	/**
	 * RBM_CPT_Documentation constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'Documentation Page', 'rbp-documentation' );
		$this->label_plural   = __( 'Documentation Pages', 'rbp-documentation' );

		$this->labels = array(
			'menu_name' => __( 'Documentation', 'rbp-documentation' ),
			'all_items' => __( 'All Documentation', 'rbp-documentation' ),
		);

		parent::__construct();

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_filter( '_rbm_fieldhelpers_documentation_order_fields_save', array( $this, 'save_order' ), 10, 2 );
	}

	/**
	 * Adds meta boxes to the post type edit page.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	function add_meta_boxes() {

		add_meta_box(
			'rbp-documentation-order',
			'Order',
			array( $this, 'mb_order' ),
			'documentation'
		);
	}

	/**
	 * Metabox callback for the order.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	function mb_order() {

		$docs = get_posts( array(
			'post_type'   => $this->post_type,
			'post_parent' => get_the_ID(),
			'numberposts' => - 1,
			'orderby'     => 'menu_order title',
			'order'       => 'ASC',
		) );

		$items = wp_list_pluck( $docs, 'post_title', 'ID' );

		rbm_fh_do_field_list( array(
			'name' => 'documentation_order',
			'group' => 'documentation_order',
			'items' => $items,
			'value' => array_keys( $items ),
		) );
		
		rbm_fh_init_field_group( 'documentation_order' );
	}

	/**
	 * Saves the new order.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $fields Fields to save.
	 * @param int $post_ID ID of post being saved.
	 *
	 * @return mixed
	 */
	function save_order( $fields, $post_ID ) {

		if ( ( $key = array_search( 'documentation_order', $fields ) ) === false ) {

			return $fields;
		}

		remove_filter( '_rbm_fieldhelpers_documentation_order_fields_save', array( $this, 'save_order' ), 10, 2 );

		$order = isset( $_POST['_rbm_documentation_order'] ) ? $_POST['_rbm_documentation_order'] : false;

		if ( $order ) {

			foreach ( $order as $i => $post_ID ) {

				// Triggers save_post, which can cause an infinite loop if we don't remove our filter before hand
				wp_update_post( array(
					'ID'         => $post_ID,
					'menu_order' => $i,
				) );
			}
		}

		unset( $fields[ $key ] );

		add_filter( '_rbm_fieldhelpers_documentation_order_fields_save', array( $this, 'save_order' ), 10, 2 );

		return $fields;
	}
}