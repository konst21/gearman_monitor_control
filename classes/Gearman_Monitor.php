<?php
class Gearman_Monitor{

    /**
     * Gearman host
     * default localhost
     * @var string
     */
    public static $host = '127.0.0.1';

    /**
     * Gearman port
     * default 4730
     * @var int
     */
    public static $port = 4730;

    /**
     * @var int
     */
    protected $timeout = 30;

    /**
     * @var bool|\resource
     */
    protected $connection_handler = false;

    /**
     * workers names with full path to each worker
     * @return array
     */
    public static function workers_file_name(){

        $file_names_array = array();
        $raw_names = scandir(self::full_path_to_workers());
        foreach($raw_names as $file_name){
            if(is_file(self::full_path_to_workers() . $file_name)){
                $file_names_array[] = $file_name;
            }
        }
        return $file_names_array;
    }

    /**
     * full path to worker's directory
     * (with slash)
     * default gmonitor/workers
     * replace directory if need
     * @static
     * @return string
     */
    public static function full_path_to_workers(){
        return dirname(dirname(__FILE__)) . '/workers/';
    }



    /**
     * @throws Exception
     * When the object is created immediately open a socket to the server,
     * failure - generated exception
     * Exception should be handled with each object creation
     *
     * The most common situation generate this exception is not running the server
     * or not connections to it
    */
    public function __construct(){
        $this->connection_handler = @fsockopen(self::$host, self::$port, $errno, $errstr, $this->timeout);
        if(!$this->connection_handler){
            throw new Exception("Error! Msg: " . $errstr . " ; Code: ". $errno);
        }
    }

    /**
     * Send command to Gearman server
     * in this web-application use only commands 'status' and 'workers'
     * @param  $cmd
     * @return void
     */
    protected function send_cmd($cmd){
        fwrite($this->connection_handler, $cmd."\r\n");
    }

    /**
     * Receive data from Gearman server after command send
     * @return string
     */
    protected function receive_cmd_data(){
        $full_response = '';
        while (true) {
            $data = fgets($this->connection_handler , 4096);
            if ($data == ".\n") {
                break;
            }
            $full_response .= $data;
        }

        return $full_response;
    }

    /**
     * close socket after work
     */
    public function __destruct(){
        if(is_resource($this->connection_handler)){
           fclose($this->connection_handler);
        }
    }

    /**
     * The full list of functions that are registered on the server
     * Treatment done in the situation when the function was once registered
     * and Gearman responds that such a function is, but she 0 queue 0 in process and 0 for workers
     * @return array
     */
    public function all_functions_statuses(){
        $this->send_cmd('status');
        $raw_data =  $this->receive_cmd_data();

        $status = array();
        $temp_array = explode("\n", $raw_data);
        foreach ($temp_array as $item) {

            $raw_array = explode("\t", $item);

            if(is_array($raw_array) AND count($raw_array) == 4){

                if($raw_array[1] != 0 OR $raw_array[2] !=0 OR $raw_array[3] != 0){
                    $status[$raw_array[0]] = array(
                        'in_queue' => $raw_array[1],
                        'jobs_running' => $raw_array[2],
                        'capable_workers' => $raw_array[3]
                    );
                }

            }
        }

        return $status;
    }

    /**
     * The list of active functions on Gearman server (either task for this function in queue
     * or worker with this function is running)
     * @return array
     */
    public function all_functions_list(){
        $raw_statuses = $this->all_functions_statuses();
        return array_keys($raw_statuses);
    }

    /**
     * function status, false if none
     * @param  $function_name
     * @return bool
     */
    public function function_status($function_name){
        $all_func_array = $this->all_functions_statuses();
        if(!array_key_exists($function_name, $all_func_array)){
            return false;
        }
        return $all_func_array[$function_name];
    }

    /**
     * reset the task queue for the function
     * @param  $function_name
     * @return bool
     */
    public function reset_task($function_name){

        $raw_func_array = $this->function_status($function_name);
        $number_of_func = $raw_func_array['in_queue'];

        $this->fake_worker_start($function_name);

        //danger cycle, but...
        //wait until the queue is cleared
        while($number_of_func !=0){

            $raw_func_array = $this->function_status($function_name);
            $number_of_func = $raw_func_array['in_queue'];

            //small delay
            usleep(10000);
        }
        $this->fake_worker_exit();
    }

