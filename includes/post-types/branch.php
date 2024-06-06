<?php

$args = array(
    'labels'             => array(
        'name'          => __('Properties'),
        'singular_name' => __('Property')
    ),
	'supports'              => array( 'title', 'editor', 'thumbnail' ),
	'hierarchical'          => false,
	'public'                => true,
	'show_ui'               => true,
	'show_in_menu'          => true,
	//'menu_position'         => 5,
	'menu_icon'             => 'dashicons-building',
	'show_in_admin_bar'     => true,
	'show_in_nav_menus'     => true,
	'can_export'            => true,
	'has_archive'           => false,
	'exclude_from_search'   => true,
	'publicly_queryable'    => true,
	'rewrite'               => array(
	    'slug'=>'property',
        'with_front' => true
    ),
	'capability_type'       => 'post',
	'show_in_rest'          => false,
);
register_post_type( 'sa_property', $args );
