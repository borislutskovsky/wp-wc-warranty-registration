<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 1/8/2016
 * Time: 10:56 AM
 */


  if(isset($_POST['wc-wr-submit-settings']) && $_POST['wc-wr-submit-settings'] == 'Submit'){
    update_option('wc-wp-wr-newsletter', filter_input(INPUT_POST, 'wc-wp-wr-newsletter'));


  }
$newsletter = get_option('wc-wp-wr-newsletter');
?>
<div class="wrap">
  <h2>Warranty Registration Administration</h2>

  <h3>Settings:</h3>
  <form method="POST">
    <div class="controls">
      <label for="newsletter">
        <input type="checkbox" name="wc-wp-wr-newsletter" <?php if($newsletter) echo ' checked'; ?> />Show newsletter subscription box
      </label>
    </div>
    <input type="submit" name="wc-wr-submit-settings"/>
  </form>
</div>
