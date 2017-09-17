cd tests/ab

wstest -m fuzzingserver -s fuzzingserver.json &
sleep 5
php clientRunner.php

sleep 2

php startServer.php &
sleep 3
wstest -m fuzzingclient -s fuzzingclient.json
