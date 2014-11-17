<?php
// Worker code can be anything you want.
echo "Starting HelloWorker at ".date('r')."\n";
echo "payload:";
$payload = getPayload();
print_r($payload);
 
for ($i = 1; $i <= 5; $i++) {
    echo "Sleep $i\n";
    sleep(1);
}
echo "HelloWorker completed at ".date('r');