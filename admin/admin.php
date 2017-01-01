<?php


  if(isset($_POST['wc-wr-submit-settings']) && $_POST['wc-wr-submit-settings'] == 'Submit'){
    update_option('wc-wp-wr-newsletter', filter_input(INPUT_POST, 'wc-wp-wr-newsletter'));
    update_option('wc-wp-wr-email-subject', filter_input(INPUT_POST, 'wc-wp-wr-email-subject'));
    update_option('wc-wp-wr-email-from', filter_input(INPUT_POST, 'wc-wp-wr-email-from'));
    update_option('wc-wp-wr-company', filter_input(INPUT_POST, 'wc-wp-wr-company'));
    update_option('wc-wp-wr-autousername', filter_input(INPUT_POST, 'wc-wp-wr-autousername'));
    update_option('wc-wp-wr-categories',filter_input(INPUT_POST, 'wc-wp-wr-categories',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));
    update_option('wc-wp-wr-registerusers', filter_input(INPUT_POST, 'wc-wp-wr-registerusers'));
  }


$newsletter = get_option('wc-wp-wr-newsletter');
$email_subject = get_option('wc-wp-wr-email-subject');
$email_from = get_option('wc-wp-wr-email-from');
$company = get_option('wc-wp-wr-company');
$autousename = get_option('wc-wp-wr-autousername');
$categories = get_option('wc-wp-wr-categories');
$registerusers = get_option('wc-wp-wr-registerusers');

$site_title = get_bloginfo();
if($email_subject == ''){
  $email_subject = $site_title . ' - Your Warranty Registration';
}

?>

<div class="wrap warranty-registration">
  <h2>Warranty Registration Administration</h2>

  <h3>Settings:</h3>
  <form method="POST">
    <table class="form-table">
      <tbody>
        <tr class="form-field">
          <th scope="row"><label for="wc-wp-wr-company">Company Name</label></th>
          <td><input type="text" name="wc-wp-wr-company" value="<?php echo $company; ?>"></td>
        </tr>
        <tr class="form-field">
          <th scope="row"><label for="wc-wp-wr-email-subject">Email Subject</label></th>
          <td><input type="text" name="wc-wp-wr-email-subject" value="<?php echo $email_subject; ?>" /></td>
        </tr>
        <tr class="form-field">
          <th scope="row"><label for="wc-wp-wr-email-from">Email From</label></th>
          <td><input type="text" name="wc-wp-wr-email-from" value="<?php echo $email_from; ?>"  /></td>
        </tr>
        <tr class="form-field">
          <th scope="row">Show newsletter subscription box</th>
          <td><input type="checkbox" name="wc-wp-wr-newsletter" <?php if($newsletter) echo ' checked'; ?> /></td>
        </tr>
        <tr class="form-field">
          <th scope="row">Register users</th>
          <td><input type="checkbox" name="wc-wp-wr-registerusers" <?php if($registerusers) echo ' checked '; ?>></td>
        </tr>
        <tr class="form-field">
          <th scope="row">Auto-generate username</th>
          <td><input type="checkbox" name="wc-wp-wr-autousername" <?php if($autousename) echo ' checked '; ?> /></td>
        </tr>
        <tr class="form-field">
          <th scope="row"><label for="wc-wp-wr-categories">Include Categories</label></th>
          <td><select name="wc-wp-wr-categories[]" id="" multiple>

          <?php
            $taxonomy     = 'product_cat';
            $orderby      = 'name';
            $show_count   = 0;      // 1 for yes, 0 for no
            $pad_counts   = 0;      // 1 for yes, 0 for no
            $hierarchical = 1;      // 1 for yes, 0 for no
            $title        = '';
            $empty        = 0;

            $args = array(
                   'taxonomy'     => $taxonomy,
                   'orderby'      => $orderby,
                   'show_count'   => $show_count,
                   'pad_counts'   => $pad_counts,
                   'hierarchical' => $hierarchical,
                   'title_li'     => $title,
                   'hide_empty'   => $empty
            );
           $all_categories = get_categories( $args );
           foreach($all_categories as $cat){
             $selected = array_search($cat->slug, $categories);

             echo '<option value="'.$cat->slug.'"'. ($selected !== FALSE ? ' selected ' : '') . '>'.$cat->name.'</option>';
           }
?>

          </select></td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="wc-wr-submit-settings" class="button button-primary"/>
    </p>
  </form>
</div>
