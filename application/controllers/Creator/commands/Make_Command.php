<?php
/**
 * Make command class
 *
 * Make command class
 *
 * @package     Ertikaz
 * @subpackage  Libraries
 * @category    Libraries
 */

class Make_Command {

    /**
     * The CodeIgniter instance 
     *
     * @var object
     */
    public $CI = NULL;

    /**
     * Overloading variables
     */
    public function __get($name)
    {
        return (isset($this->CI->$name))?$this->CI->$name:NULL;
    }

    /**
     * Overloading functions
     */
    public function __call($name, $arguments)
    {
        return (method_exists($this->CI, $name))?call_user_func_array(array(&$this->CI,$name), $arguments):NULL;
    }

    /**
     * commands function.
     *
     * return command list as array.
     *
     * @access public
     * @return array
     */
    public function commands()
    {
        return [
            'name' => 'make', 
            'desc' => 'Make new migration or seeder or model or controller or view.', 
            'vars' => [
                [
                    'name' => '$type', 
                    'desc' => 'Can be one of the following values: '.$this->_color('migration', 'success').', '.
                            $this->_color('seeder', 'success').', '.
                            $this->_color('model', 'success').', '.
                            $this->_color('controller', 'success').' and '.
                            $this->_color('view', 'success').'.',
                ],
                [
                    'name' => '$name', 
                    'desc' => 'The name of the file.', 
                ],
                [
                    'name' => '$app', 
                    'desc' => 'The application name.', 
                ],
            ], 
        ];
    }

