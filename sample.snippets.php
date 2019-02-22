<?php
$configs =  [
    'root_data' => 'xerox-widget/data',
    'hash' => 'conf-5d72e24687a152bffd38ff4631208752bb37a6d3', //config hash from widget
    'production' => false
];


$modx->getService('xerox','xerox',MODX_CORE_PATH.'components/xerox/model/',$configs);

$xerox_products = $modx->xerox->getXeroxProducts();
$rows = $xerox_products->asArray();
$update_rows = [];
$create_rows = [];
$response = '';

foreach($rows as $row){
    $resource = $modx->getObject('modResource', array('pagetitle' => $row['pagetitle']));
    $method = '';
    if($resource){
        $row['id'] = $resource->get('id');
        $response = $modx->runProcessor('resource/updatenooverride', $row);
        $update_rows[] = $row;
        $method = 'update';
    }else{
        $response = $modx->runProcessor('resource/create', $row);
        $create_rows[] = $row;
        $method = 'create';
    }
    if ($response->isError()) {
        $modx->log(modX::LOG_LEVEL_ERROR, $method.' error. Processor response: '.$response->getMessage());
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR, 'resource '. $method.' successfully');
    }
}