<?php

class ComponentHelperViewTranslation extends BApplicationView 
{

    public function show()
 {
    $oDocument =& BFactory::getDocument();
      $input    =& BFactory::getInput();

      $oDocument->setTemplate('translation/index.htpl');

      if (BSettings::f('localization.engine', 'none') == 'none') {
        BMessages::_("Localization not enabled!", "ini file jedoens", 'info');
        $oDocument->assign('is_disabled', true);
      }

    $localization =& BLocalization::getInstance();
    $separator    = $localization->get('key_sep');

    $enabled_locales = $localization->enabled;
    $default_locale  = $localization->fallback_locale;

    $translate_to = array();
    foreach ($enabled_locales as $el) {
      if ($el != $default_locale) $translate_to[] = $el;
    }

    $from = $oInput->get('from', null, 'get');
    $to   = $oInput->get('to', null, 'get');

    if (is_null($from) || is_null($to)) {
      BResponse::redirect(sprintf('%s?from=%s&to=%s', $_SERVER['REDIRECT_URL'], $default_locale, $translate_to[0]));
    }

    $data = array();
    foreach ($localization->loadFiles(true) as $key => $value) {
      list($lang, $area) = explode($separator, $key);

      $data[$lang][$area] = $value;
    }

    $from_data = $data[$from];
    $to_data = ((isset($data[$to])) ? $data[$to] : array());
    foreach ($from_data as $a_key => $a_data) {
      foreach ($a_data as $k => $v) {
        if (!isset($to_data[$a_key])) {
          $to_data[$a_key] = array();
        }
        if (!isset($to_data[$a_key][$k])) {
          $to_data[$a_key][$k] = array('t' => "", 'p' => "", 'n' => "");
        }
      }
    }

    $oDocument->assign('from', $from);
    $oDocument->assign('to', $to);
    $oDocument->assign('from_data', $from_data);
    $oDocument->assign('to_data', $to_data);
    }

    public function save()
    {
      $oDocument =& BFactory::getDocument();
      $input    =& BFactory::getInput();


      $localization =& BLocalization::getInstance();
      $separator    = $localization->get('key_sep');

      $to   = $oInput->get('to', null, 'post');

      // the cache
      $c = array();

      foreach (array_keys($_POST) as $key) {
      if (substr($key, 0, 1) != '_') continue;
      $area = substr($key, 1);

      $area_data = $oInput->get($key, array(), 'post');

      foreach ($area_data as $hash => $ct) {
        if (!isset($ct['t']) || strlen($ct['t']) <= 0) {
          unset($area_data[$hash]);
        } else {
          if (!isset($ct['p'])) {
            $area_data[$hash]['p'] = null;
          }
          if (!isset($ct['n'])) {
            $area_data[$hash]['n'] = null;
          }
        }
      }

      #var_dump($area_data);

      $ckey = $to.$separator.$area;
      $c[$ckey] = $area_data;
      }

      $localization->saveFiles($c);

      BMessages::_("Success", "Translation saved!", "success");
      BResponse::redirect($_SERVER['HTTP_REFERER']);
    }

    public function flush_cache()
    {
      $localization =& BLocalization::getInstance();
      $localization->clearCache();

      BMessages::_("Success", "Cache cleared!", "success");
      BResponse::redirect($_SERVER['HTTP_REFERER']);
    }
}