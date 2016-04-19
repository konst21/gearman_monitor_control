<?php
/**
 * Это "фейковый" воркер
 * Служит для сброса очереди по определенной функции, алгоритм такой:
 * он регистрирует на сервере очередей функцию с нужным именем, и с вот таким кодом (например, имя
 * сбрасываемой задачи task1):
 * function task1(){}
 * вся очередь быстро прогоняется через это функцию, и таким образом сбрасывается.
 * После сброса фейковый воркер удаляется из процессов точно также, как и обычный,
 * методом exit_fake_worker() класса Gearman_Monitor
 * Системная команда для вызова: fake_worker.php function_name_for_reset
 */

include_once '../gearman_includes.php';

//fake worker start nessessary with argument, f.e. php fake_worker.php function_name_for_reset
//function name or function synonym
$function_name = $argv[1];

$func_synonym = array_search($function_name, Gmonitor_Settings::$func_name_synonyms);
if($func_synonym){
    $function_name = $func_synonym;
}

eval('function ' . $function_name . '(){};');

$worker = new GearmanWorker();
$worker->addServer(Gearman_Monitor::$host, Gearman_Monitor::$port);
$worker->addFunction($function_name, $function_name);
while($worker->work()){}

 
