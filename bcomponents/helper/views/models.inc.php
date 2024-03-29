<?php

class ComponentHelperViewModels extends BApplicationView 
{

    public function show()
 {

        $oDocument =& BFactory::getDocument();
        $oDocument->setTemplate('database/index.htpl');
        $oDocument->assign('_current_step_tpl', 'models/step/show.htpl');

        BLoader::register('ProjectUrls', PROJECT_CONFIG.DS.'urls.inc.php');
        $project_urls = new ProjectUrls();

        $return = $this->parsePattern($project_urls, 'project', true);



        #$oDocument->assign('db_designer', &$designer);
        $oDocument->assign('map', $return);

        $host     = BSettings::get('database.host',         'undef');
        $database = BSettings::get('database.database', 'undef');
        $user     = BSettings::get('database.user',         'undef');

        BMessages::_('', sprintf('U\'re modifying host: %s / database: %s / user: %s', $host, $database, $user));
    }

    public function sql_create()
    {

        $oDocument =& BFactory::getDocument();
        $input    =& BFactory::getInput();
        $dbo      =& BFactory::getDatabase();

        $classname= $oInput->get('model', false, 'get');
        $check    = true;

        if (!$classname) 
{
            BMessages::_('Error', 'Invalid Model', 'error');
            $check = false;
        }
        BLoader::autoload($classname);
        if ($check && !class_exists($classname)) 
{
            BMessages::_('Error', sprintf('Class \'%s\' not found!', $classname), 'error');
            $check = false;
        }

        $model = new $classname();

        $dbo->userQuery("SET foreign_key_checks = 0;");

        if ($model->getModelVersion() == 1) {
            $sql = $model->getSQL(true);
            // DROP Tables
            if (isset($sql[1]) && is_array($sql[1])) {
                foreach ($sql[1] as $tmp) {
                  try {
                      $dbo->userQuery($tmp);
                    } catch (Exception $e) {
                      BMessages::_('Databse error', $e->getMessage(), 'error');
                      header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
                      exit;
                    }
                }
            }
            // CREATE Tables
            if (isset($sql[0]) && is_array($sql[0])) {
                foreach ($sql[0] as $tmp) {
                    try {
                      $dbo->userQuery($tmp);
                    } catch (Exception $e) {
                      BMessages::_('Databse error', $e->getMessage(), 'error');
                      header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
                      exit;
                    }
                }
            }
        } elseif ($model->getModelVersion() == 2) {
            $designer         = BDatabaseDesigner::getInstance();
            $drop_table     = $designer->getDropTable($model);
            $create_table = $designer->getCreateTable($model);
            $create_index = $designer->getCreateIndex($model);
            // DROP Tables
            if (isset($drop_table) && is_array($drop_table)) {
                foreach ($drop_table as $tmp) {
                  try {
                      $dbo->userQuery($tmp);
                    } catch (Exception $e) {
                      BMessages::_('Databse error', $e->getMessage(), 'error');
                      header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
                      exit;
                    }
                }
            }
            // CREATE Tables
            if (isset($create_table) && is_array($create_table)) {
                foreach ($create_table as $tmp) {
                  try {
                      $dbo->userQuery($tmp);
                    } catch (Exception $e) {
                      BMessages::_('Databse error', $e->getMessage(), 'error');
                      header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
                      exit;
                    }
                }
            }
            // CREATE Indexes
            if (isset($create_index) && is_array($create_index)) {
                foreach ($create_index as $tmp) {
                  try {
                      $dbo->userQuery($tmp);
                    } catch (Exception $e) {
                      BMessages::_('Databse error', $e->getMessage(), 'error');
                      header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
                      exit;
                    }
                }
            }

            $dbo->userQuery("SET foreign_key_checks = 1;");
        }

        BMessages::_('Success!', sprintf('Database Layout for Modal \'%s\' installed!', $classname), 'success');

        header( sprintf("Location: /%sdatabase/models", $oDocument->getUrlPrefix()) );
        exit;
    }

