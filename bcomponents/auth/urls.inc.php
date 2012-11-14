<?php

class ComponentAuthUrls extends BUrl
{
    
    var $pattern = array(
        '^/$'            => 'auth.login.show',
        '^logout/$'      => 'auth.login.logout'
    );
    
}