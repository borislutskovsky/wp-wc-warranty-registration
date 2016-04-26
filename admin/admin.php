<?php


  if(isset($_POST['wc-wr-submit-settings']) && $_POST['wc-wr-submit-settings'] == 'Submit'){
    update_option('wc-wp-wr-newsletter', filter_input(INPUT_POST, 'wc-wp-wr-newsletter'));
    update_option('wc-wp-wr-email-subject', filter_input(INPUT_POST, 'wc-wp-wr-email-subject'));
    update_option('wc-wp-wr-email-from', filter_input(INPUT_POST, 'wc-wp-wr-email-from'));

  }
  

$newsletter = get_option('wc-wp-wr-newsletter');
$email_subject = get_option('wc-wp-wr-email-subject');
$email_from = get_option('wc-wp-wr-email-from');

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
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="wc-wr-submit-settings" class="button button-primary"/>
    </p>
  </form>
</div>
