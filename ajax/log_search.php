<?php
include_once '../gearman_includes.php';

$db = new Gearman_Db();

/**
 * Make the string for interface
 * @param $rows
 * @return array
 */
function search_row_handler($rows){
    $outer_array = array();
    foreach($rows as $index => $log_row){
        $log_row['ctime'] = date('H:i:s', $log_row['time']);
        $log_row['date'] = date('d/m/Y', $log_row['time']);
        $log_row['odd_class'] = '';
        //подкрашиваем строки через одну
        //в разные цвета
        if($index & 1){
            $log_row['odd_class'] = 'row_odd_search';
        }
        else{
            $log_row['odd_class'] = 'row_no_odd_search';
        }
        $log_row['log_msg'] = $log_row['message'] .
            ((!is_null($log_row['file']))? ' | file: ' . $log_row['file']:'')  .
                        ((!is_null($log_row['line']))? ' | line: ' . $log_row['line']:'');
        $outer_array[] = $log_row;

    }

    //разворачиваем массив, иначе будет отображаться выборка из БД в обратном порядке,
    //а нам нужно сверху вниз по убыванию времени
    $outer_array = array_reverse($outer_array);

    return $outer_array;
}

//это объект Smarty с настроенными путями к шаблонам
$tpl = new Template_Obj();

$outer_array = array();

$search_text = $_GET['search_text'];

$rows = $db->log_select_records_by_search_string($search_text);

$outer_array = search_row_handler($rows);

setcookie('search_text', $search_text, 0, '/');

//выдаем строки в шаблон
$tpl->assign('rows', $outer_array);

//ну и отображаем строки для веб-интерфейса (см. файл gearman.js, ф-я view_log())
$tpl->display('gmonitor/log_row.tpl');