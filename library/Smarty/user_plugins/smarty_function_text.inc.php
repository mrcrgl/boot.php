<?php 

function smarty_function_text($params, &$smarty)
 {
  $ident = $params['ident'];
  unset($params['ident']);
  return $ident;
  $return = Text::_($ident, $params);
  
  if ($params['capitalize']) {
    $return = ucwords($return);
  }
  
  return $return;
}