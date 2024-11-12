<button class="btn btn-default btn-block btn-xs" id="performanceSettingsBTN" data-toggle="collapse" data-target="#performanceSettings" aria-expanded="false" aria-controls="performanceSettings">
    <i class="fas fa-tachometer-alt"></i> 
    <?php echo __("Optimal performance settings"); ?>
    <i class="fas fa-caret-down"></i>
</button>

<div id="performanceSettings" class="collapse">
    <ul class="list-group small">
        <li class="list-group-item"><i class="fas fa-tv"></i> <strong><?php echo __("Resolution"); ?>:</strong> 1280x720 (720p @ 30 FPS)</li>
        <li class="list-group-item"><i class="fas fa-signal"></i> <strong><?php echo __("Video Bitrate"); ?>:</strong> 1,500 - 4,000 Kbps</li>
        <li class="list-group-item"><i class="fas fa-volume-up"></i> <strong><?php echo __("Audio Bitrate"); ?>:</strong> 128 - 256 Kbps</li>
        <li class="list-group-item"><i class="fas fa-microphone-alt"></i> <strong><?php echo __("Audio Codec"); ?>:</strong> AAC, Stereo, 44.1kHz/48kHz</li>
    </ul>
</div>