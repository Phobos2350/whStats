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
      case 'addStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - addStats - ".$decodedMsg['data']['kill']['killID']." - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $this->statsGenerator->addStats($decodedMsg['data']['kill'], $decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - addStats - ".$decodedMsg['data']['kill']['killID']." - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."({$decodedMsg['key']})\n");
        $newData = $this->rethinkQueries->getStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newData);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."({$decodedMsg['key']})\n");
        break;
      case 'genEntityStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - genEntityStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        $this->statsGenerator->genEntityStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - genEntityStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        break;
      case 'addEntityStatsMonth':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - addEntityStatsMonth - ".$decodedMsg['data']['kill']['killID'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $this->statsGenerator->addEntityStatsMonth($decodedMsg['data']['kill'], $decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - addEntityStatsMonth - ".$decodedMsg['data']['kill']['killID'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getEntityStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getEntityStats - ".$decodedMsg['data']['period'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newData = $this->rethinkQueries->getEntityStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newData);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getEntityStats - ".$decodedMsg['data']['period'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'genPilotStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - genPilotStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        $this->statsGenerator->genPilotStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - genPilotStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."\n");
        break;
      case 'addPilotStatsMonth':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - addPilotStatsMonth - ".$decodedMsg['data']['kill']['killID'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $this->statsGenerator->addPilotStatsMonth($decodedMsg['data']['kill'], $decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - addPilotStatsMonth - ".$decodedMsg['data']['kill']['killID'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getPilotStats':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getPilotStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."({$decodedMsg['key']})\n");
        $newData = $this->rethinkQueries->cachePilotStats($decodedMsg['data']['period'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newData);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getPilotStats - ".$decodedMsg['data']['period']." - ".$decodedMsg['data']['year']." - ".$decodedMsg['data']['month']."({$decodedMsg['key']})\n");
        break;
      case 'getPilotStatsMonthByID':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getPilotStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newData = $this->rethinkQueries->getPilotStatsMonthByID($decodedMsg['data']['id'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newData);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getPilotStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      case 'getEntityStatsMonthByID':
        printf("[ ] ".date("Y-m-d H:i:s")." - Running Task - getEntityStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        $newData = $this->rethinkQueries->getEntityStatsMonthByID($decodedMsg['data']['id'], $decodedMsg['data']['year'], $decodedMsg['data']['month']);
        $this->rethinkQueries->setCache($decodedMsg['tableName'], $decodedMsg['key'], $newData);
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        printf("[x] ".date("Y-m-d H:i:s")." - TASK COMPLETE - getEntityStatsMonthByID - ".$decodedMsg['data']['id'].'_'.$decodedMsg['data']['year'].'_'.$decodedMsg['data']['month']." ({$decodedMsg['key']})\n");
        break;
      default:
        break;
    }
  }
}
