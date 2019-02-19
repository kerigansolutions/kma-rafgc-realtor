<?php

/**
 * Registers the `featured_listing` post type.
 */
function featured_listing_init() {
	register_post_type( 'featured-listing', array(
		'labels'                => array(
			'name'                  => __( 'Featured Listings', 'wordplate' ),
			'singular_name'         => __( 'Featured Listing', 'wordplate' ),
			'all_items'             => __( 'All Featured Listings', 'wordplate' ),
			'archives'              => __( 'Featured Listing Archives', 'wordplate' ),
			'attributes'            => __( 'Featured Listing Attributes', 'wordplate' ),
			'insert_into_item'      => __( 'Insert into Featured Listing', 'wordplate' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Featured Listing', 'wordplate' ),
			'featured_image'        => _x( 'Featured Image', 'featured-listing', 'wordplate' ),
			'set_featured_image'    => _x( 'Set featured image', 'featured-listing', 'wordplate' ),
			'remove_featured_image' => _x( 'Remove featured image', 'featured-listing', 'wordplate' ),
			'use_featured_image'    => _x( 'Use as featured image', 'featured-listing', 'wordplate' ),
			'filter_items_list'     => __( 'Filter Featured Listings list', 'wordplate' ),
			'items_list_navigation' => __( 'Featured Listings list navigation', 'wordplate' ),
			'items_list'            => __( 'Featured Listings list', 'wordplate' ),
			'new_item'              => __( 'New Featured Listing', 'wordplate' ),
			'add_new'               => __( 'Add New', 'wordplate' ),
			'add_new_item'          => __( 'Add New Featured Listing', 'wordplate' ),
			'edit_item'             => __( 'Edit Featured Listing', 'wordplate' ),
			'view_item'             => __( 'View Featured Listing', 'wordplate' ),
			'view_items'            => __( 'View Featured Listings', 'wordplate' ),
			'search_items'          => __( 'Search Featured Listings', 'wordplate' ),
			'not_found'             => __( 'No Featured Listings found', 'wordplate' ),
			'not_found_in_trash'    => __( 'No Featured Listings found in trash', 'wordplate' ),
			'parent_item_colon'     => __( 'Parent Featured Listing:', 'wordplate' ),
			'menu_name'             => __( 'Featured Listings', 'wordplate' ),
		),
		'public'                => true,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_nav_menus'     => true,
		'supports'              => array( 'title' ),
		'has_archive'           => true,
		'rewrite'               => true,
		'query_var'             => true,
		'menu_position'         => null,
		'menu_icon'             => 'dashicons-admin-post',
		'show_in_rest'          => true,
		'rest_base'             => 'featured-listing',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'featured_listing_init' );

/**
 * Sets the post updated messages for the `featured_listing` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `featured_listing` post type.
 */
function featured_listing_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['featured-listing'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Featured Listing updated. <a target="_blank" href="%s">View Featured Listing</a>', 'wordplate' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'wordplate' ),
		3  => __( 'Custom field deleted.', 'wordplate' ),
		4  => __( 'Featured Listing updated.', 'wordplate' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Featured Listing restored to revision from %s', 'wordplate' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Featured Listing published. <a href="%s">View Featured Listing</a>', 'wordplate' ), esc_url( $permalink ) ),
		7  => __( 'Featured Listing saved.', 'wordplate' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Featured Listing submitted. <a target="_blank" href="%s">Preview Featured Listing</a>', 'wordplate' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Featured Listing scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Featured Listing</a>', 'wordplate' ),
		date_i18n( __( 'M j, Y @ G:i', 'wordplate' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Featured Listing draft updated. <a target="_blank" href="%s">Preview Featured Listing</a>', 'wordplate' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'featured_listing_updated_messages' );
