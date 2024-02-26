<?php

$cmd = 'ps aux | grep YPTSocket';
exec($cmd);

$cmd = 'cat /proc/56529/limits | grep open';
exec($cmd);