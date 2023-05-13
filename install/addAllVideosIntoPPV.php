<?php
//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');

if (empty($ppv)) {
    return die('PayPerView plugin not enabled');
}


$plans = PPV_Plans::getAllActive();

if(empty($plans)){
    return die('there is no PPV plan');
}
$ppv_plans_id = 0;
if(count($plans) == 1){
    $ppv_plans_id = $plans[0]['id'];
}else{
    echo "What plan should I add?".PHP_EOL;
    foreach ($plans as $key => $value) {
        echo "{$value['id']} => {$value['name']}".PHP_EOL;
    }
    $ppv_plans_id = intval(readline(""));
}

if (!empty($userGroup)) {
    
    $videos = Video::getAllVideosLight('', false, true);
    
    foreach ($videos as $value) {
        $plan = new PPV_Plans_Videos(0);
        $plan->loadFromPlanVideo($ppv_plans_id, $value['id']);
        $plan->setStatus('a');
        $plan->save();
        
        echo "Videos_id {$value['id']} saved".PHP_EOL;
    }
}
echo "Bye";
echo "\n";
die();
