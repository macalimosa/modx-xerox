<?php
$configs =  [
    'root_data' => 'xerox-widget/data',
    'hash' => 'your config hash', //config hash from widget
    'production' => true,
    'office_printer_id' => 1657, // parent resource
    'multifunction_printers_id' => 1658, // parent resource
    'production_printers_id' => 1659, // parent resource
    'template' => 4,
    'overwrite' => false
];


$modx->getService('xerox','xerox',MODX_CORE_PATH.'components/xerox/model/',$configs);

$xerox_products = $modx->xerox->getXeroxProducts()->asArray();

if($modx->xerox->checkFileSize()){
    $xerox_products = $modx->xerox->getXeroxProducts();
    $rows = $xerox_products->asArray();
    $update_rows = [];
    $create_rows = [];
    $response = '';
    foreach($rows as $row){
        $resource = $modx->getObject('modResource', array('alias' => $row['alias']));
        $method = '';
        if($resource){
            if($configs['overwrite']){
                $row['id'] = $resource->get('id');
                $response = $modx->runProcessor('resource/updatenooverride', $row);
                $update_rows[] = $row;
                $method = 'update';
            }
            
        }else{
           
            $response = $modx->runProcessor('resource/create', $row);
            $create_rows[] = $row;
            $method = 'create';
        }
        if ($response->isError()) {
            $modx->log(modX::LOG_LEVEL_ERROR, $method.' error. Processor response: '.$response->getMessage());
        } else {
            $modx->log(modX::LOG_LEVEL_ERROR, $row['pagetitle'].' resource '. $method.' successfully');
        }
    }
    //Test
    // echo '<pre>';
    // print_r($test);
    // echo '</pre>';
 
}else{
    $modx->log(modX::LOG_LEVEL_ERROR, 'No update at' . date('l jS \of F Y h:i:s A'));
}