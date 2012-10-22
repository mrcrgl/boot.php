<?php

class BSettingsIni implements BSettingsInterface 
{

    static $collection = array();

    static $core_config_path;

    static $user_config_path;

    /**
     *
     * init()
     * @return void
     */
    static function init($config_path=null, $config_file='settings')
     {

        if (!$config_path) {
            self::$core_config_path = VCONFIG;
            self::$user_config_path = PROJECT_CONFIG;

            self::import_path(self::$core_config_path);
            self::import_path(self::$user_config_path);

            if (self::get('environment')) {
                self::import_path(self::$user_config_path, self::get('environment'));
            }
        } else {
            self::import_path($config_path, $config_file);
        }


    }

    /**
     *
     * get value by defined key
     * example:
     *     environment (interprets default.environment)
     *     database.host
     *
     * @param string $key
     * @return mixed value
     */
    static function get($key, $default=null)
     {

        $group = 'default';

        if (strpos($key, '.') !== false) {
            list ($group, $key) = explode('.', $key);
        }

        if (!isset(self::$collection[$group])) {
            return $default;
        }

        if (!isset(self::$collection[$group][$key])) {
            return $default;
        }

        return self::$collection[$group][$key];
    }

    static function set($key, $value=false)
    {
        #return call_user_func(self::$handler.'::set', $key, $value);

    }

    static function import_path($__path, $__file='settings')
    {
        $__file = $__file.'.ini';

        if (!is_dir($__path)) {
            throw new Exception(sprintf("path not found: '%s'", $__path));

            #BDebug::_(new BDebugMessage(sprintf("path not found: '%s'", $__path)));
        }

        $settings_file = $__path.DS.$__file;
        if (!is_file($settings_file)) {
            throw new Exception(sprintf("config %s not found in: '%s'", $__file, $__path));
        }

        self::$collection = array_merge((array)self::$collection, (array)parse_ini_file($settings_file, true));
    }

}