<?php

/**
 * Registers the `top_home` post type.
 */
function top_home_init() {
	register_post_type( 'top-home', array(
		'labels'                => array(
			'name'                  => __( 'Top Homes', 'wordplate' ),
			'singular_name'         => __( 'Top Home', 'wordplate' ),
			'all_items'             => __( 'All Top Homes', 'wordplate' ),
			'archives'              => __( 'Top Home Archives', 'wordplate' ),
			'attributes'            => __( 'Top Home Attributes', 'wordplate' ),
			'insert_into_item'      => __( 'Insert into Top Home', 'wordplate' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Top Home', 'wordplate' ),
			'featured_image'        => _x( 'Featured Image', 'top-home', 'wordplate' ),
			'set_featured_image'    => _x( 'Set featured image', 'top-home', 'wordplate' ),
			'remove_featured_image' => _x( 'Remove featured image', 'top-home', 'wordplate' ),
			'use_featured_image'    => _x( 'Use as featured image', 'top-home', 'wordplate' ),
			'filter_items_list'     => __( 'Filter Top Homes list', 'wordplate' ),
			'items_list_navigation' => __( 'Top Homes list navigation', 'wordplate' ),
			'items_list'            => __( 'Top Homes list', 'wordplate' ),
			'new_item'              => __( 'New Top Home', 'wordplate' ),
			'add_new'               => __( 'Add New', 'wordplate' ),
			'add_new_item'          => __( 'Add New Top Home', 'wordplate' ),
			'edit_item'             => __( 'Edit Top Home', 'wordplate' ),
			'view_item'             => __( 'View Top Home', 'wordplate' ),
			'view_items'            => __( 'View Top Homes', 'wordplate' ),
			'search_items'          => __( 'Search Top Homes', 'wordplate' ),
			'not_found'             => __( 'No Top Homes found', 'wordplate' ),
			'not_found_in_trash'    => __( 'No Top Homes found in trash', 'wordplate' ),
			'parent_item_colon'     => __( 'Parent Top Home:', 'wordplate' ),
			'menu_name'             => __( 'Top Homes', 'wordplate' ),
		),
		'public'                => true,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_in_nav_menus'     => true,
		'supports'              => array( 'title','page-attributes' ),
		'has_archive'           => false,
		'rewrite'               => false,
		'query_var'             => false,
		'menu_position'         => null,
		'menu_icon'             => 'dashicons-admin-post',
		'show_in_rest'          => true,
		'rest_base'             => 'top-homes',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'top_home_init' );

/**
 * Sets the post updated messages for the `top_home` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `top_home` post type.
 */
function top_home_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['top-home'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Top Home updated. <a target="_blank" href="%s">View Top Home</a>', 'wordplate' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'wordplate' ),
		3  => __( 'Custom field deleted.', 'wordplate' ),
		4  => __( 'Top Home updated.', 'wordplate' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Top Home restored to revision from %s', 'wordplate' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Top Home published. <a href="%s">View Top Home</a>', 'wordplate' ), esc_url( $permalink ) ),
		7  => __( 'Top Home saved.', 'wordplate' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Top Home submitted. <a target="_blank" href="%s">Preview Top Home</a>', 'wordplate' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Top Home scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Top Home</a>', 'wordplate' ),
		date_i18n( __( 'M j, Y @ G:i', 'wordplate' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Top Home draft updated. <a target="_blank" href="%s">Preview Top Home</a>', 'wordplate' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'top_home_updated_messages' );


if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5cc1f8435d633',
		'title' => 'Featured List',
		'fields' => array(
			array(
				'key' => 'field_5cc1f851bcdb5',
				'label' => 'MLS Number',
				'name' => 'mls_number',
				'type' => 'text',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'top-home',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));
	
	endif;