<?php

class RBM_CPT_Documentation extends RBM_CPT {

    public $post_type = 'documentation';
    public $label_singular = null;
    public $label_plural = null;
    public $labels = array();
    public $icon = 'welcome-write-blog';
    public $post_args = array(
        'hierarchical' => true,
        'supports' => array( 'title', 'editor', 'author', 'page-attributes', 'thumbnail' ),
        'has_archive' => false,
        'rewrite' => array(
            'slug' => 'docs',
            'with_front' => false,
            'feeds' => false,
            'pages' => true
        ),
    );
    
    function __construct() {
        
        // This allows us to Localize the Labels
        $this->label_singular = __( 'Documentation Page', CPT_Documentation_Plugin::$plugin_id );
        $this->label_plural = __( 'Documentation Pages', CPT_Documentation_Plugin::$plugin_id );

        $this->labels = array(
            'menu_name' => __( 'Documentation', CPT_Documentation_Plugin::$plugin_id ),
            'all_items' => __( 'All Documentation', CPT_Documentation_Plugin::$plugin_id ),
        );

        parent::__construct();
        
    }

}