<?php
// Incident hardening: configurable FFmpeg values are data, never shell source.
function avideoSafeFFmpegParameters($value) {
    if (!is_string($value) || strlen($value) > 2048 || preg_match('/[^\x20-\x7E]/', $value)) {
        throw new InvalidArgumentException('Invalid FFmpeg profile');
    }
    $tokens = preg_split('/ +/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
    if (!$tokens || count($tokens) > 40 || count($tokens) % 2) {
        throw new InvalidArgumentException('FFmpeg options require option/value pairs');
    }
    $enums = [
        '-c:v' => ['libx264','libx265','h264','hevc','mpeg4','vp8','vp9','libvpx','libvpx-vp9','copy'],
        '-vcodec' => ['libx264','libx265','h264','hevc','mpeg4','libvpx','libvpx-vp9','copy'],
        '-c:a' => ['aac','libmp3lame','libopus','libvorbis','alac','copy'],
        '-acodec' => ['aac','libmp3lame','libopus','libvorbis','alac','copy'],
        '-preset' => ['ultrafast','superfast','veryfast','faster','fast','medium','slow','slower','veryslow'],
        '-pix_fmt' => ['yuv420p','yuv422p','yuv444p','yuv420p10le'],
        '-profile:v' => ['baseline','main','high','high10'],
        '-movflags' => ['+faststart','faststart'],
    ];
    $seen = [];
    for ($i=0; $i<count($tokens); $i+=2) {
        $option=$tokens[$i]; $arg=$tokens[$i+1];
        if (isset($seen[$option])) throw new InvalidArgumentException('Duplicate FFmpeg option');
        $seen[$option]=true;
        if (isset($enums[$option])) {
            $ok=in_array($arg,$enums[$option],true);
        } elseif (in_array($option,['-b:v','-b:a','-maxrate','-bufsize'],true)) {
            $ok=(bool)preg_match('/^[1-9][0-9]{0,6}[kKmM]?$/D',$arg);
        } elseif ($option==='-crf') {
            $ok=(bool)preg_match('/^[0-9]{1,2}$/D',$arg) && (int)$arg<=51;
        } elseif ($option==='-ar') {
            $ok=in_array($arg,['8000','16000','22050','32000','44100','48000','96000'],true);
        } elseif ($option==='-ac') {
            $ok=(bool)preg_match('/^[1-8]$/D',$arg);
        } elseif ($option==='-level' || $option==='-level:v') {
            $ok=(bool)preg_match('/^[1-6](?:\.[0-2])?$/D',$arg);
        } else {
            $ok=false;
        }
        if (!$ok) throw new InvalidArgumentException('Unsupported FFmpeg option or value');
    }
    return implode(' ',array_map('escapeshellarg',$tokens));
}
