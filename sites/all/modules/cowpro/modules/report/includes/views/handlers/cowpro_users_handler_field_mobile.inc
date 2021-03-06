<?php

class cowpro_users_handler_field_mobile extends views_handler_field {
  function render($values) {
  	$result = '';
    $uid = $this->get_value($values);

    $profile = cowpro_customer_profile_load_one_row_with_conditions($uid, 'mobile');
    if ($profile) {
    	$result = $profile->mobile_number;
    } else {
    	$result = '';
    }
    return $result;
  }
}
