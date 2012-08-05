<?php

class ComponentCrudUrls extends VUrl {
	
	var $pattern = array(
		'^/$' 			=> 'crud.model.read',
		'^new/$' 		=> 'crud.model.create',
		'^(?P<object_uid>[a-f0-9]{13})/$' => 'crud.model.update',
		'^(?P<object_uid>[a-f0-9]{13})/delete/$' => 'crud.model.delete'
	);
	
}