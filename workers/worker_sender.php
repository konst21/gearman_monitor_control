<?php
include_once '../../includes.php';
include_once '../gearman_includes.php';

$gearman_client = new GearmanClient();
$gearman_client->addServer('127.0.0.1', 4730);
$gearman_client->doBackground('alarm_sender', ' ');

$gl = new Gearman_Logmaker();

$worker = new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('alarm_sender', 'alarm_sender');


function alarm_sender(GearmanJob $job){
    global $gl;
    global $gearman_client;
    $db = new Alarm_Db();
    $gl->save_log('SOS alarm sender start');
    date_default_timezone_set( 'Europe/Moscow' );
    @$passengers = $db->get_all_passengers_data_by_arrival_date(date('Y-m-d', time()));

    $events = $db->get_all_events();
    if($events){
        foreach($events as $e){
            if($e['type'] == 'extreme'){
                $xtreme = $db->extreme_msg_by_id(intval($e['data']));
                $country_id = $xtreme['country_id'];
                $url = 'http://alarm.starliner.ru/viewer2/' . $country_id;
                $risk_link = Sender::url_shortener($url);
                $country_ru = $db->country_ru_by_country_id($country_id);
                $sms = $xtreme['sms'];
                $email_header = 'Опасность в стране ' . $country_ru;
                $email_body = <<<html
<p>Здравствуйте!</p>
<p>Ваше путешествие проходит через страну $country_ru. Для данной страны появилось предупреждение, <a href="$risk_link">подробности</a></p>
html;
            }
            elseif($e['type'] == 'country'){
                $country_id = $e['data'];
                $url = 'http://alarm.starliner.ru/viewer2/' . $country_id;
                $risk_link = Sender::url_shortener($url);
                $country_ru = $db->country_ru_by_country_id($country_id);
                $sms = 'Опасность в стране ' . $country_ru . ': ' . $risk_link;
                $email_header = 'Опасность в стране ' . $country_ru;
                $risk_link = $db->block_ru_select_by_id($country_id);
                $email_body = <<<html
<p>Здравствуйте!</p>
<p>Ваше путешествие проходит через страну $country_ru. Для данной страны появилось предупреждение, <a href="$risk_link">подробности</a></p>
html;


            }
            if(is_array($passengers) && count($passengers) > 0){
                foreach($passengers as $p){
                    $ph = $p['phone'];
                    $em = $p['email'];
                    if($country_id == $p['country_id']){
                        if(!$db->log_send_by_phone_get($country_id, $p['phone'])){ // отправки товарисчу по этой стране не было
                            Sender::sms($p['phone'], $sms);
                            $gl->save_log("SOS Отправлено SMS на номер $ph, страна $country_ru");
                            Sender::email($p['email'], $email_header, $email_body);
                            $gl->save_log("Отправлен Email на адрес $em, страна $country_ru");
                            $db->log_send_insert($country_id, $p['phone']);
                            //echo $country_id;
                        }
                        else{ // уже отправляли
                            $gl->save_log("SMS на номер $ph, страна $country_ru, УЖЕ ОТПРАВЛЕНО");
                            $gl->save_log("Email на адрес $em, страна $country_ru, УЖЕ ОТПРАВЛЕН");
                        }
                    }
                }
            }
        }

        $db->update_event($e['id']);
    }
    else{
        $gl->save_log("Нет событий для отправки");
    }
    sleep(30);

    //самовозрождающаяся задача - Феникс
    $gearman_client->doBackground('alarm_sender', ' ');
    return;
}

while($worker->work());