<?php 

function smarty_function_sentence($params, &$smarty) {
  $ident = $params['ident'];
  unset($params['ident']);
  array_unshift($params, $ident);
  
  
  $return = call_user_func_array(array('Text', 'sentence'), $params);
  
  if ($params['capitalize']) {
  	$return = ucwords($return);
  }
  
  return $return;
	#return call_user_func_array('Text::sentence', $param_arr);
}