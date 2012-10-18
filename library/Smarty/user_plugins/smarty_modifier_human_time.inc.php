<?php 

function smarty_modifier_human_time($iNumber)
 {
  if (Instance::f('settings')->date_format) {
    date_format();
  }
  
  return number_format($iNumber, $iDecPlaces, $sDecSep, $sThSep);
}