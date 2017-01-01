<?php
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
          if($item['user_id'] != -1)
            return "{$user->first_name} {$user->last_name}";
          else
            return $item['first_name'] . ' ' . $item['last_name'];
        case 'product':
          return $item['product_name'];
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

      $query = "SELECT r.* FROM {$wpdb->prefix}wc_wr_registrations r";

      $_orderby = filter_input(INPUT_GET, 'orderby');
      $orderby = !empty($_orderby) ? $wpdb->_real_escape($_orderby) : 'ASC';
      $_order = filter_input(INPUT_GET, 'order');
      $order = !empty($_order) ? $wpdb->_real_escape($_orderby) : '';
      $search = filter_input(INPUT_POST, 's');
      if(!empty($search) && $search != '') {
        $search = "%$search%";
        $query .= " INNER JOIN {$wpdb->prefix}users u ON (u.ID = r.user_id)
                      LEFT OUTER JOIN {$wpdb->prefix}usermeta um_first_name ON (um_first_name.user_id = r.user_id AND um_first_name.meta_key = 'fist_name')
                      LEFT OUTER JOIN {$wpdb->prefix}usermeta um_last_name ON (um_last_name.user_id = r.user_id AND um_last_name.meta_key = 'last_name')";
        $query .= $wpdb->prepare(" WHERE  (u.user_login LIKE %s OR u.user_nicename LIKE %s
                            OR u.user_email LIKE %s OR u.display_name LIKE %s
                            OR r.serial_number LIKE %s OR um_first_name.meta_value LIKE %s
                            OR um_last_name.meta_value LIKE %s)
                            OR r.email LIKE %s OR r.first_name LIKE %s OR r.last_name LIKE %s",
                        $search, $search, $search, $search, $search, $search, $search, $search, $search, $search);

      }
      if(!empty($orderby) & !empty($order)){
        $query.=' ORDER BY '.$orderby.' '.$order;
      } else {
        $query .= ' ORDER BY r.id DESC ';
      }

      $totalitems = $wpdb->query($query);
      $perpage = 20;

      $_paged = filter_input(INPUT_GET, 'paged');
      $paged = !empty($_paged) ? $wpdb->_real_escape($_paged) :'';
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

  }
?>
<div class="wrap">
  <h2>Warranty Registrations</h2>

  <h3></h3>
  <div>

    <?php
      $table = new WCRegistrationsTable();
      $table->prepare_items();
      echo '<form method="post"><p class="search-box">Search registrations by name or serial number</p><br/><br/>';
      $table->search_box('Search', 'search_id');
      echo '</form>';
      $table->display();
    ?>

  </div>
</div>
