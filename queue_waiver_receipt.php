<?php
  require("phar://iron_worker.phar");

  $payload = array(
      'waiver_id' => 9,
      'recipients' => 'travishubbard@gmail.com',
  );

  $worker = new IronWorker();
  $res = $worker->postTask('waiver_receipt', $payload);
  print_r($res);
?>