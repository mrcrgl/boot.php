<?php


class BDirectory
{
    
    var $dir;
    
    public function __construct($__dir)
    {
        
        $this->dir = $this->parse($__dir);
    }
    
    private function parse($__dir)
        {
        
        while (strpos($__dir, '..') !== false) {
            $parts = explode(DS, $__dir);
            
            $revparts = array_reverse($parts);
            
            foreach ($revparts as $k => $v) {
                if ($v == '..' && isset($revparts[$k+1]) && $revparts[$k+1] != '..') {
                    
                    unset($revparts[$k+1]);
                    unset($revparts[$k]);
                    //if (isset($revparts[$k+1]))
                }
            }
            
            $__dir = implode(DS, array_reverse($revparts));
        }
        
        return $__dir;
    }
    
    public function __toString()
    {
        return $this->dir;
    }
}
