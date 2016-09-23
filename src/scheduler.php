<?php
require_once(dirname(__FILE__).'../../vendor/autoload.php');
require_once 'generateStats.php';
require_once 'rethinkQueries.php';
date_default_timezone_set('Etc/GMT');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueListener
{
  public function __construct(){
    $this->statsGenerator = new GenerateStats();
    $this->rethinkQueries = new RethinkQueries();
  }

  public function listenToQueue() {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $channel->queue_declare('task_queue', false, true, false, false);
    $channel->basic_qos(null, 1, null);
    $channel->basic_consume('task_queue', '', false, false, false, false, array($this, 'queueCallback'));

    while(count($channel->callbacks)) {
      $channel->wait();
    }
    $channel->close();
    $connection->close();
  }

  public function queueCallback($msg) {

    $decodedMsg = json_decode($msg->body, true);
    switch($decodedMsg['jobType']) {
      case 'genStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - genStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        $this->statsGenerator->genStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - genStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        break;
      case 'getStatsPeriod':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getStatsPeriod - ".$decodedMsg['data']['period']." ({$decodedMsg['key']})\n");
        $newTask = $this->rethinkQueries->getStatsPeriod($decodedMsg['data']['period']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newTask);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getStatsPeriod - ".$decodedMsg['data']['period']." ({$decodedMsg['key']})\n");
        break;
      case 'getStatsMonth':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getStatsMonth - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newTask = $this->rethinkQueries->getStatsMonth($decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newTask);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getStatsMonth - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getEntityStatsPeriod':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getEntityStatsPeriod - ".$decodedMsg['data']['tz']."_".$decodedMsg['data']['period']." ({$decodedMsg['key']})\n");
        $newTask = $this->rethinkQueries->getEntityStatsPeriod($decodedMsg['data']['tz'], $decodedMsg['data']['period']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newTask);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getEntityStatsPeriod - ".$decodedMsg['data']['tz']."_".$decodedMsg['data']['period']." ({$decodedMsg['key']})\n");
        break;
      case 'getEntityStatsMonth':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getEntityStatsMonth - ".$decodedMsg['data']['tz'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newTask = $this->rethinkQueries->getEntityStatsMonth($decodedMsg['data']['tz'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newTask);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getEntityStatsMonth - ".$decodedMsg['data']['tz'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getEntityStatsMonthByID':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getEntityStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newTask = $this->rethinkQueries->getEntityStatsMonthByID($decodedMsg['data']['id'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newTask);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getEntityStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      default:
        break;
    }
  }
}
