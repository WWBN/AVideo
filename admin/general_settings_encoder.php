<?php

$filter = array(
    'useEncoderNetworkRecomendation' => 'Let the encoder network (If configured) choose what is the best encoder to use',
    'doNotShowEncoderResolutionLow' => 'Do not allow encode in Low resolution',
    'doNotShowEncoderResolutionSD' => 'Do not allow encode in SD resolution',
    'doNotShowEncoderResolutionHD' => 'Do not allow encode in HD resolution',
    'makeVideosInactiveAfterEncode' => 'Maybe you need to approve or check something on your video before make it public');
createTable("CustomizeAdvanced", $filter);
