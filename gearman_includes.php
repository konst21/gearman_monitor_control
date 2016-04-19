<?php
/**
 * Устанавливаем пути для подключаемых файлов.
 * Актуально для автолоадера классов
 */
$all_path = array(
    '/view/Smarty',
    '/classes'
);
foreach ($all_path as $path){
    $path = dirname(__FILE__).$path;
    set_include_path(get_include_path().PATH_SEPARATOR.$path);
}


/**
 * Автозагрузчик классов
 * @param $class_name
 */
function gearman_class_loader($class_name){

    /**
     * если происходит конфликт автолоадеров
     * или "чужие" классы через цепочку inclide*(), require*() цепляются
     * к этому автолоадеру, перечислите разрешенные в работе с Gearman классы
     * в этом массиве
     */

    $allowed_classes = array(
        'Template_Obj',
        'Smarty',
        'Gearman_Logmaker',
        'Gearman_Db',
        'G_Db',
        'Gmonitor_Settings',
        'Gearman_Monitor',
        'Debugger'
    );
    //if(!in_array($class_name, $allowed_classes)){ return; }


    if(strstr($class_name, 'Smarty')){
        $class_name = strtolower($class_name);
    }
    include_once $class_name.'.php';

}

spl_autoload_register('gearman_class_loader');


/**
 * Подключение шаблонов и Smarty
 * Все нужные пути прописываются и файлы подключаются при создании объекта Tpl_Obj_Gearman
 * Все пути относительные. Прописать требуется только пути к Smarty и шаблонам
 * Все пути - относительно корня веб-директории
 */
define('RELATIVE_PATH_TO_GEARMAN_TEMPLATES', dirname(__FILE__).'/view/tpl');

include_once 'G_Error.php';

 