    private function parsePattern($urls, $name='project', $is_root=false)
    {
        $return = array();

        $return['name'] = $name;
        $return['vmodels'] = array();
        $return['models'] = array();

        $ro = new ReflectionObject($urls);
        $models_path = (($is_root) ? realpath(dirname($ro->getFileName()).DS.'..'.DS.'models') : dirname($ro->getFileName()).DS.'models');
        $component_root_path = (($is_root) ? null : dirname($ro->getFileName()));
        #printf("Models coud be here: %s".NL, $models_path);

        $vmodels = $this->getRequiredModels($component_root_path);
        $models = $this->scanModels($models_path);

        /*
         * array('name' => "foo", 'is_installed' => bool, 'is_uptodate' => bool)
         */
        if (count($vmodels)) {
            foreach ($vmodels as $model) {
                $return['vmodels'][] = $this->checkModel($model);
            }
        }
        if (count($models)) {
            foreach ($models as $model) {
                $return['models'][] = $this->checkModel($model);
            }
        }


        foreach ($urls->getPattern() as $destination) {

            $destination = $urls->splitDestination($destination, false);

            if (!$component_ident = $urls->getDestinationComponent($destination)) {
                #print $destination.' is not a component.'.NL;
                continue;
            }

            #print "Component ".$component_ident.NL;

            $url_classname = sprintf("Component%sUrls", ucfirst($component_ident));
            $surls = new $url_classname();

            $return['components'][] = $this->parsePattern($surls, $component_ident);
        }
        return $return;
    }

    private function checkModel($model)
    {
        $return = array(
            'name'                     => $model,
            'is_installed'     => false,
            'is_uptodate'     => false,
            'is_deprecated_layout' => false
        );

        $designer = BDatabaseDesigner::getInstance();

        $obj = new $model();
        if ($obj->getModelVersion() == 1) {
            $return['is_installed'] = $obj->isSqlInstalled();
            $return['is_deprecated_layout'] = true;
        } elseif ($obj->getModelVersion() == 2) {
            $return['is_installed'] = $designer->isInstalled($obj);
        }

        if ($return['is_installed']) {
            if ($obj->getModelVersion() == 1) {
                $return['is_uptodate'] = $obj->isSqlUpToDate();
            } elseif ($obj->getModelVersion() == 2) {
                $return['is_uptodate'] = $designer->isUpToDate($obj);
            }
        }
        return $return;
    }

    private function getRequiredModels($config_path)
    {
        if (!$config_path) return array();
        if (!is_file($config_path.DS.'controller.ini')) return array();

        $config = parse_ini_file($config_path.DS.'controller.ini', true);

        if (!isset($config['model']) || !isset($config['model']['require'])) return array();

        return $config['model']['require'];
    }

    private function scanModels($models_path)
    {
        if (!is_dir($models_path)) return null;

        $models = array();

        $handle = opendir($models_path);

        if (!$handle) die( sprintf('Cannot open directory \'%s\''.NL, $models_path) );

        while (false !== ($file = readdir($handle))) {
            if (is_dir($models_path.DS.$file) && $file != '.' && $file != '..') {
                $newmodels = $this->scanModels($models_path.DS.$file);
                if (count($newmodels) > 0) {
                    foreach ($newmodels as $newmodel) {
                        #print "adding model ".$newmodel.NL;
                        $models[] = $newmodel;
                    }
                }
                continue;
            }

            if (!preg_match('!\.php$!', $file)) {
                continue;
            }

            $models = $this->getClassesOfFile($models_path.DS.$file, $models);
        }

        closedir($handle);

        return $models;
    }

    private function getClassesOfFile($file, $classes=array())
    {
        $php_file = file_get_contents($file);
        $tokens = token_get_all($php_file);
        $class_token = false;
        foreach ($tokens as $token) {
          if (is_array($token)) {
            if ($token[0] == T_CLASS) {
               $class_token = true;
            } else if ($class_token && $token[0] == T_STRING) 
{
                BLoader::register($token[1], $file);
              $temp = new $token[1]();
              if (isset($temp->_DataMap)) {
                  $classes[] = $token[1];
              }
              if (is_subclass_of($token[1], 'VModelStructure'))
                  $classes[] = $token[1];
                $class_token = false;
            }
          }
        }
        #var_dump($classes);
        return $classes;
    }
}