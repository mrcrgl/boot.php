<?php
/**
 * Layout:
 *   locale
 *     component
 *       _ftime : fetched timestamp
 *       _mtime : last modified timestamp
 *       md5 => (
 *         t = Text
 *         p = Plural version
 *       )
 *
 *
 * @author marc
 *
 */
class VLocalizationDefault extends VLocalization {

  /**
   * The users locale
   *
   * @var unknown_type
   */
  //static $locale = 'de';

  /**
   * Default locale of code and fallback
   *
   * @var unknown_type
   */
  var $fallback_locale = null;

  /**
   * Namespace for storage
   *
   * @var string
   */
  var $ns = 'i18n';

  /**
   * Key Seperator for Storage
   *
   * @var string
   */
  var $key_sep = ';';

  /**
   * The current component
   *
   * @var string
   */
  var $component = 'default';

  /**
   * Path for Translation files
   *
   * @var string
   */
  var $file_path = null; //'./translations';

  /**
   * Storage file type
   *
   * @var string
   */
  var $file_type = 'json';

  /**
   * Storage file extension
   *
   * @var string
   */
  var $file_ext = '.i18n.json';

  /*public function _($text) {
   return $this->translate($text);
  }*/

  public function __construct() {

    $this->set('file_path', PROJECT_CONFIG.DS.'translations');
    $this->set('fallback_locale', VSettings::f('localization.default_locale', 'en'));
    $this->set('ns', $_SERVER['HTTP_HOST']); // Set to domain, in case of multiple vhosts

    if (!$this->hasCache()) {
      $this->loadFiles();
    }

    parent::__construct();
  }

  public function _($text, $options=array()) {

    $plural = null;
    $none = null;

    if (isset($options['plural'])) {
      $plural = $options['plural'];
      unset($options['plural']);
    }

    if (isset($options['none'])) {
      $none = $options['none'];
      unset($options['none']);
    }

    if (isset($options['count'])) {
      $count = $options['count'];
    } else {
      $count = 0;
    }

    $ct = array('t' => &$text, 'p' => &$plural, 'n' => &$none);
    $ct = $this->translate($ct);

    if (!is_null($ct['n']) && $count == 0) {
      return $this->parse($ct['n'], $options);
    }
    elseif (!is_null($ct['p']) && $count < 1) {
      return $this->parse($ct['p'], $options);
    }
    else {
      return $this->parse($ct['t'], $options);
    }
  }

  private function parse($text, $options) {
    if (count($options)) {
      foreach ($options as $key => $value) {
        #print $key;
        $text = str_replace('%'.$key, $value, $text);
      }
    }
    return $text;
  }

  public function translate($ct) {
    $h = $this->getHash($ct);
    $k = $this->getKey($this->get('user_locale', $this->get('fallback_locale')));

    // check if requested locale exists
    if ($this->inCache($h, $k)) {
      #print "[cached locale]";
      return $this->getText($ct, $h, $k);
    }

    // check if fallback locale exists
    $k = $this->getKey();
    if ($this->inCache($h, $k)) {
      #print "[cached fallback_locale]";
      return $this->getText($ct, $h, $k);
    }

    return $this->storeCache(&$ct, $h, $k);
  }

  public function record() {
    $this->saveFiles();
  }

  private function getText(&$ct, $h=null, $k=null) {
    if (is_null($h)) {
      $h = $this->getHash($ct);
    }
    if (is_null($k)) {
      $k = $this->getKey();
    }

    $c = $this->getCache();
    if (isset($c[$k][$h]))
      return $c[$k][$h];

    return $this->storeCache(&$ct, $h, $k);
  }

  public function storeCache(&$ct, $h=null, $k=null) {
    #print "[stored]";

    if (is_null($h)) {
      $h = $this->getHash($ct);
    }
    if (is_null($k)) {
      $k = $this->getKey();
    }

    $c =& $this->getCache();

    if (!isset($c[$k]))
      $c[$k] = array();

    $c[$k][$h] = $ct;

    $this->setCache($c);

    #    var_dump($c);

    return $text;
  }

  public function getCache() {
    if (!apc_exists($this->get('ns')))
      apc_store($this->get('ns'), array());

    return apc_fetch($this->get('ns'));
  }

  public function hasCache() {
    return apc_exists($this->get('ns'));
  }

  public function setCache($c) {
    apc_store($this->get('ns'), $c);
  }

  public function clearCache() {
    apc_delete($this->get('ns'));
  }

  public function inCache($h, $k) {
    $c = $this->getCache();
    #var_dump($c);
    return (bool)isset($c[$k][$h]);
  }

  public function saveFiles($c=null) {
    if (null === $c) {
      $c = $this->getCache();
    }

    if (!is_dir($this->get('file_path')))
      mkdir($this->get('file_path'));

    foreach ($c as $k => $data) {
      list($loc, $com) = explode($this->get('key_sep'), $k);

      if (!$loc || !$com) continue;

      if (!is_dir($this->get('file_path').DS.$loc))
        mkdir($this->get('file_path').DS.$loc);

      $filename = $this->get('file_path').DS.$loc.DS.$com.$this->get('file_ext');

      file_put_contents($filename, $this->encode($data));
    }
  }

  public function loadFiles($return=false) {

    if (!$return)
      $this->clearCache();

    if (!is_dir($this->get('file_path')))
      return false;

    $locs = scandir($this->get('file_path'));

    if (!$return) {
      $c = $this->getCache();
    } else {
      $c = array();
    }


    foreach ($locs as $loc) {
      if (in_array($loc, array('.', '..'))) continue;


      $coms = scandir($this->get('file_path').DS.$loc);
      foreach ($coms as $com) {
        $file = $this->get('file_path').DS.$loc.DS.$com;
        if (!is_file($file)) continue;
        if (!substr($file, -strlen($this->get('file_ext')))) continue;

        $component = substr($com, 0, -strlen($this->get('file_ext')));

        $data = $this->decode(file_get_contents($file));



        $k = $this->getKey($loc, $component);
        $c[$k] = $data;


      }

    }

    if (!$return) {
      $this->setCache($c);
    } else {
      return $c;
    }

    #$filename = $this->get('file_path').DS.$loc.DS.$com.$this->get('file_ext');

  }

  public function encode($data) {
    switch($this->get('file_type')) {
      case "json":
        return json_encode($data);
    }
  }

  public function decode($data) {
    switch($this->get('file_type')) {
      case "json":
        return json_decode($data, true);
    }
  }

  public function getKey($locale=null, $component=null) {
    if (is_null($locale))
      $locale =& $this->get('fallback_locale');

    return $locale.$this->get('key_sep').((is_null($component)) ? $this->get('component') : $component);
  }

  public function getHash(&$ct) {
    return md5($ct['t']);
  }
}