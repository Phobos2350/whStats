<?php
require_once(dirname(__FILE__).'../../vendor/autoload.php');
require_once(dirname(__FILE__)."../../vendor/danielmewes/php-rql/rdb/rdb.php");
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
date_default_timezone_set('Etc/GMT');

class CacheManager {
  
  public function getCache($tableName, $key, $expiry) {
    $conn = r\connect('localhost', 28015, 'stats');
    $result = r\table($tableName)->get($key)->run($conn);
    if($result == null) {
      $conn->close();
      return array('refreshDue' => true, 'statsArray' => null, 'lastCached' => null);
    }
    if($result['cacheTime'] > strtotime('-'.$expiry.' minutes')) {
      $returnData = array('refreshDue' => false, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', $result['cacheTime']));
      $conn->close();
      return $returnData;
    }
    $returnData = array('refreshDue' => true, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', $result['cacheTime']));
    r\table($tableName)->get($key)->replace(array('cacheTime' => time()))->run($conn);
    $conn->close();
    return $returnData;
  }

  public function setCache($tableName, $key, $data) {
    $conn = r\connect('localhost', 28015, 'stats');
    $storageArray = array(
      'key' => $key,
      'cacheTime' => time(),
      'data' => $data
    );
    $recordExists = r\table($tableName)->get($key)->run($conn);
    if($recordExists == null) {
      $result = r\table($tableName)->insert($storageArray)->run($conn);
      $conn->close();
      return true;
    }
    $result = r\table($tableName)->get($key)->replace($storageArray)->run($conn);
    $conn->close();
    return true;
  }

  public function queueTask($jobType, $tableName, $key, $data) {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
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

}
