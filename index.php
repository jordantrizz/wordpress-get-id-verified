<?php

/**
* Plugin Name: Get ID Verified
* Plugin URI: https://github.com/jordantrizz/wordpress-get-id-verified
* Description: This plugin contains all of my awesome custom functions.
* Author: Jordan
* Version: 0.1
*
* Code taken from https://www.shift8web.ca/2018/06/how-to-implement-a-government-id-verification-system-with-woocommerce-and-wordpress/ and cleaned up to work properly.
*/

/* Created Menu Item in WooCommerce for "Get ID Verified" */
function account_menu_items( $items ) { 
        $items['idverify'] = __( 'ID Verification', 'idverify' );
            return $items;
} 
add_filter( 'woocommerce_account_menu_items', 'account_menu_items', 10, 1 );

function add_my_account_endpoint() {
        add_rewrite_endpoint( 'idverify', EP_PAGES );
}
add_action( 'init', 'add_my_account_endpoint' );

function idverify_endpoint_content() {
    $current_user = get_current_user_id();
    $user_verified = get_field('government_id_verified', 'user_' . $current_user);
    if ($user_verified && $user_verified == 'yes') {
        echo 'You are already verified and no longer need to upload your ID';
    } else {
        $page = get_page_by_title( 'Get ID Verified Not Verified' );
        $content = apply_filters('the_content', $page->post_content); 
        echo $content;

/*        acf_form_head();
        $form_options = array(
            'fields' => array(
                'attach_valid_government_id',
            ),
            'submit_value' => __("Save changes", 'acf'),
            'updated_message' => __("Government ID submitted. Please allow 1-2 business days for verification to be complete.", 'acf'),
            'post_id' => 'user_' . $current_user,
        );
        acf_form($form_options);*/
    }
}
add_action( 'woocommerce_account_idverify_endpoint', 'idverify_endpoint_content' );

function idverify_order_column( $columns ) {
    $columns['idverify_column'] = 'ID Verified';
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'idverify_order_column' );

/* Add ID Verified Column to Orders Page */
function add_idverify_column_header( $columns )  {
     $new_columns = array();
 
    foreach ( $columns as $column_name => $column_info ) {
        $new_columns[ $column_name ] = $column_info;
        if ( 'idverify_column' === $column_name ) {
            $new_columns['idverify_column'] = __( 'ID Verified', 'my-textdomain' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'add_idverify_column_header', 20 );

/* Add Verified or Not Verified to ID Verified Column */
function add_order_idverify_column_content( $column ) {
    global $post;
    if ( 'idverify_column' === $column ) {
        $order    = wc_get_order( $post->ID );
        $user_id = $order->user_id;
        $verified_status = get_field('government_id_verified', 'user_' . $user_id);
        if ($verified_status && $verified_status == 'yes') {
            echo '<div class="idverify_button_verified">Verified</div>';
        } else {
            echo '<div class="idverify_button_verify"><a href="' . get_edit_user_link($user_id) . '#government_id_verified" target="_new">Verify User</a></div>';
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'add_order_idverify_column_content' );


/*function add_notice_for_verified() {
    $current_user = get_current_user_id();
    $user_verified = get_field('government_id_verified', 'user_' . $current_user);
    if (!$user_verified || $user_verified = 'no') {
        $page = get_page_by_title( 'Get ID Verified Checkout Notice' );
        $content = apply_filters('the_content', $page->post_content);
        wc_add_notice($content,'error');
    }
}
add_action( 'woocommerce_checkout_before_customer_details', 'add_notice_for_verified');*/

function load_custom_wp_admin_style($hook) {
        wp_enqueue_style( 'custom_wp_admin_css', plugins_url('style.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
?>