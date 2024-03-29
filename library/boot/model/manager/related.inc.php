<?php

class BModelManagerRelated extends BModelManager 
{

    var $related = null;

    var $reverse = null;

    var $declaration = null;

    var $relation_types = array(
        'ForeignKey',
        'ManyToMany',
        'OneToOne'
    );

    public function __construct(&$model, &$related)
    {
        parent::__construct(&$model);
        #var_dump($model);
        $this->set('related', &$related);

        $this->initializeRelation();



    }

    public function clear()
    {
        $dbo =& BDatabase::getInstance();
        $dbo->userQuery(sprintf(
            "DELETE FROM `%s` WHERE `%s` = '%s'",
            $this->declaration->get('reference_table'),
            $this->declaration->get('model_pk'),
            $this->declaration->get('_model')->get('uid')
        ));
    }

    public function bulkAdd($array, $truncate=true)
        {
        if ($truncate) {
            $this->clear();
        }

        foreach ($array as $value) {
            if (Validator::is($value, 'hexuid') || is_object($value))
                $this->add($value);
        }
    }

    public function add($mixed)
        {
        $mixed = ((is_object($mixed)) ? $mixed->get('uid') : $mixed);

        $dbo =& BDatabase::getInstance();
        $dbo->userQuery(sprintf(
            "INSERT IGNORE INTO `%s` SET `%s` = '%s', `%s` = '%s'",
            $this->declaration->get('reference_table'),
            $this->declaration->get('model_pk'),
            $this->_model->get('uid'),
            $this->declaration->get('reference_pk'),
            $mixed
        ));
    }

    public function remove($mixed)
        {
      $mixed = ((is_object($mixed)) ? $mixed->get('uid') : $mixed);

      $dbo =& BDatabase::getInstance();
      $dbo->userQuery(sprintf(
          "DELETE FROM `%s` WHERE `%s` = '%s' AND `%s` = '%s' LIMIT 1",
          $this->declaration->get('reference_table'),
          $this->declaration->get('model_pk'),
          $this->_model->get('uid'),
          $this->declaration->get('reference_pk'),
          $mixed
      ));

      $this->clearOptions();
    }

    public function has($mixed)
    {
        $mixed = ((is_object($mixed)) ? $mixed->get('uid') : $mixed);

        #print $this->_model->get('uid').' - '.$mixed.NL;

        $dbo =& BDatabase::getInstance();
        $dbo->userQuery(sprintf(
            "SELECT * FROM `%s` WHERE `%s` = '%s' AND `%s` = '%s'",
            $this->declaration->get('reference_table'),
            $this->declaration->get('model_pk'),
            $this->_model->get('uid'),
            $this->declaration->get('reference_pk'),
            $mixed
        ));

        $this->clearOptions();

        return (bool)$dbo->getNumRows();
    }

    private function initializeRelation()
    {
    #exit;
    $related_class =& $this->related->getClass();
      if ($this->checkRelation(&$this->_model, $related_class)) 
{
            // TODO: set debug message
            #print "Parent is master and related is related".NL;
            #printf("Parent: %s(%s); Related: %s(%s)".NL, get_class($this->_model), $this->_model->get('uid'), get_class($this->related), $this->related->get('uid'));
            $this->reverse = false;

          $designer =& BDatabaseDesigner::getInstance();
          $table_name = $designer->getTableName($related_class);
          $table = $this->declaration->get('reference_table');
          $table .= sprintf(
              " LEFT JOIN %s ON (%s.%s = %s.%s) ",
              $table_name,
              $table_name,
              'uid',
              $this->declaration->get('reference_table'),
              $this->declaration->get('reference_pk')
          );
          $this->setTable($table);
          $this->setModelName($related_class);
          #print sprintf('[%s:%s]', $this->declaration->get('model_pk'), $this->_model->get('uid'));
            $this->filterSticky( sprintf('[%s:%s]', $this->declaration->get('model_pk'), $this->_model->get('uid')) );

        } elseif ($this->checkRelation(&$this->related, $this->_model->getClass())) {
            // TODO: set debug message
            #print "Parent is related and related is master / reverse mode".NL;
          #printf("Parent: %s(%s); Related: %s(%s)".NL, get_class($this->_model), $this->_model->get('uid'), get_class($this->related), $this->related->get('uid'));

            $this->reverse = true;

            /*
             * ManyToMany
             */
          if (get_class($this->declaration) == 'BModelFieldManyToMany') 
{

            $designer =& BDatabaseDesigner::getInstance();
            $table_name =& $designer->getTableName($this->related->getClass());
            $table = $this->declaration->get('reference_table');
            $table .= sprintf(
                " LEFT JOIN %s ON (%s.%s = %s.%s) ",
                $table_name,
                $table_name,
                'uid',
                $this->declaration->get('reference_table'),
                $this->declaration->get('model_pk')
            );

            $this->filterSticky( sprintf('[%s:%s]', $this->declaration->get('reference_pk'), $this->_model->uid) );

            // TODO: set the group_uid at second parameter
            #print sprintf('[%s:%s]', $this->declaration->get('db_column'), 'theuid');
            #print get_class($this->_model);
            #$this->set('related', get_class($this->_model));
            parent::__construct(&$this->related, $this->_model->getClass());
            #exit;
            $this->setTable($table);

          }

          /*
           * ForeignKey
           */
          elseif (get_class($this->declaration) == 'BModelFieldForeignKey') 
{
            $filter = sprintf('[%s:%s]', $this->declaration->get('db_column'), $this->_model->uid);
            parent::__construct(&$this->related, $this->_model->getClass());
            $this->filterSticky($filter);
          }




            // Only for ForeignKeys
            #$designer =& BDatabaseDesigner::getInstance();
            #$this->setTable($designer->getTableName(get_class($this->_model)));
        } else {
            // TODO: set debug message
            #print "Nix von beidem.".NL;
        }
    }

    private function checkRelation(&$model, $related)
        {
        foreach ($model->getFields() as $field) {
            $declaration =& $model->getFieldDeclaration($field);

            if (!$declaration) {
              #print $field." will fucken.".NL;
              continue;
            }

            // strip prefix: BModelField and check for relational fields
            if (!in_array(substr(get_class($declaration), 11), $this->relation_types)) 
{
                continue;
            }
            #print $declaration->get('reference');
            if ($declaration->get('reference') == $related) {
                $this->declaration =& $declaration;
                return true;
            }
        }
        return false;
    }
}