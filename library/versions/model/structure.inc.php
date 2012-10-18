<?php

require_once dirname(__FILE__).DS.'field'.DS.'unique_id.inc.php';

class VModelStructure extends VObject 
{

    #var $_fields = array();
    var $_valid = false;

    var $_manager = null;

    var $_allow_cache = true;

    var $_related_manager = array();

    var $_classname = null;

    var $_field_declarations = array();

    var $_fields = array();

    public function __construct($__uid=null)
   {

        $this->initialize();

        if (Validator::is($__uid, 'hexuid')) $this->load($__uid);

    }

    public function getModelVersion()
    {
        return 2;
    }

    private function initialize()
    {
        #printf("Class initialize: %s + Valid: %s".NL, get_class($this), (($this->isValid()) ? 'true' : 'false'));

      VModelField::prepareModel(&$this);

        foreach ($this->getFields() as $field) {
          #print "Init field: $field".NL;
          #$this->$field = $this->get(&$field);
          #$this->$field = $this->getFieldDeclaration(&$field)->onInitialize( $this->get(&$field) );
            $this->set($field, $this->getFieldDeclaration($field)->onInitialize( $this->get($field) ), true);
        }


    }

    public function bulkSet($params, $bypasscheck=false)
        {
        $fields =& $this->getFields();

        foreach ($fields as $field) {
            $declaration =& $this->getFieldDeclaration($field);
            if (isset($params[($declaration->get('db_column'))])) {
                $params[$field] = $params[($declaration->get('db_column'))];
            }
        }
        #print_r($params);
        foreach ($params as $k => $v) {
            if (in_array($k, $fields)) {
                $this->set($k, $v, &$bypasscheck);
            }
        }
    }

    public function save()
            {
        $designer = VDatabaseDesigner::getInstance();
      return $designer->saveModel(&$this);
    }

    public function load($pk)
    {
      $this->objects->get(sprintf('[filter:[uid:%s]]', $pk));
    }

    public function set($__field, $__value, $bypasscheck=false)
    {
        #print "setting field: ".$__field.' with '.$__value.NL;
        $__value = $this->getFieldDeclaration($__field)->onSet($__value, &$this);

      if (!$bypasscheck) {
          if (!$this->checkField($__field, $__value)) {
              return false;
          }
      }

        parent::set($__field, $__value);

        return true;
    }

    public function get($__field)
    {
        $__value = parent::get($__field);

        $declaration =& $this->getFieldDeclaration($__field);

    if (!$declaration)
          return null;

        if (!method_exists($declaration, 'onGet'))
          return null;

        return $declaration->onGet($__value, &$this);
  }

    public function isValid($set=null)
  {
        if (!is_null($set)) {
            $this->_valid = (bool)$set;
        }
        return $this->_valid;
    }

    public function __set($var, $value)
    {
        // TODO var does not exist
    }

    public function __get($__var)
    {
        if ($__var == 'objects') {
            if (is_null($this->_manager))
                $this->_manager = VModelManager::getInstance(&$this);
            return $this->_manager;
        }
        if (substr($__var, -5, 5) == '__set') {
          #print $__var.NL;exit();
          if (!$this->_allow_cache || !isset($this->_related_manager[$__var])) {
            $declaration =& $this->getFieldDeclaration(substr($__var, 0, -5));
            $reference = (($declaration) ? $declaration->get('reference') : null );
            if ($reference) {
              $related = new $reference();// VModelStorage::_($reference);
            } else {
              $related_name = VString::underscores_to_camelcase(substr($__var, 0, -5));
              $related = new $related_name(); //VModelStorage::_($related_name);
            }
            $this->_related_manager[$__var] = VModelManager::getInstance(&$this, $related, 'related');
          }
          #else { print "den jibbet schon"; }
      #var_dump($this->_related_manager[$__var]);
      #exit;
            return $this->_related_manager[$__var];
        }


        // TODO var does not exist
    }

    private function getRelationalManager($__related_to)
    {

    }

    public function &getFields($only_db_columns=false)
    {

      $type = (($only_db_columns) ? 'a' : 'b');
      if (!isset($this->_fields[$type])) {
        $class_vars = get_class_vars($this->getClass());
        $this->_fields[$type] = array();
        foreach ($class_vars as $column => $declaration) 
{
          if (substr($column, 0, 1) == '_') continue;
          if ($only_db_columns && $this->getFieldDeclaration($column)->get('type') == 'none') continue;
          $this->_fields[$type][] = $column;
        }
      }
        return $this->_fields[$type];
    }

    public function &getFieldDeclaration($__field)
    {

      // TODO: __PHP_Incomplete_Class Exception?
      /*if (get_class($this->_field_declarations[$__field]) == '__PHP_Incomplete_Class') 
{
        print gettype($this->_field_declarations[$__field]);
      }*/
      if (!isset($this->_field_declarations[$__field])/* || get_class($this->_field_declarations[$__field]) == '__PHP_Incomplete_Class'*/) 
{
        $this->_field_declarations[$__field] =& VModelField::getInstance($this->getClass(), $__field);
      }

      return $this->_field_declarations[$__field];
    }

    public function getClass()
    {
      if (!$this->_classname) 
{
        $this->_classname = get_class(&$this);
      }
      return $this->_classname;
    }

    public function checkFields()
    {
        foreach ($this->getFields() as $field) {
            if (!$this->checkField($field, $this->get($field))) {
                print "Invalid field: $field".NL;
                return false;
            }
        }
        return true;
    }

    public function checkField($__field, $__value)
    {
        return Validator::checkField($this->getFieldDeclaration($__field), $__value);
    }
}
