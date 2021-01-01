<?php
$filter = array(
    'encoderNetwork'=>'The <a target="_blank" href="https://github.com/WWBN/AVideo-Encoder-Network">Encoder Network</a> URL ',
    'useEncoderNetworkRecomendation'=>__('Let the encoder network (if configured) choose what is the best encoder to use'),
    'doNotShowUploadMP4Button'=>__('Users will not be able to directly upload, only use the encoder'),
    'doNotShowImportMP4Button'=>__('Disable the option to import MP4 videos from your local (Server) storage'),
    'doNotShowEncoderButton'=>__('Do not show the button to the encoder'),
    'doNotShowEmbedButton'=>__('Check this if you will not use embed videos on your site')
);
createTable("CustomizeAdvanced", $filter);
