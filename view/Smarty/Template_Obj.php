<?php
set_include_path(get_include_path() . PATH_SEPARATOR .dirname(__FILE__).'/libs');
set_include_path(get_include_path() . PATH_SEPARATOR .dirname(__FILE__).'/libs/sysplugins');
include_once 'Smarty.class.php';


class Template_Obj extends Smarty{

    public function __construct(){

        parent::__construct();
        $this->template_dir = RELATIVE_PATH_TO_GEARMAN_TEMPLATES;
        $this->compile_dir = dirname(__FILE__).'/smarty_dirs/templates_c/';
        $this->config_dir = dirname(__FILE__).'/smarty_dirs/configs/';
        $this->cache_dir = dirname(__FILE__).'/smarty_dirs/cache/';
        $this->caching = false;

        $this->assign('path_to_view', 'view');
    }
}
 
