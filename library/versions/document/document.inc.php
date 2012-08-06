<?php


class VDocument {
	
	/**
	 * Document title
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $title = '';

	/**
	 * Document description
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $description = '';

	/**
	 * Document project name
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $project_name = 'Versions 2.0 - Project';
	
	/**
	 * Document full URL
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $link = '';

	/**
	 * Document base URL
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $base = '';
	
	/**
	 * Document url_prefix URL Prefix
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $url_prefix = '';
	
	/**
	 * Contains the document language setting
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $language = 'de-de';

	/**
	 * Document generator
	 *
	 * @var    string
	 */
	public $_generator = 'Versions 2.0';

	/**
	 * Document modified date
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_mdate = '';

	/**
	 * Tab string
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_tab = "\11";

	/**
	 * Contains the line end string
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_lineEnd = "\12";

	/**
	 * Contains the character encoding string
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_charset = 'utf-8';

	/**
	 * Contains the document author
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_author = '';
	
	/**
	 * Document mime type
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_mime = '';

	/**
	 * Array of linked scripts
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $_scripts = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $_script = array();

	/**
	 * Array of linked style sheets
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $_styleSheets = array();

	/**
	 * Array of included style declarations
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $_style = array();

	/**
	 * Array of meta tags
	 *
	 * @var    array
	 * @since  2.0
	 */
	public $_metaTags = array();

	/**
	 * The rendering engine
	 *
	 * @var    object
	 * @since  2.0
	 */
	public $_engine = null;

