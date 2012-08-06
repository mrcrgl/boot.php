<?php

class ComponentHelperUrls extends VUrl {
	
	var $pattern = array(
		'^/$' 									=> 'helper.index.show',
		'^login/$' 							=> array('include:auth', array('referer' => '/versions/helper/')),
		'^database/$'						=> 'helper.database.show',
		'^database/configure/$'	=> 'helper.database.configure'
	);
	
}