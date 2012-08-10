<?php

class ComponentAuthUrls extends VUrl {
	
	var $pattern = array(
		'^/$' 			=> 'auth.login.show',
		'^logout/$' => 'auth.login.logout'
	);
	
}