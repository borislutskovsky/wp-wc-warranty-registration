<?php
/*
 * Plugin Name: WooCommerce Product Warranty Registration
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
          comments TEXT,
          created_date DATETIME NOT NULL,
          PRIMARY KEY (id)
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
  ob_start();
  wp_enqueue_style('wp-wc-warranty-registration', plugins_url('/warranty-registration.css', __FILE__));


  $username = sanitize_user( filter_input(INPUT_POST, 'wr-username'));
  $firstname = filter_input(INPUT_POST, 'wr-firstname');
  $lastname = filter_input(INPUT_POST, 'wr-lastname');
  $email = sanitize_email(filter_input(INPUT_POST, 'wr-email'));
  $address = filter_input(INPUT_POST, 'wr-address');
  $city = filter_input(INPUT_POST, 'wr-city');
  $state = filter_input(INPUT_POST, 'wr-state');
  $postalcode = filter_input(INPUT_POST, 'wr-postalcode');
  $purchasedate = filter_input(INPUT_POST, 'wr-purchasedate');
  $location = filter_input(INPUT_POST, 'wr-purchaselocation');
  $comments = filter_input(INPUT_POST, 'wr-comments');
  $country = filter_input(INPUT_POST, 'wr-country');
  $phone = filter_input(INPUT_POST, 'wr-phone');
  $product_id = filter_input(INPUT_POST, 'wr-product');
  $serial_number = filter_input(INPUT_POST, 'wr-serialnumber');

  if(isset($_POST['wr-submit']) && $_POST['wr-submit'] == 'Submit'){

    //process form
    if(!is_user_logged_in()) {
      $user = register_new_user( $username, $email );
    } else {
      $user = $current_user->ID;
    }
    $_pf = new WC_Product_Factory();
    $product = $_pf->get_product($product_id);
    if ( ! is_wp_error( $user ) ) {

      //save user meta
      update_user_meta($user, 'firstname', $firstname);
      update_user_meta($user, 'lastname', $lastname);
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
        'serial_number' => $serial_number,
        'purchase_date' => date('Y-m-d', strtotime($purchasedate)),
        'purchase_location' => $location,
        'comments' => $comments,
        'created_date' => date('Y-m-d H:i:s')
      ));
      //echo $wpdb->last_query;
    } else {
      echo '<div class="error">'.$user->get_error_message().'</div>';
    }
  }
  if(isset($ret) && $ret){
   
    //echo var_dump($_POST);
    $thankyou_file = 'templates/warranty-registration-thank-you.php';
    if(file_exists(get_template_directory()."/woocommerce/$thankyou_file")){
      require_once(get_template_directory()."/woocommerce/$thankyou_file");
    } else {
      require_once($thankyou_file);
    }


    return ob_get_clean();
  }
    echo '<div data-ng-app="warranty-registration-app" ><form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="POST" class="wp-wc-warranty-registration" data-ng-controller="WarrantyRegistration as wr">';

    //show form
    echo '
  <fieldset>
  <legend>About You</legend>
    <div class="controls">
      <label for="username">Username *:</label>
      <input type="text" name="wr-username" id="username" required value="'. (is_user_logged_in() ? $current_user->display_name : $username) .'" />
    </div>
    <div class="controls">
      <label for="email">Email *:</label>
      <input type="email" name="wr-email" id="email" value="'.(is_user_logged_in() ? $current_user->get('user_email') : $email).'" />
    </div>
    <div class="controls">
      <label for="firstname">First name *:</label>
      <input type="text" name="wr-firstname" id="firstname" value="' . (is_user_logged_in() ? $current_user->get('first_name') : $firstname). '" />
    </div>
    <div class="controls">
      <label for="lastname">Last name *:</label>
      <input type="text" name="wr-lastname" id="lastname" value="' . (is_user_logged_in() ? $current_user->get('last_name'): $lastname) . '" />
    </div>
    <div class="controls">
      <label for="address">Address:</label>
      <input type="text" name="wr-address" id="address" value="'. (is_user_logged_in() ? $current_user->get('address') : $address).'" />
    </div>
    <div class="controls">
      <label for="city">City:</label>
      <input type="text" name="wr-city" id="city" value="'. (is_user_logged_in() ? $current_user->get('city') : $city) .'" />
    </div>
    <div class="controls">
      <label for="state">State/Province:</label>
      <input name="wr-state" id="state" value="'. (is_user_logged_in() ? $current_user->get('state') : $state) .'" />

    </div>
    <div class="controls">
      <label for="postalcode">Postal Code:</label>
      <input name="wr-postalcode" id="postalcode" value="'. (is_user_logged_in() ? $current_user->get('postalcode') : $postalcode).'" />
    </div>
    <div class="controls">
      <label for="country">Country:</label>
      <input name="wr-country" id="country" value="'.(is_user_logged_in() ? $current_user->get('country') : $country).'" />
    </div>
    <div class="controls">
      <label for="phone">Phone:</label>
      <input name="wr-phone" id="phone" value="'.(is_user_logged_in() ? $current_user->get('phone') : $phone) .'" />
    </div>
</fieldset>
<fieldset>
  <legend>About the Product</legend>
  <div class="controls">
    <label for="product">Product: </label>
    <select name="wr-product" id="product" value="">
      <option value="-1"></option>
  ';
    $args = array('post_type' => 'product', 'number_posts' => 1000, 'orderby' => 'post_title', 'order' => 'ASC');
    $products = get_posts($args);
    foreach($products as $p){
      echo '<option value="'.$p->ID.'" '.($p->ID == $product_id ? " selected " : "").' >'.$p->post_title.'</option>';
    }
  echo '
    </select>
  </div>
  <div class="controls">
    <label for="serialnumber">Serial Number<strong>*</strong>:</label>
    <input type="text" required id="serialnumber" name="wr-serialnumber" value="'.$serial_number.'" />
  </div>
  <div class="controls">
    <label for="purchasedate">Purchase Date:</label>
    <input type="text" name="wr-purchasedate" id="purchasedate" class="datepicker" value="'.$purchasedate.'"/>
  </div>
  <div class="controls">
    <label for="purchaselocation">Purchase Location:</label>
    <input type="text" name="wr-purchaselocation" id="purchaselocation" value="'.$location.'" />
  </div>
  <div class="controls">
    <label for="comments">Comments:</label>
    <textarea name="wr-comments" id="comments">'.$comments.'</textarea>
  </div>
</fieldset>
  <input type="submit" value="Submit" name="wr-submit"/>
</form>
</div>
    ';

  wp_enqueue_script('wp-wc-warranty-registration', plugins_url('/warranty-registration.js', __FILE__), array('angular'));


  return ob_get_clean();
}
add_shortcode('wp_wc_wr_form', 'wp_wc_wr_show_warranty_form');



add_action('admin_menu', 'wp_wc_wr_admin_page');
function wp_wc_wr_admin_page(){
  add_menu_page('Warranty Registration', 'Warranty', 'manage_options',  'wp-wc-warranty-registration',
    'wp_wc_wr_plugin_admin', 'dashicons-clipboard');
  add_submenu_page('wp-wc-warranty-registration', 'Warranty Registrations', 'Registrations', 'manage_options', 'wp_wc_wr_plugin_registrations', 'wp_wc_wr_plugin_registrations');
}

function wp_wc_wr_plugin_registrations(){
  require('admin/registrations.php');
}

function wp_wc_wr_plugin_admin(){
  require('admin/admin.php');
}