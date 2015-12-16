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
          product_id bigint(20) UNSIGNED ,
          product_name varchar(128),
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

function wp_wc_wr_generateUsername($firstname, $lastname){
  $username = '';

  return $username;
}

function wp_wc_wr_show_warranty_form() {
  global $wpdb, $current_user;

  wp_enqueue_style('wp-wc-warranty-registration', plugins_url('/warranty-registration.css', __FILE__));


  $username = sanitize_user( filter_input(INPUT_POST, 'username'));
  $firstname = filter_input(INPUT_POST, 'firstname');
  $lastname = filter_input(INPUT_POST, 'lastname');
  $email = sanitize_email(filter_input(INPUT_POST, 'email'));
  $address = filter_input(INPUT_POST, 'address');
  $city = filter_input(INPUT_POST, 'city');
  $state = filter_input(INPUT_POST, 'state');
  $postalcode = filter_input(INPUT_POST, 'postalcode');
  $country = filter_input(INPUT_POST, 'country');
  $phone = filter_input(INPUT_POST, 'phone');
  $product_id = filter_input(INPUT_POST, 'product');
  $serial_number = filter_input(INPUT_POST, 'serialnumber');

  if(((isset($email) && $email != '') || $current_user) && $product_id != ''){
    //process form
    if(!$current_user)
      $user = register_new_user($username, $email);
    else {
      $user = $current_user->ID;
    }
    $_pf = new WC_Product_Factory();
    $product = $_pf->get_product($product_id);
    if ( ! is_wp_error( $user ) ) {

      //save user meta
      update_user_meta($user, 'address', $address);
      update_user_meta($user, 'city', $city);
      update_user_meta($user, 'state', $state);
      update_user_meta($user, 'country', $country);
      update_user_meta($user, 'postalcode', $postalcode);
      update_user_meta($user, 'phone', $phone);

      //save registration:
      $ret = $wpdb->insert($wpdb->prefix . 'wc_wr_registrations', array(
        'user_id' => $user,
        'product_id' => $product_id,
        'product_name' =>$product->post_title,
        'serial_number' => $serial_number
      ));
    } else {

    }
  }

  if($ret){
    echo '<p>Thank you!</p>';
    return;
  }
    echo '<div data-ng-app="warranty-registration-app" ><form action="' . $_SERVER['REQUEST_URI'] . '" method="POST" class="wp-wc-warranty-registration" data-ng-controller="WarrantyRegistration as wr">';

    //show form
    echo '
  <fieldset>
  <legend>About You</legend>
    <div class="controls">
      <label for="username">Username *:</label>
      <input type="text" name="username" id="username" required value="'. ($current_user ? $current_user->display_name : '') .'" />
    </div>
    <div class="controls">
      <label for="email">Email *:</label>
      <input type="email" name="email" id="email" value="'.($current_user ? $current_user->get('user_email') : '').'" />
    </div>
    <div class="controls">
      <label for="firstname">First name *:</label>
      <input type="text" name="firstname" id="firstname" value="' . ($current_user ? $current_user->get('first_name') : ''). '" />
    </div>
    <div class="controls">
      <label for="lastname">Last name *:</label>
      <input type="text" name="lastname" id="lastname" value="' . ($current_user ? $current_user->get('last_name'): '') . '" />
    </div>
    <div class="controls">
      <label for="address">Address:</label>
      <input type="text" name="address" id="address" value="'. ($current_user ? $current_user->get('address') : '').'" />
    </div>
    <div class="controls">
      <label for="city">City:</label>
      <input type="text" name="city" id="city" value="'. ($current_user ? $current_user->get('city') : '') .'" />
    </div>
    <div class="controls">
      <label for="state">State/Province:</label>
      <input name="state" id="state" value="'. ($current_user ? $current_user->get('state') : '') .'" />

    </div>
    <div class="controls">
      <label for="postalcode">Postal Code:</label>
      <input name="postalcode" id="postalcode" value="'. ($current_user ? $current_user->get('postalcode') : '').'" />
    </div>
    <div class="controls">
      <label for="country">Country:</label>
      <input name="country" id="country" value="'.($current_user ? $current_user->get('country') : '').'" />
    </div>
    <div class="controls">
      <label for="phone">Phone:</label>
      <input name="phone" id="phone" value="'.($current_user ? $current_user->get('phone') : '') .'" />
    </div>
</fieldset>
<fieldset>
  <legend>About the Product</legend>
  <div class="controls">
    <label for="product">Product: </label>
    <select name="product" id="product" value="">
      <option value="-1"></option>
  ';
    $args = array('post_type' => 'product', 'number_posts' => 1000, 'orderby' => 'post_title', 'order' => 'ASC');
    $products = get_posts($args);
    foreach($products as $p){
      echo '<option value="'.$p->ID.'">'.$p->post_title.'</option>';
    }
  echo '
    </select>
  </div>
  <div class="controls">
    <label for="serialnumber">Serial Number<strong>*</strong>:</label>
    <input type="text" required id="serialnumber" name="serialnumber" />
  </div>
  <div class="controls">
    <label for="purchasedate">Purchase Date:</label>
    <input type="text" name="purchasedate" id="purchasedate" class="datepicker"/>
  </div>
  <div class="controls">
    <label for="purchaselocation">Purchase Location:</label>
    <input type="text" name="purchaselocation" id="purchaselocation" />
  </div>
  <div class="controls">
    <label for="comments">Comments:</label>
    <textarea name="comments" id="comments"></textarea>
  </div>
</fieldset>
</form>
</div>
    ';

  wp_enqueue_script('wp-wc-warranty-registration', plugins_url('/warranty-registration.js', __FILE__), array('angular'));


}
add_shortcode('wp_wc_wr_form', 'wp_wc_wr_show_warranty_form');