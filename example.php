<?php
$gclient = new GearmanClient();
$gclient->addServer('localhost');
for($i = 0; $i < 100; $i++){
    $data = array(
        mt_rand(1, 1000),
        mt_rand(1, 1000),
    );

    $data_for_gearman = serialize($data);

    $gclient->doBackground('summ', $data_for_gearman);
    $gclient->doBackground('muliply', $data_for_gearman);
    $gclient->doBackground('subtract', $data_for_gearman);
    $gclient->doBackground('divide', $data_for_gearman);
}
 
