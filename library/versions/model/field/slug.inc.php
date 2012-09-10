<?php

class VModelFieldSlug extends VModelField {

	var $max_length = 50;

	var $reference = null;

	var $editable = false;

	public function __construct($options=array()) {
		parent::__construct($options);
	}

	public function onCreate($value) {
		return $this->generateSlug();
	}

	public function onUpdate($value) {
		return $this->generateSlug();
	}

	private function generateSlug() {
		if (is_null($this->get('reference'))) {
			return '';
		}

		VLoader::import('versions.utilities.string');

		$slug = VString::sanitize( $this->get('_model')->get( $this->get('reference') ) );
		if (!$slug) return '';

		$slug_base = $slug;
		$i = 1;

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