<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 1/8/2016
 * Time: 1:02 PM
 */

  class WCRegistrationsTable extends WP_List_Table {
    function __construct() {
      parent::__construct(array(
        'singular' => 'Registration',
        'plural' => 'Registrations',
        'ajax' => true
      ));
    }

    function get_columns() {
      return $columns = array(
        'col_registration_id' => __('ID'),
        'col_user' => __('User'),
        'col_product' => __('Product'),
        'col_serial_number' => __('Serial Number'),
        'col_purchase_date' => __('Purchase Date'),
        'col_purchase_location' => __('Purchase Location')

      );
    }

    public function get_sortable_columns() {
     return $sortable = array(

     );
    }
  }
?>
<div class="wrap">
  <h2>Warranty Registrations</h2>

  <h3></h3>

</div>

