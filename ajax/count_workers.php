<?php
include_once '../gearman_includes.php';

try{
   $monitor = new Gearman_Monitor();
}
catch(Exception $e){
    echo $e->getMessage();
    die();
}

$outer_html = '';
$tpl = new Template_Obj();

$workers_file_name = $monitor::workers_file_name();
foreach($workers_file_name as $file_name){
    $count = $monitor::count_workers_by_file_name($file_name);
    if($count > 0){
        $tpl->assign('file_name', $file_name);
        $tpl->assign('count', $count);
        $outer_html .= $tpl->fetch('gmonitor/count_workers.tpl');
    }
}

echo $outer_html;