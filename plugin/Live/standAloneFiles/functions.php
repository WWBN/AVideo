<?php
function getRestreamsRuning()
{
    $obj = new stdClass();
    $obj->error = true;
    $obj->msg = "";
    $obj->process = array();

    // Command to list processes that contain 'ffmpeg -re -rw_timeout'
    $command = "ps aux | grep 'ffmpeg -re -rw_timeout' | grep -v grep";

    // Execute the command
    exec($command, $output, $return_var);
    $restreams_ids = array();
    $liveTransmitionHistory_ids = array();

    // Check if the command was successful
    if ($return_var === 0) {
        foreach ($output as $line) {
            preg_match('/-i http.*(live|cdn.ypt.me)\/([^\/]+)\/index.m3u8.*/i', $line, $matches);

            // Extract the RTMP destination domain from the command line
            preg_match('/rtmps?:\/\/([^\/:]+)/i', $line, $destMatches);
            $restreamDomain = $destMatches[1];

            preg_match('/live_restreams_id=([0-9]+)/i', $line, $matchesLt);
            preg_match('/liveTransmitionHistory_id=([0-9]+)/i', $line, $matchesLth);

            $live_restreams_id = intval($matchesLt[1]);
            $liveTransmitionHistory_id = intval($matchesLth[1]);
            // Add process info to the response without the command line
            $obj->process[] = array(
                'key' => $matches[2],
                'live_restreams_id' => $live_restreams_id,
                'liveTransmitionHistory_id' => $liveTransmitionHistory_id,
                'restream_destination' => $restreamDomain,
                //'line' => $line,
            );
            $restreams_ids[] = $live_restreams_id;
            $liveTransmitionHistory_ids[] = $liveTransmitionHistory_id;
        }

        // Add total connections to the response
        $obj->restreams_ids = $restreams_ids;
        $obj->liveTransmitionHistory_ids = $liveTransmitionHistory_ids;
        $obj->error = false;
    } else {
        $obj->msg = "No processes found using 'ffmpeg -re -rw_timeout' or an error occurred.";
    }
    return $obj;
}


function isRestreamRuning($live_restreams_id, $liveTransmitionHistory_id){
    $obj = getRestreamsRuning();

    foreach ($obj->process as $key => $value) {
        if($live_restreams_id == $value['live_restreams_id'] AND $liveTransmitionHistory_id == $value['liveTransmitionHistory_id']){
            return $value;
        }
    }
    return false;
}