(function(){
  jQuery(document).ready(function(){
    //event handler for product selector
    if(jQuery('select#product').val() == 'other'){
      jQuery('div#product_name_div').show();
    }

    jQuery('select#product').change(function(){
      if(jQuery(this).val() == 'other'){
        jQuery('div#product_name_div').show();
      } else {
        jQuery('div#product_name_div').hide();
      }
    });
  });
})();