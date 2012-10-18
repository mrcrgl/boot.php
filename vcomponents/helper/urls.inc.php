<?php

class ComponentHelperUrls extends VUrl 
{

    var $pattern = array(
        '^/$'                                     => 'helper.index.show',
        '^login/$'                             => array('include:auth', array('referer' => '/versions/helper/')),
        '^database/$'                        => 'helper.database.show',
        '^database/configure/$'    => 'helper.database.configure',
        '^database/create/$'        => 'helper.database.create',
        '^database/showconfig/$'=> 'helper.database.showconfig',
        '^database/models/$'        => 'helper.models.show',
        '^database/(?P<model>\w+)/create/$'        => 'helper.models.sql_create',
      '^translation/$'        => 'helper.translation.show',
      '^translation/save/$'        => 'helper.translation.save',
      '^translation/cache_clear/$'        => 'helper.translation.flush_cache',
    );

}