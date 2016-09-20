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
    if($result === null) {
      return null;
    }
    if($result['cacheTime'] > strtotime('-'.$expiry.' minutes')) {
      return array('refreshDue' => false, 'statsArray' => $result['data']);
    } else {
      $update = r\table($tableName)->get($key)->update(array('cacheTime' => time()))->run($conn);
      return array('refreshDue' => true, 'statsArray' => $result['data']);
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
      ->filter(function($aKill) use(&$fromHour, &$toHour){
        return $aKill('killTime')->hours()->ge($fromHour)
        ->rAnd(
          $aKill('killTime')->hours()->lt($toHour)
        );
      })
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
      $entity['c1Kills'] = 0; $entity['c2Kills'] = 0; $entity['c3Kills'] = 0; $entity['c4Kills'] = 0; $entity['c5Kills'] = 0;
      $entity['c6Kills'] = 0; $entity['c7Kills'] = 0; $entity['c8Kills'] = 0; $entity['c9Kills'] = 0;
      $entity['totalKills'] = 0; $entity['totalISK'] = 0;

      $killsSeen = array();
      $shipsUsed = array();

      foreach($entity['killsArray'] as $kill) {
        $shipTypeID = $kill['shipTypeID'];
        if($shipTypeID != 0) {
          $shipType = $systemClass = r\table('shipTypes')->get($shipTypeID)->run($conn);
          $shipClass = $shipType['shipType'];
          !isset($shipsUsed[$shipClass]) ? $shipsUsed[$shipClass]['totalUses'] = 1 : $shipsUsed[$shipClass]['totalUses'] += 1;
          !isset($shipsUsed[$shipClass][$shipTypeID]) ? $shipsUsed[$shipClass][$shipTypeID] = $shipType : null;
          !isset($shipsUsed[$shipClass][$shipTypeID]['numUses']) ? $shipsUsed[$shipClass][$shipTypeID]['numUses'] = 1 : $shipsUsed[$shipClass][$shipTypeID]['numUses'] += 1;
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
          $entity['c'.$systemClass.'Kills'] += 1;
          $entity['totalKills'] += 1;
          $entity['totalISK'] += $kill['value'];
          array_push($killsSeen, $kill['killID']);
        }
      }
      $entity['shipsUsed'] = $shipsUsed;
      if(!isset($toEncode['stats'][$entityID])) {
        $entity['killsArray'] = null;
        $toEncode['stats'][$entityID] = $entity;
      }
    }

    usort($toEncode['stats'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    $toEncode['stats'] = array_slice($toEncode['stats'], 0, 100, true);
    return $toEncode;
  }

  public function getEntityStatsMonth($tz, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
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
      ->filter(function($aKill) use(&$fromHour, &$toHour){
        return $aKill('killTime')->hours()->ge($fromHour)
        ->rAnd(
          $aKill('killTime')->hours()->lt($toHour)
        );
      })
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
      ->orderBy(r\desc('totalISK'))
      ->run($conn);

      $combinedResults = array_merge_recursive($combinedResults, $blockResults);
      if(count($blockResults) >= 1) {
        $page++;
      } else {
        $continue = false;
      }
    }

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
      $entity['c1Kills'] = 0; $entity['c2Kills'] = 0; $entity['c3Kills'] = 0; $entity['c4Kills'] = 0; $entity['c5Kills'] = 0;
      $entity['c6Kills'] = 0; $entity['c7Kills'] = 0; $entity['c8Kills'] = 0; $entity['c9Kills'] = 0;
      $entity['totalKills'] = 0; $entity['totalISK'] = 0;

      $killsSeen = array();
      $shipsUsed = array();

      foreach($entity['killsArray'] as $kill) {
        $shipTypeID = $kill['shipTypeID'];
        if($shipTypeID != 0) {
          $shipType = $systemClass = r\table('shipTypes')->get($shipTypeID)->run($conn);
          $shipClass = $shipType['shipType'];
          !isset($shipsUsed[$shipClass]) ? $shipsUsed[$shipClass]['totalUses'] = 1 : $shipsUsed[$shipClass]['totalUses'] += 1;
          !isset($shipsUsed[$shipClass][$shipTypeID]) ? $shipsUsed[$shipClass][$shipTypeID] = $shipType : null;
          !isset($shipsUsed[$shipClass][$shipTypeID]['numUses']) ? $shipsUsed[$shipClass][$shipTypeID]['numUses'] = 1 : $shipsUsed[$shipClass][$shipTypeID]['numUses'] += 1;
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
          $entity['c'.$systemClass.'Kills'] += 1;
          $entity['totalKills'] += 1;
          $entity['totalISK'] += $kill['value'];
          array_push($killsSeen, $kill['killID']);
        }
      }
      $entity['shipsUsed'] = $shipsUsed;
      if(!isset($toEncode['stats'][$entityID])) {
        $entity['killsArray'] = null;
        $toEncode['stats'][$entityID] = $entity;
      }
    }

    usort($toEncode['stats'], function($a, $b) {
      return $b['totalISK'] <=> $a['totalISK'];
    });
    $toEncode['stats'] = array_slice($toEncode['stats'], 0, 200, true);
    return $toEncode;
  }

  public function getEntityTopKillers($year, $month, $entityID) {
    $conn = r\connect('localhost', 28015, 'stats');
    $entityID === 99005198 ? $takeshis = true : $takeshis = false; // If Takeshi's
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
    return array_slice($toEncode, 0, 100, true);
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
