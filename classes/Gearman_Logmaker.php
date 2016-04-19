<?php
class Gearman_Logmaker{

    /**
     * Вставляем сообщение в лог
     * @param $operation
     */
    public static function save_log($operation){
        $db = new Gearman_Db();
        $db->log_insert($operation);
    }

}
 
