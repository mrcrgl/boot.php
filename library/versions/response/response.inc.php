<?php
/**
 * @package     Versions.core
 * @subpackage  Environment
 *
 */

/**
 * VResponse Class.
 *
 * This class serves to provide the Voomla Platform with a common interface to access
 * response variables.  This includes header and body.
 *
 * @package     Versions.core
 * @subpackage  Environment
 * @since       2.0
 */
class VResponse 
{
  /**
   * @var    array  Body
   * @since  2.0
   */
  protected static $body = array();

  /**
   * @var    boolean  Cachable
   * @since  2.0
   */
  protected static $cachable = false;

  /**
   * @var    array  Headers
   * @since  2.0
   */
  protected static $headers = array(
    array('name' => 'X-Framework', 'value' => "Versions 2.0")
  );

  protected static $http_codes = array(
    '200' => "OK",
    '301' => "Moved Permanently",
    '302' => "Found",
    '303' => "See Other",
    '304' => "Not Modified",
    '307' => "Temporary Redirect",
    '400' => "Bad Request",
    '403' => "Forbidden",
    '404' => "Not Found",
    '405' => "Method Not Allowed",
    '406' => "Not Acceptable"
  );

  /**
   * Set/get cachable state for the response.
   *
   * If $allow is set, sets the cachable state of the response.  Always returns current state.
   *
   * @param   boolean  $allow  True to allow browser caching.
   *
   * @return  boolean  True if browser caching should be allowed
   *
   * @since   2.0
   */
  public static function allowCache($allow = null)
   {
    if (!is_null($allow)) {
      self::$cachable = (bool) $allow;
    }

    return self::$cachable;
  }

  /**
   * Set a header.
   *
   * If $replace is true, replaces any headers already #defined with that $name.
   *
   * @param   string   $name     The name of the header to set.
   * @param   string   $value    The value of the header to set.
   * @param   boolean  $replace  True to replace any existing headers by name.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function setHeader($name, $value, $replace = false)
   {
    $name = (string) $name;
    $value = (string) $value;

    if ($replace) {
      foreach (self::$headers as $key => $header) {
        if ($name == $header['name']) {
          unset(self::$headers[$key]);
        }
      }
    }

    self::$headers[] = array('name' => $name, 'value' => $value);
  }

  /**
   * Return array of headers.
   *
   * @return  array
   *
   * @since   2.0
   */
  public static function getHeaders()
   {
    return self::$headers;
  }

  /**
   * Clear headers.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function clearHeaders()
   {
    self::$headers = array();
  }

  /**
   * Send all headers.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function sendHeaders()
   {
    if (!headers_sent()) {

      foreach (self::$headers as $header) {
        if ('status' == strtolower($header['name'])) {
          // 'status' headers indicate an HTTP status, and need to be handled slightly differently
          header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int) $header['value']);
        } else
        {
          header($header['name'] . ': ' . $header['value'], false);
        }
      }
    }
  }

  /**
   * Set body content.
   *
   * If body content already #defined, this will replace it.
   *
   * @param   string  $content  The content to set to the response body.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function setBody($content)
   {
    self::$body = array((string) $content);
  }

  /**
   * Prepend content to the body content
   *
   * @param   string  $content  The content to prepend to the response body.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function prependBody($content)
   {
    array_unshift(self::$body, (string) $content);
  }

  /**
   * Append content to the body content
   *
   * @param   string  $content  The content to append to the response body.
   *
   * @return  void
   *
   * @since   2.0
   */
  public static function appendBody($content)
   {
    array_push(self::$body, (string) $content);
  }

  /**
   * Return the body content
   *
   * @param   boolean  $toArray  Whether or not to return the body content as an array of strings or as a single string; defaults to false.
   *
   * @return  string  array
   *
   * @since   2.0
   */
  public static function getBody($toArray = false)
   {
    if ($toArray) {
      return self::$body;
    }

    ob_start();
    foreach (self::$body as $content) {
      echo $content;
    }

    return ob_get_clean();
  }

  /**
   * Sends all headers prior to returning the string
   *
   * @param   boolean  $compress  If true, compress the data
   *
   * @return  string
   *
   * @since   2.0
   */
  public static function toString($compress = false)
   {
    $data = self::getBody();

    // Don't compress something if the server is going to do it anyway. Waste of time.
    if ($compress && !ini_get('zlib.output_compression') && ini_get('output_handler') != 'ob_gzhandler') {
      $data = self::compress($data);
    }

    if (self::allowCache() === false) {
      self::setHeader('Cache-Control', 'no-cache', false);
      // HTTP 1.0
      self::setHeader('Pragma', 'no-cache');
    }

    self::sendHeaders();

    return $data;
  }

  public static function error($code=404, $message=null, $debug=null)
  {
    #print $code." thrown.".NL;

    self::setHeader("Status", $code);

    $oDocument =& VFactory::getDocument();
    $oRenderer =& $oDocument->getRenderer();
    #$oRenderer->init();

    #var_dump($oDocument->getRenderer()->getTemplateDir());

    $oDocument->assign('message', $message);
    if (!is_null($debug) && VSettings::f('default.debug')) {
      $oDocument->assign('debug', $debug);
    }
    $oDocument->setTemplate( sprintf("error/%d.htpl", (int)$code) );
    $oDocument->render();

    #var_dump($document);exit;

    self::setBody( $oDocument->getBody() );

    ob_clean();
    print self::toString(false);
    exit;
  }

  public static function redirect($to, $code=303)
  {
    if (!isset(self::$http_codes[$code])) return false;

    header(sprintf("HTTP/1.1 %d %s", $code, self::$http_codes[$code]));
    header(sprintf("Location: %s", $to));
    exit;
  }

  /**
   * Compress the data
   *
   * Checks the accept encoding of the browser and compresses the data before
   * sending it to the client.
   *
   * @param   string  $data  Content to compress for output.
   *
   * @return  string  compressed data
   *
   * @note    Replaces _compress method in 2.0
   * @since   2.0
   */
  protected static function compress($data)
   {
    $encoding = self::clientEncoding();

    if (!VSettings::f('default.use_gzip')) {
        return $data;
    }
    
    if (!$encoding) {
      return $data;
    }

    if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
      return $data;
    }

    if (headers_sent()) {
      return $data;
    }

    if (connection_status() !== 0) {
      return $data;
    }

    // Ideal level
    $level = 4;

    /*
    $size    = strlen($data);
    $crc    = crc32($data);

    $gzdata    = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
    $gzdata    .= gzcompress($data, $level);

    $gzdata  = substr($gzdata, 0, strlen($gzdata) - 4);
    $gzdata  .= pack("V",$crc) . pack("V", $size);
    */

    $gzdata = gzencode($data, $level);

    self::setHeader('Content-Encoding', $encoding);
    self::setHeader('X-Content-Encoded-By', 'Versions 2.0');

    return $gzdata;
  }

  /**
   * Check, whether client supports compressed data
   *
   * @return  boolean
   *
   * @since   2.0
   * @note    Replaces _clientEncoding method from 2.0
   */
  protected static function clientEncoding()
   {
    if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
      return false;
    }

    $encoding = false;

    if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
      $encoding = 'gzip';
    }

    if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
      $encoding = 'x-gzip';
    }

    return $encoding;
  }
}
