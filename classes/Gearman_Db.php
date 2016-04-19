<?php
class Gearman_Db extends G_Db{

    public function __construct(){

        $this->db_host = 'localhost';
        $this->db_name = '';
        $this->db_user = '';
        $this->db_pass = '';

        parent::__construct();
    }


    /**
     * Insert messages & errors into log
     * @param $message
     * @param null $type
     * @param null $file
     * @param null $line
     */
    public function log_insert($message, $type = null, $file = null, $line = null){
        $sql = <<<err
INSERT INTO logger SET
message = :message,
type = :type,
file = :file,
line = :line,
time = :time
err;
        $data = array(
            'message' => is_array($message)?implode(' ', $message):$message,
            'type' => $type,
            'file' => $file,
            'line' => $line,
            'time' => time()
        );

        $this->sql_prepare_and_execute($sql, $data);
    }

    /**
     * Select a specified number of the latest log records
     * @param int $number_last_records
     * @return array
     */
    public function log_select_last_records($number_last_records = 10){
        $sql = <<<ll
SELECT * FROM logger WHERE id > ((SELECT MAX(id) FROM logger) - :number)
ll;
        $data = array(
            'number' => $number_last_records,
        );

        $st = $this->sql_prepare_and_execute($sql, $data);

        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);

        return $raw_array;
    }

    /**
     * Select new records, "new records" is a records inserted after max_id value in cookie
     * @param $max_id
     * @return array
     */
    public function log_select_records_after_max_id($max_id){
        $sql = <<<ll
SELECT * FROM logger WHERE id > :max_id
ll;
        $data = array(
            'max_id' => $max_id,
        );

        $st = $this->sql_prepare_and_execute($sql, $data);

        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);

        return $raw_array;
    }

    /**
     * Search in log by user string
     * @param $search_string
     * @return array
     */
    public function log_select_records_by_search_string($search_string){
        $sql = "SELECT * FROM logger WHERE message LIKE '%$search_string%'";

        $st = $this->sql_prepare_and_execute($sql);

        $raw_array = $st->fetchAll(PDO::FETCH_ASSOC);

        return $raw_array;
    }

    /**
     * Max ID in log
     * @return string
     */
    public function log_select_max_id(){
        $sql = "SELECT MAX(id) FROM logger";
        $st = $this->sql_prepare_and_execute($sql);

        return $st->fetchColumn();
    }



}
 
