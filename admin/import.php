<?php
  if(isset($_POST['wr-import-submit']) && $_POST['wr-import-submit'] == 'Submit'){
    //file uploaded
  }

?>

<div class="wrap">
  <h2>Warranty Registrations - Import Data</h2>

  <h3></h3>
  <div>

    <form method="POST">
      <p>Upload a tab delimited file with the following columns:</p>
      <p><ul>
        <li>product_name</li>
        <li>purchase_date</li>
        <li>purchase_location</li>
        <li>comments</li>
        <li>first_name</li>
        <li>last_name</li>
        <li>email</li>
        <li>address</li>
        <li>address_city</li>
        <li>address_state</li>
        <li>address_postal_code</li>
        <li>address_country</li>
        <li>phone</li>
      </ul></p>
      <label for="file">File:</label>
      <input type="file" name="reg_import"/>
      <input type="submit" value="wr-import-submit"/>
    </form>

  </div>
</div>