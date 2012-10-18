<?php

abstract class VArray 
{

    static public function strip_empty_values($array, $reorder_keys=false)
 {
        VLoader::import('versions.utilities.validator');

        if (!Validator::is($array, 'array')) {
            return array();
        }

        foreach ($array as $key => $value) {
            if (!Validator::is($value, 'filled')) {
                unset($array[$key]);
            }
        }

        if ($reorder_keys) {
            $temp = $array;
            $array = array();

            foreach ($temp as $value) {
                $array[] = $value;
            }
        }

        return $array;
    }

    public static function ucfirst(&$array)
    {
        foreach ($array as $k => $v) {
            $array[$k] = ucfirst($v);
        }
        return $array;
    }

    static public function get($array, $key=0, $default=null)
    {
        if (!Validator::is($array, 'array')) return null;
        return ((array_key_exists($key, $array)) ? $array[$key] : $default);
    }

    static public function parseOptions($unparsed, $temp=array())
    {
        if (Validator::is($unparsed, 'array')) return $unparsed;

        // fetch strings in ""
        while (preg_match('/("[^\"\"]*")/', $unparsed, $tmpmatch)) {
            #var_dump($tmpmatch);
            $unique = uniqid();
            $temp[$unique] = substr($tmpmatch[1], 1, -1);
            $unparsed = str_replace($tmpmatch[1], $unique, $unparsed);
        }

        $unparsed = preg_replace('/[\n\t\ ]/', '', $unparsed);
        #print $unparsed.NL;


        if (!preg_match('/^\[(?P<options>.*)\]$/', $unparsed, $matches)) {
            // TODO add Debug
            printf("VOPTS layout mismatch: %s<br />", $unparsed);
            var_dump($unparsed);print "<br />";
            #throw new Exception(sprintf("VModel column declataion layout mismatch: %s", $declaration));
        }
        #var_dump($matches);print "<br />";

        $options = array();
        #$temp    = array();

        if (strlen($matches['options'])) {

            /*if (preg_match('/(\[.*\])/', $matches['options'], $tmpmatch)) {
                $unique = uniqid();
                $temp[$unique] = VArray::parseOptions($tmpmatch[1]);
                $matches['options'] = preg_replace('/(\[.*\])/', $unique, $matches['options']);
            }*/
            while (preg_match('/(\[[^\[\]]*\])/', $matches['options'], $tmpmatch)) {
                $unique = uniqid();
                $temp[$unique] = VArray::parseOptions($tmpmatch[1], $temp);
                $matches['options'] = str_replace($tmpmatch[1], $unique, $matches['options']);
            }

            $option_pairs = preg_split('/,/', $matches['options']);
            $n=0;
            foreach ($option_pairs as $option_pair) {
                $option_pair = trim($option_pair);

                if (strpos($option_pair, ':') === false) {
                    $value     = $option_pair;
                    $key         = $n;
                    $n++;
                } else {
                    list($key, $value) = explode(':', $option_pair);
                }

                $key = trim($key);
                $value = trim($value);

                if (isset($temp[$value])) {
                    $options[$key] = $temp[$value];
                } elseif (strtolower($value) == 'true') {
                    $options[$key] = true;
                } elseif (strtolower($value) == 'false') {
                    $options[$key] = false;
                } elseif (strtolower($value) == 'null') {
                    $options[$key] = null;
                } else {
                    $options[$key] = $value;
                }
            }
        }

        return $options;
    }
}