    /**
     * Reset the entire queue - reset each task
     * @return void
     */
    public function reset_all_queue(){
        $functions_list = $this->all_functions_list();
        foreach($functions_list as $function){
            $this->reset_task($function);
        }
    }

    /**
     * All workers, registered on Gearman server
     * @return array
     */
    public function workers_list(){
        $this->send_cmd('workers');
        $raw_workers = $this->receive_cmd_data();
        $workers = array();
        $temp_array = explode("\n", $raw_workers);
        foreach ($temp_array as $item) {

            $z = explode(" : ", $item);
            if(is_array($z) AND count($z) > 1){
                $info = $z[0];
                $functions = $z[1];
                list($fd, $ip, $id) = explode(' ', $info);

                $functions = explode(' ', trim($functions));

                if(is_array($functions) AND count($functions) > 0){
                    $workers[] = array(
                        'fd' => $fd,
                        'ip' => $ip,
                        'id' => $id,
                        'functions' => $functions
                    );
                }
            }
        }

        return $workers;
    }

    /**
     * Stop workers (by Linux system command)
     * You can stop one worker by file name or all workers, registered on server
     * @param $file_name - имя файла воркера
     * @return string
     */
    public function exit_workers($file_name = null){

        //if "$file_name", stop worker by file name, else stop all workers
        $workers_for_stop = (is_null($file_name))?self::workers_file_name():array($file_name);

        $workers_count = 0;

        foreach($workers_for_stop as $worker_name){

            exec("ps ax | grep ". $worker_name ." | awk '{print $1}' | xargs kill");
            $workers_count += $this->count_workers_by_file_name($file_name);
        }
        //pause while all workers stop
        sleep(1);

        if($workers_count != 0){
            return false;
        }
        return true;
    }

    /**
     * Start worker (by Linux system command)
     * Because one of the most frequent problems start of the worker is a wrong paths,
     * write full path to workers in log if worker start is fail
     * @param $file_name - worker's file name
     * @return bool
     */
    public function start_worker($file_name = null){
        $success_counter = 0;
        $fail_counter = 0;

        if(is_null($file_name)){
            $start_array = self::workers_file_name();
        }
        else{
            $start_array = array($file_name);
        }


        foreach($start_array as $worker_name){
            $before_start = self::count_workers_by_file_name($worker_name);
            $ctl_string = "php ". self::full_path_to_workers() . $worker_name ." > /dev/null &"; //echo $ctl_string;
            exec($ctl_string);
            usleep(30000);
            $after_start = self::count_workers_by_file_name($worker_name);
            if($after_start == $before_start + 1){
                Gearman_Logmaker::save_log('Worker ' . $worker_name . ' start SUCCESS');
                $success_counter++;
            }
            else{
                Gearman_Logmaker::save_log('Worker ' . self::full_path_to_workers() . $worker_name . ' start FAIL');
                $fail_counter++;
            }
        }


        return array(
            'success' => $success_counter,
            'fail' => $fail_counter,
        );

    }

    /**
     * Fake worker needed for reset tasks and queue
     * Attention! Fake worker located in worker's directory and have a "fake_worker.php" file name
     * Change this if you need in this function
     * @param $function_name
     */
    public function fake_worker_start($function_name){

        $ctl_string = "php ". self::full_path_to_workers() . "fake_worker.php $function_name > /dev/null &";

        exec($ctl_string);

        Gearman_Logmaker::save_log('fake worker start');
    }

    /**
     * @return string
     */
    public function fake_worker_exit(){

        exec("ps ax | grep fake_worker.php | awk '{print $1}' | xargs kill");

        Gearman_Logmaker::save_log('fake worker exit');
    }

    public static function count_workers_by_file_name($file_name){
        $command_string = "ps ax | grep " . $file_name;
        ob_start();
        system($command_string);
        $raw_out = ob_get_contents();
        ob_clean();

        $workers_count = count(explode(self::full_path_to_workers() . $file_name, $raw_out)) - 1;

        return $workers_count;
    }

}
 
