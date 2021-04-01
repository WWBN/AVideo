<?php
$filter = array(
    'useEncoderNetworkRecomendation' => __('Let the encoder network (If configured) choose what is the best encoder to use'),
    'doNotShowEncoderResolutionLow' => __('Do not allow encode in Low resolution'),
    'doNotShowEncoderResolutionSD' => __('Do not allow encode in SD resolution'),
    'doNotShowEncoderResolutionHD' => __('Do not allow encode in HD resolution'),
    'makeVideosInactiveAfterEncode' => __('Maybe you need to approve or check something on your video before make it public'),
    'makeVideosUnlistedAfterEncode' => __('Maybe you need to approve or check something on your video before make it public')
);

createTable("CustomizeAdvanced", $filter);