    /**
     *
     * make migration or seeder or model file.
     *
     */
    public function make($type, $name, $app='')
    {
        $this->_print('','',"\n");
        $templates_path = APPPATH .'controllers/Creator/templates/';
        // migration
        if($type == 'migration')
        {
            $table_name = strtolower($name);
            $class_name = 'Migration_'.ucfirst($table_name);
            $class_file = date('YmdHis').'_'.$table_name.'.php';
            $class_data = file_get_contents($templates_path.'migration.php');
            $class_data = str_replace('{class_name}', $class_name, $class_data);
            if(file_put_contents(config_item('migration_path').$class_file, $class_data))
            {
                $this->_print($class_file.' Created', 'success');
            }
            else
            {
                $this->_print($class_file.' Error', 'error');
            }
        }
        // seeder
        if($type == 'seeder')
        {
            $table_name = strtolower($name);
            $class_name = ucfirst($table_name).'_Seeder';
            $class_file = date('YmdHis').'_'.$table_name.'.php';
            $class_data = file_get_contents($templates_path.'seeder.php');
            $class_data = str_replace('{class_name}', $class_name, $class_data);
            if(file_put_contents(config_item('migration_path').'seeds/'.$class_file, $class_data))
            {
                $this->_print('seeds/'.$class_file.' Created', 'success');
            }
            else
            {
                $this->_print('seeds/'.$class_file.' Error', 'error');
            }
        }
        // model
        if($type == 'model')
        {
            $app_name   = ($app)?ucfirst($app).'/':'';
            $table_name = strtolower($name);
            $class_name = ucfirst($table_name).'_model';
            $class_file = 'models/'.$app_name.ucfirst($table_name).'_model.php';
            $class_data = file_get_contents($templates_path.'model.php');
            $class_data = str_replace('{class_name}', $class_name, $class_data);

            if(!file_exists(APPPATH.'models/'.$app_name))
            {
                if(mkdir(APPPATH.'models/'.$app_name))
                {
                    $this->_print('models/'.$app_name.' Created', 'success');
                }
                else
                {
                    $this->_print('models/'.$app_name.' Error', 'error');
                    return;
                }
            }

            if(!file_exists(APPPATH.$class_file))
            {
                if(file_put_contents(APPPATH.$class_file, $class_data))
                {
                    $this->_print($class_file.' Created', 'success');
                }
                else
                {
                    $this->_print($class_file.' Error', 'error');
                }
            }
        }
        // controller
        if($type == 'controller')
        {
            $app_name   = ($app)?ucfirst($app).'/':'';
            $table_name = strtolower($name);
            $class_name = ucfirst($table_name);

            if(!file_exists(APPPATH.'controllers/'.$app_name))
            {
                if(mkdir(APPPATH.'controllers/'.$app_name))
                {
                    $this->_print('controllers/'.$app_name.' Created', 'success');
                }
                else
                {
                    $this->_print('controllers/'.$app_name.' Error', 'error');
                    return;
                }
            }

            if(!file_exists(APPPATH.'controllers/'.$app_name.'routes.php'))
            {
                $class_data = file_get_contents($templates_path.'routes.php');
                $class_data = str_replace('{class_name}', $class_name, $class_data);
                $class_data = str_replace('{app_name}', trim($app_name,'/'), $class_data);
                if(file_put_contents(APPPATH.'controllers/'.$app_name.'routes.php', $class_data))
                {
                    $this->_print('controllers/'.$app_name.'routes.php'.' Created', 'success');
                }
                else
                {
                    $this->_print('controllers/'.$app_name.'routes.php'.' Error', 'error');
                }
            }

            $langs_dir = glob(APPPATH.'language/*', GLOB_ONLYDIR);
            foreach ($langs_dir as $key => $lang_dir)
            {
                // copy {appname}_lang.php file
                if(!file_exists($lang_dir.'/'.strtolower(trim($app_name,'/')).'_lang.php'))
                {
                    if(copy($templates_path.'lang.php', $lang_dir.'/'.strtolower(trim($app_name,'/')).'_lang.php'))
                    {
                        $this->_print(str_replace(APPPATH, '',$lang_dir).'/'.strtolower(trim($app_name,'/')).'_lang.php'.' Created', 'success');
                    }
                    else
                    {
                        $this->_print(str_replace(APPPATH, '',$lang_dir).'/'.strtolower(trim($app_name,'/')).'_lang.php'.' Error', 'error');
                    }
                }

                // copy global_{appname}_lang.php file
                if(!file_exists($lang_dir.'/global_'.strtolower(trim($app_name,'/')).'_lang.php'))
                {
                    if(copy($templates_path.'global_lang.php', $lang_dir.'/global_'.strtolower(trim($app_name,'/')).'_lang.php'))
                    {
                        $app_lang_line = "\$lang['".strtolower(trim($app_name,'/'))."'] = '".ucfirst(trim($app_name,'/'))."';\n";
                        file_put_contents($lang_dir.'/global_'.strtolower(trim($app_name,'/')).'_lang.php', $app_lang_line, FILE_APPEND);
                        $this->_print(str_replace(APPPATH, '',$lang_dir).'/global_'.strtolower(trim($app_name,'/')).'_lang.php'.' Created', 'success');
                    }
                    else
                    {
                        $this->_print(str_replace(APPPATH, '',$lang_dir).'/global_'.strtolower(trim($app_name,'/')).'_lang.php'.' Error', 'error');
                    }
                }

                // add language line for the controller
                $app_lang_line = "\$lang['".strtolower(trim($app_name,'/')."/".$class_name)."'] = '".ucfirst(trim($class_name))."';\n";
                if(file_put_contents($lang_dir.'/global_'.strtolower(trim($app_name,'/')).'_lang.php', $app_lang_line,  FILE_APPEND))
                {
                    $this->_print(str_replace(APPPATH, '',$lang_dir).'/global_'.strtolower(trim($app_name,'/')).'_lang.php'.' language line added', 'success');
                }
                else
                {
                    $this->_print(str_replace(APPPATH, '',$lang_dir).'/global_'.strtolower(trim($app_name,'/')).'_lang.php'.' Error adding language line', 'error');
                }
            }

            $class_file = 'controllers/'.$app_name.ucfirst($table_name).'.php';
            $class_data = file_get_contents($templates_path.'controller.php');
            $class_data = str_replace('{class_name}', $class_name, $class_data);
            $class_data = str_replace('{app_name}', trim($app_name,'/'), $class_data);
            if(!file_exists(APPPATH.$class_file))
            {
                if(file_put_contents(APPPATH.$class_file, $class_data))
                {
                    $this->_print($class_file.' Created', 'success');
                }
                else
                {
                    $this->_print($class_file.' Error', 'error');
                }
            }
        }

        // view
        if($type == 'view')
        {
            $app_name   = ($app)?strtolower($app).'/':'';
            $class_name = strtolower($name);
            $view_files = glob($templates_path.'view_*.php');

            if(!file_exists(APPPATH.'views/'.$app_name))
            {
                if(mkdir(APPPATH.'views/'.$app_name))
                {
                    $this->_print('views/'.$app_name.' Created', 'success');
                }
                else
                {
                    $this->_print('views/'.$app_name.' Error', 'error');
                    return;
                }
            }

            if(!file_exists(APPPATH.'views/'.$app_name.'layout.php'))
            {
                if(copy($templates_path.'layout.php', APPPATH.'views/'.$app_name.'layout.php'))
                {
                    $this->_print('views/'.$app_name.'layout.php Created', 'success');
                }
                else
                {
                    $this->_print('views/'.$app_name.'layout.php Error', 'error');
                }
            }

            if(!file_exists(APPPATH.'views/'.$app_name.$class_name))
            {
                if(mkdir(APPPATH.'views/'.$app_name.$class_name))
                {
                    $this->_print('views/'.$app_name.$class_name.' Created', 'success');
                }
                else
                {
                    $this->_print('views/'.$app_name.$class_name.' Error', 'error');
                    return;
                }
            }

            foreach ($view_files as $key => $view_file) {
                $class_file = 'views/'.$app_name.$class_name.'/'.str_replace($templates_path.'view_', '', $view_file);
                if(!file_exists(APPPATH.$class_file))
                {
                    $class_data = file_get_contents($view_file);
                    $class_data = str_ireplace('{class_name}', $class_name, $class_data);
                    $class_data = str_ireplace('{app_name}', trim($app_name,'/'), $class_data);

                    $class_data = str_ireplace('{Class_name}', ucfirst($class_name), $class_data);
                    $class_data = str_ireplace('{App_name}', ucfirst(trim($app_name,'/')), $class_data);

                    if(file_put_contents(APPPATH.$class_file, $class_data))
                    {
                        $this->_print($class_file . ' Created', 'success');
                    }
                    else
                    {
                        $this->_print($class_file . ' Error', 'error');
                    }
                }
            }
        }

        $this->_print('','',"\n");
    }

}

?>