<?php

function smarty_function_global($params, &$smarty) {
  static $GLOBALS; 
  
  $var = $params['var'];

  if (!$var || (!isset($GLOBALS[$var]) && !isset($params['value']))) {
    return null;
  }
  
  if (isset($params['value'])) {
    $GLOBALS[$var] = $params['value'];
  }
  
  if (isset($params['assign'])) {
    #print "assign: ".$GLOBALS[$var];
    $smarty->assign($params['assign'], $GLOBALS[$var]);
    return null;
  }
  return ((is_object($GLOBALS[$var])) ? null : $GLOBALS[$var]);
}