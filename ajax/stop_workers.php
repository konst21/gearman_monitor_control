<?php
include_once '../gearman_includes.php';

try{
   $monitor = new Gearman_Monitor();
}
catch(Exception $e){
   echo $e->getMessage();
   die();
}

$workers_for_stop = (isset($_GET['worker_file_name']) && $_GET['worker_file_name'] != '1')?$_GET['worker_file_name']:null;

$worker_stop = $monitor->exit_workers($workers_for_stop);
if($worker_stop){
    if($workers_for_stop){
        Gearman_Logmaker::save_log('Worker ' . $workers_for_stop . ' stopped');
        echo 'Worker ' . $workers_for_stop . ' stopped';
    }
    else{
        Gearman_Logmaker::save_log('All Workers Stopped');
        echo 'All Workers Stopped';
    }

}
else {
    Gearman_Logmaker::save_log('FAIL Worker Stop');
    echo 'Workers NOT stopped';
}

 
