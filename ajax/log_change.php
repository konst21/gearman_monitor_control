<?php
include_once '../gearman_includes.php';

$db = new Gearman_Db();

/**
 * Make the string for interface
 * @param $rows
 * @return array
 */
function row_handler($rows){
    $outer_array = array();
    foreach($rows as $log_row){
        $log_row['ctime'] = date('H:i:s', $log_row['time']);
        $log_row['date'] = date('d/m/Y', $log_row['time']);
        $log_row['odd_class'] = '';
        //подкрашиваем строки через одну
        if($log_row['id'] & 1){
            $log_row['odd_class'] = 'row_odd';
        }
        $log_row['log_msg'] = $log_row['message'] .
            ((!is_null($log_row['file']))? ' | file: ' . $log_row['file']:'')  .
                        ((!is_null($log_row['line']))? ' | line: ' . $log_row['line']:'');
        $outer_array[] = $log_row;

    }

    //reverse the array - last log events becomes first
    $outer_array = array_reverse($outer_array);

    return $outer_array;
}

//this is a Smarty object with correct paths
$tpl = new Template_Obj();

/**
 * this section defines that giving from a log file.
 * if this is the beginning of work, the first few rows (setting Gmonitor_Settings::$initial_count_log_rows)
 * or all, if they are less than the established value in Gmonitor_Settings::$initial_count_log_rows
 * if continuation of work add only events which occurred in session time
 */
$outer_array = array();

//this is begin of work  - display some last events from log
//and set cookie max_id equal last log event ID
if(!array_key_exists('max_id', $_COOKIE) || !$_COOKIE['max_id']){

    $rows = $db->log_select_last_records(Gmonitor_Settings::$initial_count_log_rows);

    $outer_array = row_handler($rows);

    $tpl->assign('rows', $outer_array);


    $max_id = $db->log_select_max_id();
    setcookie('max_id', $max_id, 0, '/');

}
//this is log update - add only new event's rows
else{
    $rows = $db->log_select_records_after_max_id($_COOKIE['max_id']);

    $outer_array = row_handler($rows);

    $tpl->assign('rows', $outer_array);

    if(count($outer_array) > 0){
        $max_id = $db->log_select_max_id();
        setcookie('max_id', $max_id, 0, '/');
    }
}


//display rows for web-interface (view gearman.js, function view_log())
$tpl->display('gmonitor/log_row.tpl');