	/**
	 * The document type
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_type = null;
	
	/**
	 * The document renderer reference
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $_renderer = null;
	
	public $_body = null;
	
	public function __construct() {
		
	}
	
	public static function getInstance($type='html') {
		
		$classname = 'VDocument'.ucfirst($type);
		
		VLoader::autoload($classname);
		
		if (!class_exists($classname)) {
			throw new Exception( sprintf('Document engine %s not found. Exiting...', $classname) );
			//user_error()
		}
		
		return new $classname();
	}
	
	public function getRenderer($type='smarty') {
		
		if (!$this->_renderer) {
		
			$classname = 'VDocumentRenderer'.ucfirst($this->getType()).ucfirst($type);
			
			$path = dirname(__FILE__).DS.$this->getType().DS.'renderer'.DS.$type.'.inc.php';
			
			VLoader::register($classname, $path);
			VLoader::autoload($classname);
			
			if (!class_exists($classname)) {
				throw new Exception( sprintf('Document renderer %s not found. Exiting...', $classname) );
				//user_error()
			}
			
			$this->_renderer = new $classname();
		}
		
		return $this->_renderer;
	}
	
	/**
	 * Set the document type
	 *
	 * @param   string  $type  Type document is to set to
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setType($type) {
		$this->_type = $type;

		return $this;
	}

	/**
	 * Returns the document type
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * Gets a meta tag.
	 *
	 * @param   string   $name       Value of name or http-equiv tag
	 * @param   boolean  $httpEquiv  META type "http-equiv" defaults to null
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getMetaData($name, $httpEquiv = false) {
		$result = '';
		$name = strtolower($name);
		if ($name == 'generator') {
			$result = $this->getGenerator();
		}
		elseif ($name == 'description') {
			$result = $this->getDescription();
		}
		else {
			if ($httpEquiv == true) {
				$result = @$this->_metaTags['http-equiv'][$name];
			}
			else {
				$result = @$this->_metaTags['standard'][$name];
			}
		}

		return $result;
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param   string   $name        Value of name or http-equiv tag
	 * @param   string   $content     Value of the content tag
	 * @param   boolean  $http_equiv  META type "http-equiv" defaults to null
	 * @param   boolean  $sync        Should http-equiv="content-type" by synced with HTTP-header?
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setMetaData($name, $content, $http_equiv = false, $sync = true) {
		$name = strtolower($name);

		if ($name == 'generator') {
			$this->setGenerator($content);
		}
		elseif ($name == 'description') {
			$this->setDescription($content);
		}
		else {
			if ($http_equiv == true) {
				$this->_metaTags['http-equiv'][$name] = $content;

				// Syncing with HTTP-header
				if ($sync && strtolower($name) == 'content-type') {
					$this->setMimeEncoding($content, false);
				}
			}
			else {
				$this->_metaTags['standard'][$name] = $content;
			}
		}

		return $this;
	}
	
	/**
	 * Adds a linked script to the page
	 *
	 * @param   string   $url    URL to the linked script
	 * @param   string   $type   Type of script. Defaults to 'text/javascript'
	 * @param   boolean  $defer  Adds the defer attribute.
	 * @param   boolean  $async  Adds the async attribute.
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function addScript($url, $type = "text/javascript", $defer = false, $async = false) {
		$this->_scripts[$url]['mime'] = $type;
		$this->_scripts[$url]['defer'] = $defer;
		$this->_scripts[$url]['async'] = $async;

		return $this;
	}

	/**
	 * Adds a script to the page
	 *
	 * @param   string  $content  Script
	 * @param   string  $type     Scripting mime (defaults to 'text/javascript')
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function addScriptDeclaration($content, $type = 'text/javascript') {
		if (!isset($this->_script[strtolower($type)])) {
			$this->_script[strtolower($type)] = $content;
		}
		else {
			$this->_script[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param   string  $url      URL to the linked style sheet
	 * @param   string  $type     Mime encoding type
	 * @param   string  $media    Media type that this stylesheet applies to
	 * @param   array   $attribs  Array of attributes
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array()) {
		$this->_styleSheets[$url]['mime'] = $type;
		$this->_styleSheets[$url]['media'] = $media;
		$this->_styleSheets[$url]['attribs'] = $attribs;

		return $this;
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param   string  $content  Style declarations
	 * @param   string  $type     Type of stylesheet (defaults to 'text/css')
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function addStyleDeclaration($content, $type = 'text/css') {
		if (!isset($this->_style[strtolower($type)])) {
			$this->_style[strtolower($type)] = $content;
		}
		else {
			$this->_style[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Sets the document charset
	 *
	 * @param   string  $type  Charset encoding string
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setCharset($type = 'utf-8') {

		$this->_charset = $type;

		return $this;
	}

	/**
	 * Returns the document charset encoding.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getCharset() {
		return $this->_charset;
	}
	
	/**
	 * Sets the document author
	 *
	 * @param   string  $author  Author string
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setAuthor($author) {

		$this->_author = $author;

		return $this;
	}

	/**
	 * Returns the document author.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getAuthor() {
		return $this->_author;
	}
	
	/**
	 * Sets the document project name
	 *
	 * @param   string  $project_name  project name string
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setProjectName($project_name) {

		$this->project_name = $project_name;

		return $this;
	}

	/**
	 * Returns the document project name.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getProjectName() {
		return $this->project_name;
	}

	/**
	 * Sets the global document language declaration. Default is English (en-gb).
	 *
	 * @param   string  $lang  The language to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setLanguage($lang = "en-gb") {
		$this->language = strtolower($lang);

		return $this;
	}

	/**
	 * Returns the document language.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getLanguage() {
		return $this->language;
	}
	
/**
	 * Sets the title of the document
	 *
	 * @param   string  $title  The title to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

	/**
	 * Return the title of the document.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the base URI of the document
	 *
	 * @param   string  $base  The base URI to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setBase($base) {
		$this->base = $base;

		return $this;
	}

	/**
	 * Return the base URI of the document.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getBase() {
		return $this->base;
	}
	
	/**
	 * Sets the prefix URI of the document
	 *
	 * @param   string  $url_prefix  The prefix URI to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setUrlPrefix($url_prefix) {
		$this->url_prefix = $url_prefix;

		return $this;
	}

	/**
	 * Return the prefix URI of the document.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getUrlPrefix() {
		return $this->url_prefix;
	}

	/**
	 * Sets the description of the document
	 *
	 * @param   string  $description  The description to set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Return the title of the page.
	 *
	 * @return  string
	 *
	 * @since    2.0
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the document link
	 *
	 * @param   string  $url  A url
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setLink($url) {
		$this->link = $url;

		return $this;
	}

	/**
	 * Returns the document base url
	 *
	 * @return string
	 *
	 * @since   2.0
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Sets the document generator
	 *
	 * @param   string  $generator  The generator to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setGenerator($generator) {
		$this->_generator = $generator;

		return $this;
	}

	/**
	 * Returns the document generator
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getGenerator() {
		return $this->_generator;
	}

	/**
	 * Sets the document modified date
	 *
	 * @param   string  $date  The date to be set
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setModifiedDate($date) {
		$this->_mdate = $date;

		return $this;
	}

	/**
	 * Returns the document modified date
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getModifiedDate() {
		return $this->_mdate;
	}

	/**
	 * Sets the document MIME encoding that is sent to the browser.
	 *
	 * This usually will be text/html because most browsers cannot yet
	 * accept the proper mime settings for XHTML: application/xhtml+xml
	 * and to a lesser extent application/xml and text/xml. See the W3C note
	 * ({@link http://www.w3.org/TR/xhtml-media-types/}
	 * http://www.w3.org/TR/xhtml-media-types/}) for more details.
	 *
	 * @param   string   $type  The document type to be sent
	 * @param   boolean  $sync  Should the type be synced with HTML?
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 *
	 * @link    http://www.w3.org/TR/xhtml-media-types
	 */
	public function setMimeEncoding($type = 'text/html', $sync = true) {
		$this->_mime = strtolower($type);

		// Syncing with meta-data
		if ($sync) {
			$this->setMetaData('content-type', $type, true, false);
		}

		return $this;
	}

