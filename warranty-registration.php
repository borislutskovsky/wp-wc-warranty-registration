<?php
/*
 * Plugin Name: WooCommerce Warranty Registration
 * Description: a plugin that allows users to register their warranty for products
 * Version: 1.0
 * Author: Boris Lutskovsky
 * Author URI: http://www.iamboris.com
 * License: MIT
 */
global $wp_wc_wr_version;
$wp_wc_wr_version = '1.0';


function wp_wc_warranty_registration_install(){
  global $wpdb, $wp_wc_wr_version;

  $table_name = $wpdb->prefix . 'wc_wr_registrations';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE $table_name (
          id INT NOT NULL AUTO_INCREMENT,
          user_id bigint(20) UNSIGNED NOT NULL,
          product_id bigint(20) UNSIGNED NOT NULL,
          serial_number varchar(64) NOT NULL,
          purchase_date DATE NOT NULL,
          purchase_location VARCHAR(128) NOT NULL,
          comments TEXT
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );

  add_option('wp_wc_wr_version', $wp_wc_wr_version);

}

register_activation_hook(__FILE__, 'wp_wc_warranty_registration_install');