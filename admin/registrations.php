<?php
/**
 * Created by PhpStorm.
 * User: Boris
 * Date: 1/8/2016
 * Time: 1:02 PM
 */

  class WCRegistrationsTable extends WP_List_Table {

    var $productFactory;
    function __construct() {
      $this->productFactory = new WC_Product_Factory();
      parent::__construct(array(
        'singular' => 'Registration',
        'plural' => 'Registrations',
        'ajax' => true
      ));
    }

    public function get_hidden_columns()
    {
      return array();
    }
    function get_columns() {

      return $columns = array(
        'registration_id' => __('ID'),
        'user' => __('User'),
        'product' => __('Product'),
        'serial_number' => __('Serial Number'),
        'purchase_date' => __('Purchase Date'),
        'purchase_location' => __('Purchase Location')

      );
    }

    public function get_sortable_columns() {
     return $sortable = array(
       'col_registration_id' => 'id',
       'col_user' => 'user_id',
       'col_product_id' => 'product_id',
       'col_purchase_date' => 'purchase_date'

     );
    }

    public function column_default( $item, $column_name ) {
      switch($column_name){
        case 'registration_id':
          return $item['id'];
        case 'user':
          $user = new WP_User($item['user_id']);
          return "{$user->first_name} {$user->last_name}";
        case 'product':
          $product = $this->productFactory->get_product($item['product_id']);
          if($product)
            return $product->get_formatted_name();
          else
            return var_dump($product);

        default:
          return $item[$column_name];
      }
    }

    function prepare_items() {
      global $wpdb;
      $columns = $this->get_columns();
      $hidden = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();


      $this->_column_headers = array($columns, $hidden, $sortable);

      $query = "SELECT * FROM {$wpdb->prefix}wc_wr_registrations";

      $_orderby = filter_input(INPUT_GET, 'orderby');
      $orderby = !empty($_orderby) ? mysqli_real_escape_string($_orderby) : 'ASC';
      $_order = filter_input(INPUT_GET, 'order');
      $order = !empty($_order) ? mysqli_real_escape_string($_orderby) : '';
      if(!empty($orderby) & !empty($order)){
        $query.=' ORDER BY '.$orderby.' '.$order;
      }

      $totalitems = $wpdb->query($query);
      $perpage = 20;

      $_paged = filter_input(INPUT_GET, 'paged');
      $paged = !empty($_paged) ? mysqli_real_escape_string($_paged) :'';
      if(empty($paged) || !is_numeric($paged) || $paged <= 0) { $paged = 1;}
      $totalpages = ceil($totalitems/$perpage);

      if(!empty($paged) && !empty($perpage)){
        $offset = ($paged -1 )*$perpage;
        $query .= " LIMIT $offset, $perpage";
      }

      $this->set_pagination_args(array(
        'total_items' => $totalitems,
        'total_pages' => $totalpages,
        'per_page' => $perpage
      ));

      $this->items = $wpdb->get_results($query, ARRAY_A);

    }


//    function display_rows() {
//      $records = $this->items;
//
//      $columns = $this->get_columns();
//      $hidden = $this->get_hidden_columns();
//      $ret = '';
//      if(!empty($records)){
//        foreach($records as $rec){
//          $ret .= '<tr id="record_'.$rec->id.'">';
//          foreach ( $columns as $column_name => $column_display_name ) {
//
//            //Style attributes for each col
//            $class = "class='$column_name column-$column_name'";
//            $style = "";
//            if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
//            $attributes = $class . $style;
//
//            //edit link
//            $editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->id;
//
//            //Display the cell
//            switch ( $column_name ) {
//              case "registration_id":  $ret .= '< td '.$attributes.'>'.stripslashes($rec->id).'< /td>';   break;
//              case "user": $ret .= '< td '.$attributes.'>'.stripslashes($rec->user_id).'< /td>'; break;
//              case "product": $ret .= '< td '.$attributes.'>'.stripslashes($rec->product_id).'< /td>'; break;
//              case "serial_number": $ret .= '< td '.$attributes.'>'.$rec->serial_number.'< /td>'; break;
//              case "purchase_date": $ret .= '< td '.$attributes.'>'.$rec->purchase_date.'< /td>'; break;
//            }
//          }
//
//          //Close the line
//          $ret .= '</tr>';
//        }
//      }
//      return $ret;
//    }

  }
?>
<div class="wrap">
  <h2>Warranty Registrations</h2>

  <h3></h3>
  <div>
    <?php
      $table = new WCRegistrationsTable();
      $table->prepare_items();

      $table->display();
    ?>

  </div>
</div>