	/**
	 * Return the document MIME encoding that is sent to the browser.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getMimeEncoding() {
		return $this->_mime;
	}

	/**
	 * Sets the line end style to Windows, Mac, Unix or a custom string.
	 *
	 * @param   string  $style  "win", "mac", "unix" or custom string.
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setLineEnd($style) {
		switch ($style) {
			case 'win':
				$this->_lineEnd = "\15\12";
				break;
			case 'unix':
				$this->_lineEnd = "\12";
				break;
			case 'mac':
				$this->_lineEnd = "\15";
				break;
			default:
				$this->_lineEnd = $style;
		}

		return $this;
	}

	/**
	 * Returns the lineEnd
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function _getLineEnd() {
		return $this->_lineEnd;
	}
	
	/**
	 * Sets the string for the body
	 *
	 * @param   string  $string  Body
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setBody($string) {
		$this->_body = $string;

		return $this;
	}

	/**
	 * Returns the body
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function getBody() {
		return $this->_body;
	}
	
	/**
	 * Sets the string used to indent HTML
	 *
	 * @param   string  $string  String used to indent ("\11", "\t", '  ', etc.).
	 *
	 * @return  VDocument instance of $this to allow chaining
	 *
	 * @since   2.0
	 */
	public function setTab($string) {
		$this->_tab = $string;

		return $this;
	}

	/**
	 * Returns a string containing the unit for indenting HTML
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function _getTab() {
		return $this->_tab;
	}
	
	public function assign($var, $value) {
		$renderer =& $this->getRenderer();
		$renderer->assign($var, $value);
	}
	
	/**
	 * Outputs the document
	 *
	 * @return  The rendered data
	 *
	 * @since   2.0
	 */
	public function render() {
		if ($mdate = $this->getModifiedDate()) {
			VResponse::setHeader('Last-Modified', $mdate /* gmdate('D, d M Y H:i:s', time() + 900) . ' GMT' */);
		}

		VResponse::setHeader('Content-Type', $this->_mime . ($this->_charset ? '; charset=' . $this->_charset : ''));
		
		
	}
}