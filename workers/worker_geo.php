<?php
include_once '../../includes.php';
include_once '../gearman_includes.php';
$worker = new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('get_geo_data', 'get_geo_data');
function get_geo_data(GearmanJob $job){
    $address = $job->workload();
    $gl = new Gearman_Logmaker();
    $gl->save_log($address);
    $address_md5 = md5($address);
    $db = new Geo_Db();
    $cache_try = $db->select_geo_by_address_md5($address_md5);
    if(!$cache_try || !$cache_try['lat'] || !$cache_try['lng']){
        $geo = new Geo();
        $lat_lng = $geo->get_lat_lngd($address);
        if(!$lat_lng){
            $gl->save_log('FAIL for ' . $address);
            return;
        }
        $db->delete_geo_by_address_md5($address_md5);//если вдруг в БД записались null
        $db->insert_geo($address, $address_md5, $lat_lng['lat'], $lat_lng['lng']);
        $gl->save_log('GEO insert for ' . $address . ' || lat=' . $lat_lng['lat'] . ' || lng=' . $lat_lng['lng']);
    }
    return;
}
while($worker->work()){}

