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
$wp_wc_wr_version = '1.1';


function wp_wc_warranty_registration_install(){
  global $wpdb, $wp_wc_wr_version;

  $table_name = $wpdb->prefix . 'wc_wr_registrations';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE $table_name (
          id INT NOT NULL AUTO_INCREMENT,
          user_id bigint(20) UNSIGNED NOT NULL,
          first_name varchar(128) NOT NULL,
          last_name varchar(128) NOT NULL,
          email varchar(128) NOT NULL,
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
function wp_wc_warranty_registration_uninstall() {
  global $wpdb, $wp_wc_wr_version;

  $table_name = $wpdb->prefix . 'wc_wr_registrations';
  delete_option('wp_wc_wr_version');
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

register_activation_hook(__FILE__, 'wp_wc_warranty_registration_install');
register_uninstall_hook(__FILE__, 'wp_wc_warranty_registration_uninstall');

function wp_wc_wr_generateUsername($firstname, $lastname, $email){
  $username = '';

  //check if email exists.
  $user_id = email_exists($email);
  if($user_id){
    $user = get_userdata($user_id);
    return $user->user_login;
  }
  $i = 1;
  $username = "$firstname.$lastname";
  $user_id = username_exists($username);
  while($user_id){
    $username .= "$i";
    $user_id = username_exists($username);
    $i++;
  }
  return $username;
}

function wp_wc_wr_show_warranty_form() {
  global $wpdb, $current_user;
  ob_start();
  wp_enqueue_style('wp-wc-warranty-registration', plugins_url('/warranty-registration.css', __FILE__));

  $autousername = get_option('wc-wp-wr-autousername');
  $registerusers = get_option('wc-wp-wr-registerusers');
  $firstname = filter_input(INPUT_POST, 'wr-firstname');
  $lastname = filter_input(INPUT_POST, 'wr-lastname');
  $email = sanitize_email(filter_input(INPUT_POST, 'wr-email'));

  if($autousername) {
    $username = wp_wc_wr_generateUsername($firstname, $lastname, $email);
  } else {
    $username = sanitize_user( filter_input(INPUT_POST, 'wr-username'));
  }

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
  $product_name = filter_input(INPUT_POST, 'wr-product-name');
  $serial_number = filter_input(INPUT_POST, 'wr-serialnumber');

  if($product_id == -1 ||($product_id == 'other' && !$product_name)){

    $error_code = 'no_product';
    $error = 'Please select a product';
  }

  if(isset($_POST['wr-submit']) && $_POST['wr-submit'] == 'Submit' && ($product_id || $product_name)){

    //process form
    if($registerusers){
      if($autousername) {
          $user = email_exists($email);

          if(!$user){
            $user = register_new_user($username, $email);
          }
      } else {
        if(!is_user_logged_in()) {
          $user = register_new_user( $username, $email );
        } else {
          $user = $current_user->ID;
        }
      }
    } else {
      $user = -1;
    }


    $_pf = new WC_Product_Factory();
    $product = $_pf->get_product($product_id);
    if ( ! is_wp_error( $user ) ) {

      //save user meta
      if($registerusers){
        update_user_meta($user, 'first_name', $firstname);
        update_user_meta($user, 'last_name', $lastname);
        update_user_meta($user, 'address', $address);
        update_user_meta($user, 'city', $city);
        update_user_meta($user, 'state', $state);
        update_user_meta($user, 'country', $country);
        update_user_meta($user, 'postalcode', $postalcode);
        update_user_meta($user, 'phone', $phone);
      }
      //save registration:
      if($product_id == 'other'){
        $product_id = -1;
      } else {
        $product_name = $product->post->post_title;
      }

      $ret = $wpdb->insert($wpdb->prefix . 'wc_wr_registrations', array(
        'user_id' => $user,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'serial_number' => $serial_number,
        'purchase_date' => date('Y-m-d', strtotime($purchasedate)),
        'purchase_location' => $location,
        'comments' => $comments,
        'created_date' => date('Y-m-d H:i:s'),
        'first_name' => $firstname,
        'last_name' => $lastname,
        'email' => $email,
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'country' => $country,
        'postal_code' => $postalcode,
        'phone' => $phone
      ));

      //send email
      $subject = get_option('wc-wp-wr-email-subject');
      $from = get_option('wc-wp-wr-email-from');
      $company = get_option('wc-wp-wr-company');
      $headers = "From: $company Warranty Registration <$from>\r\n";

      $msg_body_file = 'templates/warranty-registration-success-email.html';

      if(file_exists(get_template_directory()."/woocommerce/$msg_body_file")){
        $msg_body_file = get_template_directory()."/woocommerce/$msg_body_file";
      } else {
        $msg_body_file = plugins_url().'/wp-wc-warranty-registration/'.$msg_body_file;
      }

      $body = file_get_contents($msg_body_file);
      $body = str_replace("{{FIRST_NAME}}", $firstname, $body);
      $body = str_replace("{{LAST_NAME}}", $lastname, $body);
      $body = str_replace("{{COMPANY}}", $company, $body);
      $body = str_replace("{{PRODUCT}}", $product_name, $body);
      $body = str_replace("{{SERIAL_NUMBER}}", $serial_number, $body);
      $body = str_replace("{{PURCHASE_DATE}}", $purchasedate, $body);
      $body = str_replace("{{PURCHASE_LOCATION}}", $location, $body);
      $body = str_replace("{{COMMENTS}}", $comments, $body);

      $ret = wp_mail($email, $subject, $body, $headers);
    } else {
      $error_code = $user->get_error_code();

      $error = '<div class="error">'.$user->get_error_message().'</div>';
    }
  }
  if(isset($ret) && $ret){

    $thankyou_file = 'templates/warranty-registration-thank-you.php';
    if(file_exists(get_template_directory()."/woocommerce/$thankyou_file")){
      require_once(get_template_directory()."/woocommerce/$thankyou_file");
    } else {
      require_once($thankyou_file);
    }


    return ob_get_clean();
  }

  //option to login for existing users


  if(!is_user_logged_in()){
    echo "<p>If you are an existing user, login here. Otherwise fill out the section below.</p>";
    wp_login_form();
  }
    echo '<div ><form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="POST" class="wp-wc-warranty-registration">';

  switch($error_code){
    case 'email_exists':
    case 'username_exists':
      echo '<div class="error">Looks like you already have an account with us. You can login above, or if you forgot your password, you can reset it here: <a href="'.wp_lostpassword_url().'">reset password</a></div>';
      break;
    default:
      echo $error;
  }

    //show form
    echo '
  <fieldset>
  <legend>'. (is_user_logged_in() || $autousername?'About You':'New User').'  </legend>';

  if(!$autousername){
    echo '
    <div class="controls">
      <label for="username">Username *:</label>
      <input type="text" name="wr-username" id="username" required value="'. (is_user_logged_in() ? $current_user->display_name : $username) .'" />
    </div>

    ';
  }
    echo '
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
    $args = array('post_type' => 'product', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish');

    $categories = get_option('wc-wp-wr-categories');
    if(count($categories) !== 0){
      $args['tax_query'] = array();
      foreach($categories as $cat){
        $args['tax_query'][] = array('taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $cat);
      }
    }
    $products = get_posts($args);
    foreach($products as $p){
      echo '<option value="'.$p->ID.'" '.($p->ID == $product_id ? " selected " : "").' >'.$p->post_title.'</option>';
    }
    echo '<option value="other" '.($product_id == 'other' ? ' selected ' : '') .'>Other</option>';
  echo '

    </select>
  </div>
  <div class="controls" id="product_name_div" style="display:none;">
    <label for="product_name">Product: </label>
    <input id="product_name" name="wr-product-name" value="'.$product_name.'" />
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
';

  if(function_exists('mc4wp_form_is_submitted') && get_option('wc-wp-wr-newsletter')) {
  echo '
  <div class="controls">
    <label for="newsletter"><input type="checkbox" name="mc4wp-subscribe" value="1"/>Subscribe to newsletter</label>

  </div>';
  }
  echo '
  <input type="submit" value="Submit" name="wr-submit"/>
</form>
</div>
    ';

  wp_enqueue_script('wp-wc-warranty-registration', plugins_url('js/warranty-registration.js', __FILE__), array('angular'));


  return ob_get_clean();
}
add_shortcode('wp_wc_wr_form', 'wp_wc_wr_show_warranty_form');



add_action('admin_menu', 'wp_wc_wr_admin_page');
function wp_wc_wr_admin_page(){
  add_menu_page('Warranty Registration', 'Warranty', 'manage_options',  'wp-wc-warranty-registration',
    'wp_wc_wr_plugin_admin', 'dashicons-clipboard');
  add_submenu_page('wp-wc-warranty-registration', 'Warranty Registrations', 'Registrations', 'manage_options', 'wp_wc_wr_plugin_registrations', 'wp_wc_wr_plugin_registrations');
  add_submenu_page('wp-wc-warranty-registration', 'Warranty Registrations', 'Import', 'manage_options', 'wp_wc_wr_plugin_import', 'wp_wc_wr_plugin_import');
}

function wpdocs_set_html_mail_content_type() {
    return 'text/html';
}
add_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );

function wp_wc_wr_plugin_import(){
  require('admin/import.php');
}

function wp_wc_wr_plugin_registrations(){
  require('admin/registrations.php');
}

function wp_wc_wr_plugin_admin(){
  require('admin/admin.php');
}
