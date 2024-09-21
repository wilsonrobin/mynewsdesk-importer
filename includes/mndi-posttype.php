<?php

function register_mndi_posttype() : void {
	$labels = [
		'name' => _x( 'News', 'Post Type General Name', 'mndi' ),
		'singular_name' => _x( 'News', 'Post Type Singular Name', 'mndi' ),
		'menu_name' => __( 'News', 'mndi' ),
		'name_admin_bar' => __( 'News', 'mndi' ),
		'archives' => __( 'News Archives', 'mndi' ),
		'attributes' => __( 'News Attributes', 'mndi' ),
		'parent_item_colon' => __( 'Parent News:', 'mndi' ),
		'all_items' => __( 'All News', 'mndi' ),
		'add_new_item' => __( 'Add New News', 'mndi' ),
		'add_new' => __( 'Add New', 'mndi' ),
		'new_item' => __( 'New News', 'mndi' ),
		'edit_item' => __( 'Edit News', 'mndi' ),
		'update_item' => __( 'Update News', 'mndi' ),
		'view_item' => __( 'View News', 'mndi' ),
		'view_items' => __( 'View News', 'mndi' ),
		'search_items' => __( 'Search News', 'mndi' ),
		'not_found' => __( 'News Not Found', 'mndi' ),
		'not_found_in_trash' => __( 'News Not Found in Trash', 'mndi' ),
		'featured_image' => __( 'Featured Image', 'mndi' ),
		'set_featured_image' => __( 'Set Featured Image', 'mndi' ),
		'remove_featured_image' => __( 'Remove Featured Image', 'mndi' ),
		'use_featured_image' => __( 'Use as Featured Image', 'mndi' ),
		'insert_into_item' => __( 'Insert into News', 'mndi' ),
		'uploaded_to_this_item' => __( 'Uploaded to this News', 'mndi' ),
		'items_list' => __( 'News List', 'mndi' ),
		'items_list_navigation' => __( 'News List Navigation', 'mndi' ),
		'filter_items_list' => __( 'Filter News List', 'mndi' ),
	];
	$labels = apply_filters( 'mndi_news-labels', $labels );

	$args = [
		'label' => __( 'News', 'mndi' ),
		'description' => __( 'News Description', 'mndi' ),
		'labels' => $labels,
		'supports' => [
			'title',
			'editor',
			'thumbnail'
		],
		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 30,
		'menu_icon' => 'dashicons-rss',
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'exclude_from_search' => false,
		'has_archive' => (get_mndi_option('mndi_archive') === 'true' ? true : false),
		'capability_type' => 'post',
		'show_in_rest' => false,
		'rewrite' => array('slug' => __('news', 'mndi')),
	];
	$args = apply_filters( 'mndi_news-args', $args );

	register_post_type( 'mndi_news', $args );


	$labels = array(
		'name'                       => _x( 'Categories', 'Taxonomy General Name', 'mndi' ),
		'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'mndi' ),
		'menu_name'                  => __( 'Categories', 'mndi' ),
		'all_items'                  => __( 'All categories', 'mndi' ),
		'parent_item'                => __( 'Parent category', 'mndi' ),
		'parent_item_colon'          => __( 'Parent category:', 'mndi' ),
		'new_item_name'              => __( 'New category Name', 'mndi' ),
		'add_new_item'               => __( 'Add New category', 'mndi' ),
		'edit_item'                  => __( 'Edit category', 'mndi' ),
		'update_item'                => __( 'Update category', 'mndi' ),
		'view_item'                  => __( 'View category', 'mndi' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'mndi' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'mndi' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'mndi' ),
		'popular_items'              => __( 'Popular categories', 'mndi' ),
		'search_items'               => __( 'Search categories', 'mndi' ),
		'not_found'                  => __( 'Not Found', 'mndi' ),
		'no_terms'                   => __( 'No categories', 'mndi' ),
		'items_list'                 => __( 'Items list', 'mndi' ),
		'items_list_navigation'      => __( 'Items list navigation', 'mndi' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'mndi_category', array( 'mndi_news' ), $args );
}
add_action( 'init', 'register_mndi_posttype');