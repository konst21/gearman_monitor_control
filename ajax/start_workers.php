<?php
include_once '../gearman_includes.php';

try{
   $monitor = new Gearman_Monitor();
}
catch(Exception $e){
   echo $e->getMessage();
   die();
}

$number_of_workers = (isset($_GET['number']) && $_GET['number'])?$_GET['number']:1;

$worker_file_name = (isset($_GET['worker_file_name']) && $_GET['worker_file_name'] != "1")?$_GET['worker_file_name']:null;

$success_counter = 0;
$fail_counter = 0;

for($i = 1; $i <= $number_of_workers; $i++){
    $worker_start_result = $monitor->start_worker($worker_file_name);
}

echo 'Success start: ' . $worker_start_result['success'] . ' workers<br>' .
    'Fail start:' . $worker_start_result['fail'] . ' worker<br>';