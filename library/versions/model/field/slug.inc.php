<?php

class VModelFieldSlug extends VModelField {

	var $max_length = 50;

	var $reference = null;

	var $editable = false;

	public function __construct($options=array()) {
		parent::__construct($options);
	}

	public function onSet($value=null, &$model) {
	  if ($value) {
		  return $this->generateSlug($value, &$model);
	  }
	  return $value;
	}

	private function generateSlug($value=null, &$model) {
		if (is_null($value)) {
			return '';
		}

		if ($model->isValid() && $model->get('slug')) {
		  return $model->get('slug');
		}

		if (!class_exists('VString'))
		  VLoader::import('versions.utilities.string');

		#var_dump($this->_model);

		$slug = VString::sanitize( $value );
		if (!$slug) return '';
    if ($slug == $value) return $slug;

		if (strlen($slug) > 47) {
		  $slug = substr($slug, 0, 47);
		}

		$slug_base = $slug;
		$i = 1;

		if ($model->get('slug') == $slug) {
		  return $slug;
		}

		$class = get_class($this->get('_model'));
		$model = new $class();
		while ($model->objects->filter(sprintf('[%s:%s]', $this->get('field_name'), $slug))->count() > 0) {
      $model = new $class();
      $slug = sprintf('%s-%d', $slug_base, $i);
      $i++;
		}

		return $slug;
	}

}