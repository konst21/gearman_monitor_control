<?php
class G_Db{

    /**
     * Принцип: при создании объекта сразу соединяемся с БД, получаем PDO handler
     * и далее его используем для исполнения SQL
     */

    /**
     * Параметры соединения с БД
     */
    protected $db_host = '';
    protected $db_name = '';
    protected $db_user = '';
    protected $db_pass = '';

    /**
     * Идентификатор соединения с БД
     */
    protected $handler;

    /**
     * При создании объекта сразу соединяемся с БД, handler потом используется методами класса
     */
    public function __construct(){
        if(isset($this->db_user)){
            $this->handler = $this->get_db_handler();
            //костыль для кодировки
            $this->handler->exec('SET NAMES UTF8');
        }
    }

    /**
     * @static
     * @param  $db_host
     * @param  $db_name
     * @param  $db_user
     * @param  $db_pass
     * @return PDO
     */
    protected function db_connect($db_host, $db_name, $db_user, $db_pass) {
        try {
           $dbHandler = new PDO('mysql:host='.$db_host.';dbname='.$db_name.'', $db_user, $db_pass);
        }
        catch(PDOException $e) {
            echo 'Db error '.$e->getMessage();
            die();
        }

        return $dbHandler;
    }

    /**
     * @return PDO
     */
    protected function get_db_handler(){
        $handler = $this->db_connect($this->db_host, $this->db_name, $this->db_user, $this->db_pass);
        return $handler;
    }

    /**
     * Подготовка и выполнение SQL-запросов с параметрами
     * @throws Exception
     * @param  $sql
     * @param array $data
     * @return PDOStatement
     */
    protected function sql_prepare_and_execute($sql, $data = array()){

        $handler = $this->handler;

        //Костыль для кодировки
        $handler->exec('SET NAMES UTF8');

        $st = $handler->prepare($sql);
        $result = $st->execute($data);
        if(!$result){
            throw new Exception('DB error: '.implode($st->errorInfo(), ' '));
        }

        return $st;
    }

    /**
     * Ф-я преобразует результат выборки PDO из БД в ассоциативный массив вида
     * [0] => array(...)
     * [1] => array(...)
     * где каждый вложенный массив - строка (запись) таблицы
     * аргумент - результат работы ф-и sql_prepare_and_execute()
     * возвращает false, если число записей в выборке == 0,
     * либо массив
     * ф-я фактически просто синоним метода PDO fetchAll с параметром FETCH_ASSOC, т.е. fetchAll(PDO::FETCH_ASSOC)
     * более интересные ф-и - ниже
     * @param PDOStatement $st
     * @return array|bool
     */
    protected function st_to_array(PDOStatement $st){
        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }
        return $raw_array;
    }

    /**
     * Ф-я используется, если требуется обеспечить уникальность каждого элемента выборки
     * Ф-я преобразует результат выборки PDO из БД в ассоциативный массив вида
     * [id0] => array(id0, ...)
     * [id1] => array(id1, ...)
     * [id2] => array(id2, ...)
     * где каждый вложенный массив - строка (запись) таблицы
     * возвращает false, если число записей в выборке == 0,
     * либо массив
     * @param PDOStatement $st
     * @param $id_field_name - имя поля, в котором находится уникальный параметр
     * @return array|bool
     */
    protected function st_to_array_with_id(PDOStatement $st, $id_field_name = 'id'){
        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }
        $outer_array = array();
        foreach($raw_array as $item){
            $outer_array[trim($item[$id_field_name])] = $item;
        }

        return $outer_array;
    }

    /**
     * Ф-я преобразует результат выборки PDO из БД в простой массив вида
     * [0] => value0
     * [1] => value1
     * [2] => value2
     * Применяется при выборке одного поля (столбца) из БД
     * возвращает false, если число записей в выборке == 0,
     * либо массив
     * @param PDOStatement $st
     * @return array|bool
     */
    protected function st_to_simple_array(PDOStatement $st){
        $raw_array = $st->fetchAll(PDO::FETCH_NUM);
        if(!is_array($raw_array) || count($raw_array) == 0){
            return false;
        }

        $outer_array = array();

        foreach($raw_array as $item){
            $outer_array[] = $item[0];
        }

        return $outer_array;
    }

    protected function st_to_value(PDOStatement $st){
        $raw_value = $st->fetchColumn();
        if(!$raw_value || is_null($raw_value) || empty($raw_value)){
            return false;
        }
        return $raw_value;
    }




}
 
