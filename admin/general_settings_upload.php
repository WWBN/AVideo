<?php
$filter = array(
        'encoderNetwork'=>'The <a target="_blank" href="https://github.com/DanielnetoDotCom/YouPHPTube-Encoder-Network">Encoder Network</a> URL ',
        'useEncoderNetworkRecomendation'=>'Let the encoder network (If configured) choose what is the best encoder to use',
        'doNotShowUploadMP4Button'=>'Users will not be able to direct upload, only use the encoder',
        'doNotShowImportMP4Button'=>'Disable the option to import MP4 videos from your local (Server) storage',
        'doNotShowEncoderButton'=>'Do not show the button to the encoder',
        'doNotShowEmbedButton'=>'Check this if you will not use embed videos on your site');
createTable("CustomizeAdvanced", $filter);