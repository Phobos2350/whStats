<?php
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

function queueTask($jobType, $tableName, $key, $data) {
  $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();

  $channel->queue_declare('task_queue', false, true, false, false);

  $job = array(
    'jobType' => $jobType,
    'tableName' => $tableName,
    'key' => $key,
    'data' => $data
  );
  $msg = new AMQPMessage(json_encode($job), array('delivery_mode' => 2));
  $channel->basic_publish($msg, '', 'task_queue');

  $channel->close();
  $connection->close();
}
