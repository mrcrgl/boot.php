<?php

VLoader::import('versions.utilities.string');

class ComponentCrudViewModel extends VApplicationView 
{

    var $object_name = null;

    var $object_uid = null;

    var $object_manager_name = null;

    var $object = null;

    var $object_manager = null;

    var $object_layout_version = null;

    public function create()
   {
        $this->fetchObject();

        $oDocument =& VFactory::getDocument();
        $oRenderer =& $oDocument->getRenderer();
        $oRenderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );

        $oDocument->setTemplate('crud/create.htpl');

        if ($oRenderer->templateExists($this->getAlternateTemplate('create.htpl'))) {
            $oDocument->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
        }

      $oInput =& VFactory::getInput();
      if (strtolower($oInput->getMethod()) == 'post') {
          $this->updateObject();
          header( sprintf("Location: /%s%s", $oDocument->getUrlPrefix(), (($this->object->getModelVersion() == 1) ? $this->object->uid : $this->object->get('uid'))) );
          exit;
      }

    }

    public function read()
      {
        $this->fetchObject();

        $oDocument =& VFactory::getDocument();
        $oRenderer =& $oDocument->getRenderer();
        $oRenderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );

        $oDocument->setTemplate('crud/read.htpl');

        if ($oRenderer->templateExists($this->getAlternateTemplate('read.htpl'))) {
            $oDocument->assign('user_defined_template', $this->getAlternateTemplate('read.htpl'));
        }

    }

    public function update()
        {
        $this->fetchObject();

        $oDocument =& VFactory::getDocument();
      $oRenderer =& $oDocument->getRenderer();
        $oRenderer->appendTemplateDirPart( $this->getAlternateTemplatePath() );

        $oDocument->setTemplate('crud/create.htpl');

        if ($oRenderer->templateExists($this->getAlternateTemplate('create.htpl'))) {
            $oDocument->assign('user_defined_template', $this->getAlternateTemplate('create.htpl'));
        }

      $oDocument->assign('delete_url', sprintf("/%s%s/delete", $oDocument->getUrlPrefix(), $this->object->uid));

        $oInput =& VFactory::getInput();
      if (strtolower($oInput->getMethod()) == 'post') {
          if ($this->updateObject()) {
              header( sprintf("Location: /%s%s", $oDocument->getUrlPrefix(), (($this->object->getModelVersion() == 1) ? $this->object->uid : $this->object->get('uid'))) );
              exit;
          }
      }
    }

    public function delete()
          {
        $this->fetchObject();

        $oDocument =& VFactory::getDocument();

        $this->object->delete();

        VMessages::_('Ok', 'L&ouml;schung erfolgreich!', 'success');

        header( sprintf("Location: /%s", $oDocument->getUrlPrefix()) );
      exit;
    }

    private function fetchObject()
    {

        $oInput =& VFactory::getInput();

        $parent = $oInput->get('parent', false, 'get');

        $this->object_name = $oInput->get('object_name', null, 'get');
        $this->object_uid = $oInput->get('object_uid', false, 'get');


        if (!$this->object_name) {
            throw new Exception( "Input object_name must be set!" );
        }

        $this->object = new $this->object_name($this->object_uid);
        $this->object_layout_version = $this->object->getModelVersion();

        if ($this->object_layout_version == 1) {

            $this->object_manager_name = $this->object_name.'Manager';
            $this->object_manager = new $this->object_manager_name();
            $this->object_manager->set('ignore_object_state', true);

        } else {
            #print "before init jacket";
            $this->object_manager = new VModelManagerJacket(&$this->object);
            #print "after init jacket";
        }

        $oDocument =& VFactory::getDocument();

        if ($parent) {
            $this->object_manager->filter($parent, $oInput->get($parent, null, 'get'));
            $oDocument->assign($parent, $oInput->get($parent, null, 'get'));
        }


        $oDocument->assign('object', &$this->object);
        $oDocument->assign('manager', &$this->object_manager);

    }

    private function getAlternateTemplate($__method)
    {
        $path = strtolower(implode(DS, VString::explode_camelcase($this->object_name)));

        if (strpos($path, '/model/') !== false) {
            $newpath = 'crud'.DS.substr($path, strpos($path, '/model/')+strlen('/model/')).DS.$__method;
        } else {
            $newpath = 'crud'.DS.$path.DS.$__method;
        }

      return $newpath;
    }

    private function getAlternateTemplatePath()
    {
        $path = strtolower(implode(DS, VString::explode_camelcase($this->object_name)));

      $newpath = 'crud'.DS.substr($path, strpos($path, '/model/')+strlen('/model/'));
      return $newpath;
    }

    private function updateObject()
    {
        $oInput =& VFactory::getInput();

        $params = array();
        foreach ($_POST as $key => $value) {
            $params[$key] = $oInput->get($key, null, 'post');
        }
        #var_dump($params);
        if ($this->object_layout_version == 1) {
            $bok = $this->object->update($params);
        } elseif ($this->object_layout_version == 2) {
            $this->object->bulkSet($params);
            $bok = $this->object->save();

            /*
             * for ManyToMany relationships
             */
            foreach ($this->object->getFields() as $field) {
                if (!$this->object->getFieldDeclaration($field)->get('many_to_many')) continue;

                if (isset($params[$field])) {
                    $fieldset = $field.'__set';
                    $this->object->$fieldset->bulkAdd($params[$field]);
                }

            }

        }


        if (!$bok) {
            VMessages::_('Error', 'Speichern fehlgeschlagen', 'error');
        } else {
            VMessages::_('Ok', 'Speichern erfolgreich!', 'success');
        }

        return $bok;
    }
}