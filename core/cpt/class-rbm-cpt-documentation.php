<?php

/**
 * Class RBM_CPT_Documentation
 *
 * Creates the post type.
 *
 * @since {{VERSION}}
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
	 * @since {{VERSION}}
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
		add_filter( 'rbm_fields_save', array( $this, 'save_order' ) );
	}

	/**
	 * Adds meta boxes to the post type edit page.
	 *
	 * @since {{VERSION}}
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
	 * @since {{VERSION}}
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

		rbm_do_field_list( 'documentation_order', false, false, array(
			'items' => wp_list_pluck( $docs, 'post_title', 'ID' ),
		) );
	}

	/**
	 * Saves the new order.
	 *
	 * @since {{VERSION}}
	 * @access private
	 *
	 * @param array $fields Fields to save.
	 * @param int $post_ID ID of post being saved.
	 *
	 * @return mixed
	 */
	function save_order( $fields, $post_ID ) {

		if ( ( $key = array_search( '_rbm_documentation_order', $fields ) ) === false ) {

			return $fields;
		}

		$order = isset( $_POST['_rbm_documentation_order'] ) ? $_POST['_rbm_documentation_order'] : false;

		if ( $order ) {

			foreach ( $order as $i => $post_ID ) {

				wp_update_post( array(
					'ID'         => $post_ID,
					'menu_order' => $i,
				) );
			}
		}

		unset( $fields[ $key ] );

		return $fields;
	}
}