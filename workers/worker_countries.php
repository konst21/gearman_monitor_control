<?php

include_once '../../includes.php';
include_once '../gearman_includes.php';

$gearman_client = new GearmanClient();
$gearman_client->addServer('127.0.0.1', 4730);
$current_time = 0;
$gl = new Gearman_Logmaker();
$gearman_client->doBackground('countries_alarm_update', ' ');

$worker = new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('countries_alarm_update', 'countries_alarm_update');
function countries_alarm_update()
{
    global $gl;
    global $gearman_client;
    $url = Scanner::$root_url . '/' . Scanner::$countries_url;
    $scanner = new Scanner($url);

    echo "URL $url\n";

    $countries = $scanner->countries_get();
    $gl->save_log('Countries Start');
    $db = new Alarm_Db();

    $counter = 0;
    foreach($countries as $c) {
        if($c['link']){
            $country = $c['country'];
            $id = md5($c['country']);
            $c['id'] = $id;
            $id = $c['id'];
            $db = new Alarm_Db();
            $scanner = new Scanner(Scanner::$root_url . '/' . $c['link']);
            $db->select_all_alarmes();
            $block = $scanner->alarm_text_block_get();
            $alarmes = $scanner->alarm_get($block);
            $db->country_alarmes_insert($id, json_encode($alarmes));
            $gl->save_log("Country $country processed OK ");
            $counter++;
        }
    }

    $countries_db = $db->all_countries_select();
    foreach($countries_db as $c){
        $two_alarmes = $db->country_two_last_alarmes($c['id']);
        if($two_alarmes[0]['alarmes'] != $two_alarmes[1]['alarmes']){
            $db->country_event_insert($c['id']);
            $gl->save_log('New country ' . $c['country_ru'] . ' alarm, country_id = ' . $c['id']);
        }
    }

    sleep(86400);
    $gearman_client->doBackground('countries_alarm_update', ' ');
    return;
}

while($worker->work()){}