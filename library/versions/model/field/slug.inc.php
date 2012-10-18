<?php

class VModelFieldSlug extends VModelField 
{

    var $max_length = 50;

    var $reference = null;

    var $editable = false;

    public function __construct($options=array())
   {
        parent::__construct($options);
    }

    public function onSet($value=null, &$model)
    {
      #print $value.NL;
      if ($value && substr($value, 0, 4) != 'Slug') {
          return $this->generateSlug($value, &$model);
      }
      return $value;
    }

    private function generateSlug($value=null, &$model)
    {
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

        while (substr($slug, -1) == '-') {
          $slug = substr($slug, 0, -1);
        }

        $slug_base = $slug;
        $i = 1;

        if ($model->get('slug') == $slug) {
          return $slug;
        }

        $class = $model->getClass();
        $vmodel = new $class();
        while ($vmodel->objects->filter(sprintf('[%s:%s]', $this->get('field_name'), $slug))->count() > 0) {
      //$model = new $class();
      $slug = sprintf('%s-%d', $slug_base, $i);
      $i++;
        }

        return $slug;
    }

}