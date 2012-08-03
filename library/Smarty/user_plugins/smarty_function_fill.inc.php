<?php 

function smarty_function_fill($params, &$smarty) {
  if (empty($params['char'])) {
    $params['char'] = "-";
  }
  if (empty($params['count'])) {
    $params['count'] = 0;
  }
  $out = "";
  for ($i=0;$i<=$params['count'];$i++) {
    $out .= $params['char'];
  }
  return $out;
}