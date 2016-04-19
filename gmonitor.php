<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

setcookie('max_id', null, time() - 365*86400, '/');
setcookie('search_text', null, time() - 365*86400, '/');

include_once 'gearman_includes.php';

$tpl = new Template_Obj();

$tpl->assign('page_title', 'Gearman Monitor');

$tpl->assign('refresh_interval', 2000);

$tpl->display('gmonitor/page_header.tpl');

$tpl->display('gmonitor/interval_div.tpl');

$tpl->assign('workers_file_name', Gearman_Monitor::workers_file_name());

/*$tpl->display('gmonitor/workers_table.tpl');

$tpl->display('gmonitor/functions_table.tpl');*/
$tpl->display('gmonitor/main_table.tpl');

$tpl->display('gmonitor/log_view.tpl');

$tpl->display('gmonitor/page_footer.tpl');
