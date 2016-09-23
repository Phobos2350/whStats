<?php
require_once(dirname(__FILE__).'../../vendor/autoload.php');
require_once(dirname(__FILE__)."../../vendor/danielmewes/php-rql/rdb/rdb.php");
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
ini_set('memory_limit', '1024M');
date_default_timezone_set('Etc/GMT');

class RethinkQueries {

  public function getCache($tableName, $key, $expiry) {
    $conn = r\connect('localhost', 28015, 'stats');
    $result = r\table($tableName)->get($key)->run($conn);
    if($result == null) {
      return array('refreshDue' => true, 'statsArray' => null, 'lastCached' => null);
    }
    if($result['cacheTime'] > strtotime('-'.$expiry.' minutes')) {
      return array('refreshDue' => false, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', $result['cacheTime']));
    } else {
      $update = r\table($tableName)->get($key)->replace(array('cacheTime' => time()))->run($conn);
      return array('refreshDue' => true, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', time()));
    }
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
    } else {
      $result = r\table($tableName)->get($key)->replace($storageArray)->run($conn);
    }
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

  public function getPeriodKills($limit, $period, $page) {
    $conn = r\connect('localhost', 28015, 'stats');
    $page -= 1;
    if($page < 0) {
      return "Please Enter a Valid Page Value of 1 or Greater";
    }
    if ($period == "hour") {
      $killExists = r\table('whKills')
      ->between(
        r\now()->sub(3600),
        r\now(),
        array('index' => 'killTime')
      )
      ->skip($limit * $page)
      ->limit($limit)
      ->run($conn);
      $count = r\table('whKills')
      ->between(
        r\now()->sub(3600),
        r\now(),
        array('index' => 'killTime')
      )
      ->count()
      ->run($conn);
      $toEncode['totalKills'] = $count;
      $count > $limit ? $toEncode['numPages'] = ceil($count / $limit) : $toEncode['numPages'] = 1;
      foreach($killExists as $kill) {
        $toEncode['kills'][] = $kill;
      }
      return $toEncode;
    }
    if ($period == "day") {
      $killExists = r\table('whKills')
      ->between(
        r\now()->sub(86400),
        r\now(),
        array('index' => 'killTime')
      )
      ->skip($limit * $page)
      ->limit($limit)
      ->run($conn);
      $count = r\table('whKills')
      ->between(
        r\now()->sub(86400),
        r\now(),
        array('index' => 'killTime')
      )
      ->count()
      ->run($conn);
      $toEncode['totalKills'] = $count;
      $count > $limit ? $toEncode['numPages'] = ceil($count / $limit) : $toEncode['numPages'] = 1;
      foreach($killExists as $kill) {
        $toEncode['kills'][] = $kill;
      }
      return $toEncode;
    }
    if ($period == "week") {
      $killExists = r\table('whKills')
      ->between(
        r\now()->sub(604800),
        r\now(),
        array('index' => 'killTime')
      )
      ->skip($limit * $page)
      ->limit($limit)
      ->run($conn);
      $count = r\table('whKills')
      ->between(
        r\now()->sub(604800),
        r\now(),
        array('index' => 'killTime')
      )
      ->count()
      ->run($conn);
      $toEncode['totalKills'] = $count;
      $count > $limit ? $toEncode['numPages'] = ceil($count / $limit) : $toEncode['numPages'] = 1;
      foreach($killExists as $kill) {
        $toEncode['kills'][] = $kill;
      }
      return $toEncode;
    }
  }

  public function getMonthKills($limit, $year, $month, $page) {
    $conn = r\connect('localhost', 28015, 'stats');
    $page -= 1;
    if($page < 0) {
      return "Please Enter a Valid Page of 1 or higher";
    }
    $endDay = 31;
    if($month === 4 || $month === 6 || $month === 9 || $month === 11) {
      $endDay = 30;
    }
    if($month == 2) {
      if(date('L', strtotime("{$year}-01-01")) === 1) {
        $endDay = 29;
      } else {
        $endDay = 28;
      }
    }
    $killExists = r\table('whKills')
    ->between(
      r\time($year, $month, 1, 0, 0, 0, 'Z'),
      r\time($year, $month, $endDay, 23, 59, 59, 'Z'),
      array('index' => 'killTime')
    )
    ->skip($limit * $page)
    ->limit($limit)
    ->run($conn);
    $count = r\table('whKills')
    ->between(
      r\time($year, $month, 1, 0, 0, 0, 'Z'),
      r\time($year, $month, $endDay, 23, 59, 59, 'Z'),
      array('index' => 'killTime')
    )
    ->count()
    ->run($conn);
    $toEncode['totalKills'] = $count;
    $count > $limit ? $toEncode['numPages'] = ceil($count / $limit) : $toEncode['numPages'] = 1;
    foreach($killExists as $kill) {
      $toEncode['kills'][] = $kill;
    }
    return $toEncode;
  }

  public function getShip($id) {
    $conn = r\connect('localhost', 28015, 'stats');
    $killExists = r\table('shipTypes')->get(intval($id))->run($conn);
    foreach($killExists as $kill) {
      $toEncode[] = $kill;
    }
    return $toEncode;
  }

  public function getStatsPeriod($period) {
    $conn = r\connect('localhost', 28015, 'stats');
    $key = md5(strtoupper('periodStats_'.$period));
    $killExists = r\table('generatedStats')->get($key)->run($conn);
    if($killExists != null) {
      foreach($killExists['stats'] as $kill) {
        $kill['activeSystems'] = null;
        $kill['items'] = null;
        $toEncode['stats'][] = $kill;
      }
      return $toEncode;
    } else {
      return null;
    }
  }

  public function getStatsMonth($year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $key = md5(strtoupper('periodStats_'.$year.'_'.$month));
    $killExists = r\table('generatedStats')->get($key)->run($conn);
    if($killExists != null) {
      foreach($killExists['stats'] as $kill) {
        $kill['activeSystems'] = null;
        $kill['items'] = null;
        $toEncode['stats'][] = $kill;
      }
      return $toEncode;
    } else {
      return null;
    }
  }

  public function getEntityStatsPeriod($tz, $period) {
    $conn = r\connect('localhost', 28015, 'stats');
    $subValue = 0;
    $toEncode['timezone'] = "UNKNOWN";
    $continue = true;
    $page = 0;
    $limit = 10000;
    $combinedResults = array();

    if(strtoupper($tz) == "US") {
      $toEncode['timezone'] = "US";
      $fromHour = 0;
      $toHour = 8;
    }
    if(strtoupper($tz) == "AU") {
      $toEncode['timezone'] = "AU";
      $fromHour = 8;
      $toHour = 16;
    }
    if(strtoupper($tz) == "EU") {
      $toEncode['timezone'] = "EU";
      $fromHour = 16;
      $toHour = 24;
    }
    if(strtoupper($tz) == "ALL") {
      $toEncode['timezone'] = "ALL";
      $fromHour = 0;
      $toHour = 24;
    }

    if($period == 'hour') {
      $subValue = 3600;
    }
    if($period == 'day') {
      $subValue = 86400;
    }
    if($period == 'week') {
      $subValue = 604800;
    }

    while($continue) {
      $blockResults = r\table('whKills')
      ->between(
        r\now()->sub($subValue),
        r\now(),
        array('index' => 'killTime')
      )
      // ->filter(function($aKill) use(&$fromHour, &$toHour){
      //   return $aKill('killTime')->hours()->ge($fromHour)
      //   ->rAnd(
      //     $aKill('killTime')->hours()->lt($toHour)
      //   );
      // })
      ->skip($page * $limit)
      ->limit($limit)
      ->concatMap(function($aKill) {
        return $aKill('attackers')
          ->map(function($each) {
            return r\branch($each('allianceID')->eq(0),
                    $each->merge(array('entityID' => $each('corporationID'), 'isAlliance' => false)),
                    $each->merge(array('entityID' => $each('allianceID'), 'isAlliance' => true)));
          })
          ->map(function($attacker) use(&$aKill) {
            return array(
              'entityID' => $attacker('entityID'),
              'corporationID' => $attacker('corporationID'),
              'corporationName' => $attacker('corporationName'),
              'allianceID' => $attacker('allianceID'),
              'allianceName' => $attacker('allianceName'),
              'killID' => $aKill('killID'),
              'shipTypeID' => $attacker('shipTypeID'),
              'weaponTypeID' => $attacker('weaponTypeID'),
              'systemID' => $aKill('solarSystemID'),
              'value' => $aKill('zkb')('totalValue'),
              'killTime' => $aKill('killTime')
            );
          });
        })
      ->group('entityID')
      ->ungroup()
      ->merge(function($row) {
        return array(
          'entityID' => $row('group'),
          'totalKills' => $row('reduction')('killID')->distinct()->count(),
          'totalISK' => $row('reduction')('value')->distinct()->sum(),
        );
      })
      ->map(function($each) {
        return array(
          'entityID' => $each('entityID'),
          'totalISK' => $each('totalISK'),
          'totalKills' => $each('totalKills'),
          'killsArray' => $each('reduction')
        );
      })
      ->orderBy(r\desc('totalISK'))
      ->run($conn);

      $combinedResults = array_merge_recursive($combinedResults, $blockResults);
      if(count($blockResults) >= 1) {
        $page++;
      } else {
        $continue = false;
      }
    }

    usort($combinedResults, function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });

    $combinedResults = array_slice($combinedResults, 0, 1000, true);

    $toEncode['stats']['ALL'] = array();
    $toEncode['stats']['US'] = array();
    $toEncode['stats']['AU'] = array();
    $toEncode['stats']['EU'] = array();

    foreach($combinedResults as $entity) {
      $entityID = $entity['entityID'];
      if($entity['killsArray'][0]['allianceID'] == $entityID) {
        $entity['isAlliance'] = true;
        $entity['entityName'] = $entity['killsArray'][0]['allianceName'];
      } else {
        $entity['isAlliance'] = false;
        $entity['entityName'] = $entity['killsArray'][0]['corporationName'];
      }
      if($entityID == 0) {
        $entity['entityName'] = 'NPC';
      }
      // $entity['c1Kills'] = 0; $entity['c2Kills'] = 0; $entity['c3Kills'] = 0; $entity['c4Kills'] = 0; $entity['c5Kills'] = 0;
      // $entity['c6Kills'] = 0; $entity['c7Kills'] = 0; $entity['c8Kills'] = 0; $entity['c9Kills'] = 0;
      // $entity['totalKills'] = 0; $entity['totalISK'] = 0;

      if(!isset($toEncode['stats']['ALL'][$entityID])) {
        $toEncode['stats']['ALL'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['ALL'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['ALL'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['ALL'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['ALL'][$entityID]['totalISK'] = 0;
        $toEncode['stats']['ALL'][$entityID]['shipsUsed'] = array();
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['ALL'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['US'][$entityID])) {
        $toEncode['stats']['US'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['US'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['US'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['US'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['US'][$entityID]['totalISK'] = 0;
        $toEncode['stats']['US'][$entityID]['shipsUsed'] = array();
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['US'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['AU'][$entityID])) {
        $toEncode['stats']['AU'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['AU'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['AU'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['AU'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['AU'][$entityID]['totalISK'] = 0;
        $toEncode['stats']['AU'][$entityID]['shipsUsed'] = array();
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['AU'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['EU'][$entityID])) {
        $toEncode['stats']['EU'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['EU'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['EU'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['EU'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['EU'][$entityID]['totalISK'] = 0;
        $toEncode['stats']['EU'][$entityID]['shipsUsed'] = array();
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['EU'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }

      $killsSeen = array();

      foreach($entity['killsArray'] as $kill) {
        $killTime = $kill['killTime']->getTimestamp();
        $killTimeHour = date('H', $killTime);
        $killTimezone = null;
        if($killTimeHour >= 0 && $killTimeHour < 8) {
          $killTimezone = 'US';
        } elseif($killTimeHour >= 8 && $killTimeHour < 16) {
          $killTimezone = 'AU';
        } elseif($killTimeHour >= 16 && $killTimeHour < 24) {
          $killTimezone = 'EU';
        }
        $shipTypeID = $kill['shipTypeID'];
        if($shipTypeID != 0) {
          $shipType = $systemClass = r\table('shipTypes')->get($shipTypeID)->run($conn);
          $shipClass = $shipType['shipType'];
          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;

          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;

          // !isset($shipsUsed[$shipClass]) ? $shipsUsed[$shipClass]['totalUses'] = 1 : $shipsUsed[$shipClass]['totalUses'] += 1;
          // !isset($shipsUsed[$shipClass][$shipTypeID]) ? $shipsUsed[$shipClass][$shipTypeID] = $shipType : null;
          // !isset($shipsUsed[$shipClass][$shipTypeID]['numUses']) ? $shipsUsed[$shipClass][$shipTypeID]['numUses'] = 1 : $shipsUsed[$shipClass][$shipTypeID]['numUses'] += 1;
        }
        if(!in_array($kill['killID'], $killsSeen)) {
          $systemID = $kill['systemID'];
          $systemData = r\table('whSystems')->get($systemID)->run($conn);
          $systemClass = $systemData['class'];
          // Thera
          if ($systemClass == 30) {
              $systemClass = 7;
          }
          // Shattereds
          if ($systemClass == 31 || $systemClass == 32 || $systemClass == 33 || $systemClass == 34 || $systemClass == 35 || $systemClass == 36) {
              $systemClass = 8;
          }
          // Frig Holes
          if ($systemClass == 41 || $systemClass == 42 || $systemClass == 43) {
              $systemClass = 9;
          }

          if ($systemClass == 0 || $systemClass == null) {
              continue;
          }
          $toEncode['stats']['ALL'][$entityID]['c'.$systemClass.'Kills'] += 1;
          $toEncode['stats'][$killTimezone][$entityID]['c'.$systemClass.'Kills'] += 1;
          $toEncode['stats']['ALL'][$entityID]['totalKills'] += 1;
          $toEncode['stats'][$killTimezone][$entityID]['totalKills'] += 1;
          $toEncode['stats']['ALL'][$entityID]['totalISK'] += $kill['value'];
          $toEncode['stats'][$killTimezone][$entityID]['totalISK'] += $kill['value'];

          // $entity['c'.$systemClass.'Kills'] += 1;
          // $entity['totalKills'] += 1;
          // $entity['totalISK'] += $kill['value'];
          array_push($killsSeen, $kill['killID']);
        }
      }
      // $entity['shipsUsed'] = $shipsUsed;
      // if(!isset($toEncode['stats'][$entityID])) {
      //   $entity['killsArray'] = null;
      //   $toEncode['stats'][$entityID] = $entity;
      // }
    }

    usort($toEncode['stats']['ALL'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['US'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['AU'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['EU'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    $toEncode['stats']['ALL'] = array_slice($toEncode['stats']['ALL'], 0, 100, true);
    $toEncode['stats']['US'] = array_slice($toEncode['stats']['US'], 0, 100, true);
    $toEncode['stats']['AU'] = array_slice($toEncode['stats']['AU'], 0, 100, true);
    $toEncode['stats']['EU'] = array_slice($toEncode['stats']['EU'], 0, 100, true);
    return $toEncode;
  }

  public function getEntityStatsMonth($tz, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $toEncode['timezone'] = "UNKNOWN";
    $continue = true;
    $page = 0;
    $limit = 5000;
    $combinedResults = array();

    if(strtoupper($tz) == "US") {
      $toEncode['timezone'] = "US";
      $fromHour = 0;
      $toHour = 8;
    }
    if(strtoupper($tz) == "AU") {
      $toEncode['timezone'] = "AU";
      $fromHour = 8;
      $toHour = 16;
    }
    if(strtoupper($tz) == "EU") {
      $toEncode['timezone'] = "EU";
      $fromHour = 16;
      $toHour = 24;
    }
    if(strtoupper($tz) == "ALL") {
      $toEncode['timezone'] = "ALL";
      $fromHour = 0;
      $toHour = 24;
    }

    $endDay = 31;
    if($month === 4 || $month === 6 || $month === 9 || $month === 11) {
      $endDay = 30;
    }
    if($month == 2) {
      if(date('L', strtotime("{$year}-01-01")) === 1) {
        $endDay = 29;
      } else {
        $endDay = 28;
      }
    }
    while($continue) {
      $blockResults = r\table('whKills')
      ->between(
        r\time($year, $month, 1, 0, 0, 0, 'Z'),
        r\time($year, $month, $endDay, 23, 59, 59, 'Z'),
        array('index' => 'killTime')
      )
      // ->filter(function($aKill) use(&$fromHour, &$toHour){
      //   return $aKill('killTime')->hours()->ge($fromHour)
      //   ->rAnd(
      //     $aKill('killTime')->hours()->lt($toHour)
      //   );
      // })
      ->skip($page * $limit)
      ->limit($limit)
      ->concatMap(function($aKill) {
        return $aKill('attackers')
          ->map(function($each) {
            return r\branch($each('allianceID')->eq(0),
                    $each->merge(array('entityID' => $each('corporationID'), 'isAlliance' => false)),
                    $each->merge(array('entityID' => $each('allianceID'), 'isAlliance' => true)));
          })
          ->map(function($attacker) use(&$aKill) {
            return array(
              'entityID' => $attacker('entityID'),
              'corporationID' => $attacker('corporationID'),
              'corporationName' => $attacker('corporationName'),
              'allianceID' => $attacker('allianceID'),
              'allianceName' => $attacker('allianceName'),
              'killID' => $aKill('killID'),
              'killTime' => $aKill('killTime'),
              'shipTypeID' => $attacker('shipTypeID'),
              'weaponTypeID' => $attacker('weaponTypeID'),
              'systemID' => $aKill('solarSystemID'),
              'value' => $aKill('zkb')('totalValue')
            );
          });
        })
      ->group('entityID')
      ->ungroup()
      ->merge(function($row) {
        return array(
          'entityID' => $row('group'),
          'totalKills' => $row('reduction')('killID')->distinct()->count(),
          'totalISK' => $row('reduction')('value')->distinct()->sum(),
        );
      })
      ->map(function($each) {
        return array(
          'entityID' => $each('entityID'),
          'totalISK' => $each('totalISK'),
          'totalKills' => $each('totalKills'),
          'killsArray' => $each('reduction')
        );
      })
      ->run($conn);

      $combinedResults = array_merge_recursive($combinedResults, $blockResults);
      if(count($blockResults) >= 1) {
        $page++;
      } else {
        $continue = false;
      }
    }

    usort($combinedResults, function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });

    $combinedResults = array_slice($combinedResults, 0, 1000, true);

    $toEncode['stats']['ALL'] = array();
    $toEncode['stats']['US'] = array();
    $toEncode['stats']['AU'] = array();
    $toEncode['stats']['EU'] = array();

    foreach($combinedResults as $entity) {
      $entityID = $entity['entityID'];
      if($entity['killsArray'][0]['allianceID'] == $entityID) {
        $entity['isAlliance'] = true;
        $entity['entityName'] = $entity['killsArray'][0]['allianceName'];
      } else {
        $entity['isAlliance'] = false;
        $entity['entityName'] = $entity['killsArray'][0]['corporationName'];
      }
      if($entityID == 0) {
        $entity['entityName'] = 'NPC';
      }

      if(!isset($toEncode['stats']['ALL'][$entityID])) {
        $toEncode['stats']['ALL'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['ALL'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['ALL'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['ALL'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['ALL'][$entityID]['totalISK'] = 0;
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['ALL'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['US'][$entityID])) {
        $toEncode['stats']['US'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['US'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['US'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['US'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['US'][$entityID]['totalISK'] = 0;
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['US'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['AU'][$entityID])) {
        $toEncode['stats']['AU'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['AU'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['AU'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['AU'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['AU'][$entityID]['totalISK'] = 0;
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['AU'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }
      if(!isset($toEncode['stats']['EU'][$entityID])) {
        $toEncode['stats']['EU'][$entityID]['isAlliance'] = $entity['isAlliance'];
        $toEncode['stats']['EU'][$entityID]['entityID'] = $entity['entityID'];
        $toEncode['stats']['EU'][$entityID]['entityName'] = $entity['entityName'];
        $toEncode['stats']['EU'][$entityID]['totalKills'] = 0;
        $toEncode['stats']['EU'][$entityID]['totalISK'] = 0;
        for($i = 1; $i < 10; $i++) {
          $toEncode['stats']['EU'][$entityID]['c'.$i.'Kills'] = 0;
        }
      }

      $killsSeen = array();

      foreach($entity['killsArray'] as $kill) {
        $killTime = $kill['killTime']->getTimestamp();
        $killTimeHour = date('H', $killTime);
        $killTimezone = null;
        if($killTimeHour >= 0 && $killTimeHour < 8) {
          $killTimezone = 'US';
        } elseif($killTimeHour >= 8 && $killTimeHour < 16) {
          $killTimezone = 'AU';
        } elseif($killTimeHour >= 16 && $killTimeHour < 24) {
          $killTimezone = 'EU';
        }
        $shipTypeID = $kill['shipTypeID'];
        if($shipTypeID != 0) {
          $shipType = r\table('shipTypes')->get($shipTypeID)->run($conn);
          $shipClass = $shipType['shipType'];

          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode['stats']['ALL'][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;

          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode['stats'][$killTimezone][$entityID]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;

          // !isset($shipsUsed[$shipClass]) ? $shipsUsed[$shipClass]['totalUses'] = 1 : $shipsUsed[$shipClass]['totalUses'] += 1;
          // !isset($shipsUsed[$shipClass][$shipTypeID]) ? $shipsUsed[$shipClass][$shipTypeID] = $shipType : null;
          // !isset($shipsUsed[$shipClass][$shipTypeID]['numUses']) ? $shipsUsed[$shipClass][$shipTypeID]['numUses'] = 1 : $shipsUsed[$shipClass][$shipTypeID]['numUses'] += 1;
        }
        if(!in_array($kill['killID'], $killsSeen)) {
          $systemID = $kill['systemID'];
          $systemData = r\table('whSystems')->get($systemID)->run($conn);
          $systemClass = $systemData['class'];
          // Thera
          if ($systemClass == 30) {
              $systemClass = 7;
          }
          // Shattereds
          if ($systemClass == 31 || $systemClass == 32 || $systemClass == 33 || $systemClass == 34 || $systemClass == 35 || $systemClass == 36) {
              $systemClass = 8;
          }
          // Frig Holes
          if ($systemClass == 41 || $systemClass == 42 || $systemClass == 43) {
              $systemClass = 9;
          }

          if ($systemClass == 0 || $systemClass == null) {
              continue;
          }

          $toEncode['stats']['ALL'][$entityID]['c'.$systemClass.'Kills'] += 1;
          $toEncode['stats'][$killTimezone][$entityID]['c'.$systemClass.'Kills'] += 1;
          $toEncode['stats']['ALL'][$entityID]['totalKills'] += 1;
          $toEncode['stats'][$killTimezone][$entityID]['totalKills'] += 1;
          $toEncode['stats']['ALL'][$entityID]['totalISK'] += $kill['value'];
          $toEncode['stats'][$killTimezone][$entityID]['totalISK'] += $kill['value'];

          //$entity['c'.$systemClass.'Kills'] += 1;
          //$entity['totalKills'] += 1;
          //$entity['totalISK'] += $kill['value'];
          array_push($killsSeen, $kill['killID']);
        }
      }
      // $entity['shipsUsed'] = $shipsUsed;
      // if(!isset($toEncode['stats'][$entityID])) {
      //   $entity['killsArray'] = null;
      //   $toEncode['stats'][$entityID] = $entity;
      // }
    }

    usort($toEncode['stats']['ALL'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['US'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['AU'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    usort($toEncode['stats']['EU'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    $toEncode['stats']['ALL'] = array_slice($toEncode['stats']['ALL'], 0, 100, true);
    $toEncode['stats']['US'] = array_slice($toEncode['stats']['US'], 0, 100, true);
    $toEncode['stats']['AU'] = array_slice($toEncode['stats']['AU'], 0, 100, true);
    $toEncode['stats']['EU'] = array_slice($toEncode['stats']['EU'], 0, 100, true);
    return $toEncode;
  }

  public function getEntityStatsMonthByID($id, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $combinedResults = array();
    $toEncode = array();
    $type = r\table('entities')->get($id)->run($conn);
    if($type == null) {
      $queryType = array('index' => 'attacker_allianceID');
      $entityType = 'allianceID';
      $entityName = r\table('entities')->getAll($id, array('index' => 'allianceID'))->limit(1)->pluck('allianceName')->run($conn);
      $entityName = $entityName->toArray();
      $toEncode['entityName'] = $entityName[0]['allianceName'];
      $toEncode['entityType'] = 'alliance';
    } else {
      $queryType = array('index' => 'attacker_corporationID');
      $entityType = 'corporationID';
      $toEncode['entityName'] = $type['corporationName'];
      $toEncode['entityType'] = 'corporation';
    }

    $endDay = 31;
    if($month === 4 || $month === 6 || $month === 9 || $month === 11) {
      $endDay = 30;
    }
    if($month == 2) {
      if(date('L', strtotime("{$year}-01-01")) === 1) {
        $endDay = 29;
      } else {
        $endDay = 28;
      }
    }
    $combinedResults = r\table('whKills')->getAll($id, $queryType)
    ->filter(function($aKill) use(&$year, &$month){
      return $aKill('killTime')->year()->eq($year)
      ->rAnd(
        $aKill('killTime')->month()->eq($month)
      );
    })
    ->concatMap(function($aKill) use(&$id, &$entityType){
      return $aKill('attackers')
        ->filter(function($thisAttacker) use(&$id, &$entityType) {
          return $thisAttacker($entityType)->eq($id);
        })
        ->map(function($attacker) use(&$aKill) {
          return array(
            'characterID' => $attacker('characterID'),
            'killID' => $aKill('killID'),
            'killTime' => $aKill('killTime'),
            'shipTypeID' => $attacker('shipTypeID'),
            'systemID' => $aKill('solarSystemID'),
            'value' => $aKill('zkb')('totalValue')
          );
        });
      })
    ->group('killID')
    ->ungroup()
    ->merge(function($row) {
      return array(
        'killID' => $row('group'),
        'killTime' => $row('reduction')('killTime')->max(),
        'systemID' => $row('reduction')('systemID')->max(),
        'value' => $row('reduction')('value')->max(),
        'uniquePilots' => $row('reduction')('characterID')->distinct()->count()
      );
    })
    ->map(function($each) {
      return array(
        'killID' => $each('killID'),
        'killTime' => $each('killTime'),
        'systemID' => $each('systemID'),
        'value' => $each('value'),
        'uniquePilots' => $each('uniquePilots'),
        'killsArray' => $each('reduction')
      );
    })
    ->run($conn);

    for($i = 1; $i < 10; $i++) {
      $toEncode['ALL']['c'.$i.'Kills'] = 0;
      $toEncode['US']['c'.$i.'Kills'] = 0;
      $toEncode['AU']['c'.$i.'Kills'] = 0;
      $toEncode['EU']['c'.$i.'Kills'] = 0;
      $toEncode['ALL']['totalKills'] = 0;
      $toEncode['US']['totalKills'] = 0;
      $toEncode['AU']['totalKills'] = 0;
      $toEncode['EU']['totalKills'] = 0;
      $toEncode['ALL']['totalISK'] = 0;
      $toEncode['US']['totalISK'] = 0;
      $toEncode['AU']['totalISK'] = 0;
      $toEncode['EU']['totalISK'] = 0;
      $toEncode['ALL']['shipsUsed'] = array();
      $toEncode['US']['shipsUsed'] = array();
      $toEncode['AU']['shipsUsed'] = array();
      $toEncode['EU']['shipsUsed'] = array();
      $toEncode['ALL']['totalPilotsOnKills'] = 0;
      $toEncode['US']['totalPilotsOnKills'] = 0;
      $toEncode['AU']['totalPilotsOnKills'] = 0;
      $toEncode['EU']['totalPilotsOnKills'] = 0;
    }

    foreach($combinedResults as $kill) {
      $killTime = $kill['killTime']->getTimestamp();
      $killTimeHour = date('H', $killTime);
      $killTimezone = null;
      if($killTimeHour >= 0 && $killTimeHour < 8) {
        $killTimezone = 'US';
      } elseif($killTimeHour >= 8 && $killTimeHour < 16) {
        $killTimezone = 'AU';
      } elseif($killTimeHour >= 16 && $killTimeHour < 24) {
        $killTimezone = 'EU';
      }

      foreach($kill['killsArray'] as $thisKill) {
        $shipTypeID = $thisKill['shipTypeID'];
        if($shipTypeID != 0) {
          $shipType = r\table('shipTypes')->get($shipTypeID)->run($conn);
          $shipClass = $shipType['shipType'];
          !isset($toEncode['ALL']['shipsUsed'][$shipClass]) ? $toEncode['ALL']['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode['ALL']['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode[$killTimezone]['shipsUsed'][$shipClass]) ? $toEncode[$killTimezone]['shipsUsed'][$shipClass]['totalUses'] = 1 : $toEncode[$killTimezone]['shipsUsed'][$shipClass]['totalUses'] += 1;
          !isset($toEncode['ALL']['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode['ALL']['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode[$killTimezone]['shipsUsed'][$shipClass][$shipTypeID]) ? $toEncode[$killTimezone]['shipsUsed'][$shipClass][$shipTypeID] = $shipType : null;
          !isset($toEncode['ALL']['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode['ALL']['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode['ALL']['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;
          !isset($toEncode[$killTimezone]['shipsUsed'][$shipClass][$shipTypeID]['numUses']) ? $toEncode[$killTimezone]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] = 1 : $toEncode[$killTimezone]['shipsUsed'][$shipClass][$shipTypeID]['numUses'] += 1;
        }
      }
      $systemID = $kill['systemID'];
      $systemData = r\table('whSystems')->get($systemID)->run($conn);
      $systemClass = $systemData['class'];
      // Thera
      if ($systemClass == 30) {
          $systemClass = 7;
      }
      // Shattereds
      if ($systemClass == 31 || $systemClass == 32 || $systemClass == 33 || $systemClass == 34 || $systemClass == 35 || $systemClass == 36) {
          $systemClass = 8;
      }
      // Frig Holes
      if ($systemClass == 41 || $systemClass == 42 || $systemClass == 43) {
          $systemClass = 9;
      }

      if ($systemClass == 0 || $systemClass == null) {
          continue;
      }
      $toEncode['ALL']['c'.$systemClass.'Kills'] += 1;
      $toEncode[$killTimezone]['c'.$systemClass.'Kills'] += 1;
      $toEncode['ALL']['totalKills'] += 1;
      $toEncode[$killTimezone]['totalKills'] += 1;
      $toEncode['ALL']['totalISK'] += $kill['value'];
      $toEncode[$killTimezone]['totalISK'] += $kill['value'];
      $toEncode['ALL']['totalPilotsOnKills'] += $kill['uniquePilots'];
      $toEncode[$killTimezone]['totalPilotsOnKills'] += $kill['uniquePilots'];
    }
    return $toEncode;
  }

  public function getEntityTopKillers($year, $month, $entityID) {
    $conn = r\connect('localhost', 28015, 'stats');
    intval($entityID, 10) === 99005198 ? $takeshis = true : $takeshis = false; // If Takeshi's
    $killExists = r\table('whKills')->getAll($entityID, array('index'=>'attacker_allianceID'))
                  ->filter(function($aKill) use(&$year, &$month){
                    return $aKill('killTime')->year()->eq($year)
                    ->rAnd(
                      $aKill('killTime')->month()->eq($month)
                    );
                  })
                  ->concatMap(function($aKill) {
                    return $aKill('attackers');
                  })
                  ->filter(array('allianceID' => $entityID))
                  ->group('characterID', array('multi'=>true))
                  ->distinct()
                  ->count()
                  ->ungroup()
                  ->eqJoin('group', r\table('characters'), array('index' => 'characterID'))
                  ->zip()
                  ->orderBy(r\desc('reduction'))
                  ->run($conn);

    if($killExists == null) {
      $killExists = r\table('whKills')->getAll($entityID, array('index'=>'attacker_corporationID'))
                    ->filter(function($aKill) use(&$year, &$month){
                      return $aKill('killTime')->year()->eq($year)
                      ->rAnd(
                        $aKill('killTime')->month()->eq($month)
                      );
                    })
                    ->concatMap(function($aKill) {
                      return $aKill('attackers');
                    })
                    ->filter(array('corporationID' => $entityID))
                    ->group('characterID', array('multi'=>true))
                    ->distinct()
                    ->count()
                    ->ungroup()
                    ->eqJoin('group', r\table('characters'), array('index' => 'characterID'))
                    ->zip()
                    ->orderBy(r\desc('reduction'))
                    ->run($conn);
    }

    if($takeshis) {
      $getAltsRaw = file_get_contents("http://eve-vippy.com/api/users/alts?apikey=aGVsbG9teW5hbWVpc2ZsaWdodHk=");
      $getAltsJSON = json_decode($getAltsRaw, true);

      $returnArray = array();
      foreach ($getAltsJSON as $main) {
        $mainID = $main['maincharacter'];
        $charData = r\table('characters')->get(intval($mainID))->run($conn);
        if($charData['allianceID'] == $entityID) {

          $returnArray[$mainID] = array(
            'allianceID' => $charData['allianceID'],
            'allianceName' => $charData['allianceName'],
            'characterID' => $charData['characterID'],
            'characterName' => $charData['characterName'],
            'corporationID' => $charData['corporationID'],
            'corporationName' => $charData['corporationName'],
            'reduction' => 0
          );
        }
      }
    }

    foreach($killExists as $kill) {
      if($takeshis) {
        foreach($getAltsJSON as $character) {
          $mainCharID = $character['maincharacter'];
          foreach($character['characters'] as $alt) {
            if($alt['id'] == $kill['characterID']) {

              $flownShips = r\table('whKills')->getAll(intval($alt['id']), array('index'=>'attacker_characterID'))
                            ->filter(function($aKill) use(&$year, &$month){
                              return $aKill('killTime')->year()->eq($year)
                              ->rAnd(
                                $aKill('killTime')->month()->eq($month)
                              );
                            })
                            ->concatMap(function($aKill) {
                              return $aKill('attackers');
                            })
                            ->filter(array('characterID' => intval($alt['id'])))
                            ->eqJoin('shipTypeID', r\table('shipTypes'), array('index' => 'shipTypeID'))
                            ->zip()
                            ->group('shipTypeID')
                            ->count()
                            ->ungroup()
                            ->eqJoin('group', r\table('shipTypes'), array('index' => 'shipTypeID'))
                            ->zip()
                            ->orderBy(r\desc('reduction'))
                            ->run($conn);
              foreach($flownShips as $ship) {
                $shipName = $ship['shipTypeID'];
                $shipType = $ship['shipType'];
                if(!array_key_exists($shipName, $returnArray[$mainCharID])){
                  $returnArray[$mainCharID]['shipsFlown'][$shipName] = $ship['reduction'];
                  $returnArray[$mainCharID]['shipTypes'][$shipType] = $ship['reduction'];
                }
                else {
                  $returnArray[$mainCharID]['shipsFlown'][$shipName] += $ship['reduction'];
                  $returnArray[$mainCharID]['shipTypes'][$shipType] += $ship['reduction'];
                }
              }
              $returnArray[$mainCharID]['reduction'] += $kill['reduction'];
            }
          }
        }
      } else {
        $returnArray[$kill['characterID']] = array(
          'allianceID' => $kill['allianceID'],
          'allianceName' => $kill['allianceName'],
          'characterID' => $kill['characterID'],
          'characterName' => $kill['characterName'],
          'corporationID' => $kill['corporationID'],
          'corporationName' => $kill['corporationName'],
          'reduction' => $kill['reduction']
        );
        $flownShips = r\table('whKills')->getAll(intval($kill['characterID']), array('index'=>'attacker_characterID'))
                      ->filter(function($aKill) use(&$year, &$month){
                        return $aKill('killTime')->year()->eq($year)
                        ->rAnd(
                          $aKill('killTime')->month()->eq($month)
                        );
                      })
                      ->concatMap(function($aKill) {
                        return $aKill('attackers');
                      })
                      ->filter(array('characterID' => intval($kill['characterID'])))
                      ->eqJoin('shipTypeID', r\table('shipTypes'), array('index' => 'shipTypeID'))
                      ->zip()
                      ->group('shipTypeID')
                      ->count()
                      ->ungroup()
                      ->eqJoin('group', r\table('shipTypes'), array('index' => 'shipTypeID'))
                      ->zip()
                      ->orderBy(r\desc('reduction'))
                      ->run($conn);
        foreach($flownShips as $ship) {
          $shipName = $ship['shipTypeID'];
          $shipType = $ship['shipType'];
          if(!array_key_exists($shipName, $returnArray[$kill['characterID']])){
            $returnArray[$kill['characterID']]['shipsFlown'][$shipName] = $ship['reduction'];
            $returnArray[$kill['characterID']]['shipTypes'][$shipType] = $ship['reduction'];
          }
          else {
            $returnArray[$kill['characterID']]['shipsFlown'][$shipName] += $ship['reduction'];
            $returnArray[$kill['characterID']]['shipTypes'][$shipType] += $ship['reduction'];

          }
        }
        //$returnArray[] = $kill;
      }
    }
    usort($returnArray, function ($item1, $item2) {
      return $item2['reduction'] <=> $item1['reduction'];
    });
    return array_slice($returnArray, 0, 100, true);
  }

  public function getCharShipUsage($year, $month, $charID) {
    $conn = r\connect('localhost', 28015, 'stats');
    $killExists = r\table('whKills')->getAll($charID, array('index'=>'attacker_characterID'))
                  ->filter(function($aKill) use(&$year, &$month){
                    return $aKill('killTime')->year()->eq($year)
                    ->rAnd(
                      $aKill('killTime')->month()->eq($month)
                    );
                  })
                  ->concatMap(function($aKill) {
                    return $aKill('attackers');
                  })
                  ->filter(array('characterID' => $charID))
                  ->eqJoin('shipTypeID', r\table('shipTypes'), array('index' => 'shipTypeID'))
                  ->zip()
                  ->group('shipTypeID')
                  ->count()
                  ->ungroup()
                  ->eqJoin('group', r\table('shipTypes'), array('index' => 'shipTypeID'))
                  ->zip()
                  ->orderBy(r\desc('reduction'))
                  ->run($conn);

    foreach($killExists as $kill) {
      $toEncode[] = $kill;
    }
    return $toEncode;
  }
}
