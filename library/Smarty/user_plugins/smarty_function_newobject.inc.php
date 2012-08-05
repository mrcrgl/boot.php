<?php 

function smarty_function_newobject($params, &$smarty) {
  if (empty($params['var'])) {
    return "Missing Parameter: 'var'!";
  }
  if (empty($params['object'])) {
    return "Missing Parameter: 'object'!";
  }
  $object = $params['object'];
  $param  = false;
  if (!isset($params['param']) || !empty($params['param'])) {
    $param = $params['param'];
  }
  
  $smarty->assign($params['var'], new $object($param));
  
  return "";
}