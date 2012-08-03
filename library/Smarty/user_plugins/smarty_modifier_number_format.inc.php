<?php 

function smarty_modifier_number_format($iNumber, $iDecPlaces=0, $sDecSep=',', $sThSep='.') {
  return number_format($iNumber, $iDecPlaces, $sDecSep, $sThSep);
}