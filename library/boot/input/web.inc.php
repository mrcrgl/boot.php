<?php

class BInputWeb extends BInput implements BInputInterface
{
    
    public function __construct()
    {
        parent::__construct();
    }
    
    
    public function get($attribute, $default=null, $method=null)
    {
        
        if (!class_exists('BLoader')) {
            BLoader::import('boot.utilities.string');
        }
        
        if (is_null($method)) {
            $data =& $_REQUEST;
        } elseif ('get' == BString::strtolower($method)) {
            $data =& $_GET;
        } elseif ('post' == BString::strtolower($method)) {
            $data =& $_POST;
        } elseif ('cookie' == BString::strtolower($method)) {
            $data =& $_COOKIE;
        } elseif ('server' == BString::strtolower($method)) {
            $data =& $_SERVER;
        }
    
        if (array_key_exists($attribute, $data)) {
            return $data[$attribute];
        }
        
        return $default;
    }
    
    public function collect()
    {
        $url =& BFactory::getUrl();
        $url->parse($this->get('REQUEST_URI', 'index', 'server'));
    }
}