<?php
class Debugger{

    /**
     * Просмотр дампа переменных в нормальном виде
     * @static
     * @param $data
     */
    public static function view_dump($data){
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }

    /**
     * Просмотр дампа массива в нормальном виде
     * @static
     * @param $data
     */
    public static function view_array($data){
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    public static function msg($text, $hlevel = 3){
        echo '<h' . $hlevel . '>' . $text . '</h' . $hlevel . '>';
    }
}
 
