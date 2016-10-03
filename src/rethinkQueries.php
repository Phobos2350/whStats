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
      $returnData = array('refreshDue' => false, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', $result['cacheTime']));
      return $returnData;
    } else {
      $returnData = array('refreshDue' => true, 'statsArray' => $result['data'], 'lastCached' => date('Y-m-d H:i:s', $result['cacheTime']));
      $update = r\table($tableName)->get($key)->replace(array('cacheTime' => time()))->run($conn);
      return $returnData;
    }
    $conn->close();
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
    $conn->close();
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
      $conn->close();
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
      $conn->close();
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
      $conn->close();
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
      $conn->close();
      return $toEncode;
    }
  }

  public function getMonthKills($limit, $year, $month, $page) {
    $conn = r\connect('localhost', 28015, 'stats');
    $page -= 1;
    if($page < 0) {
      $conn->close();
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
    $conn->close();
    return $toEncode;
  }

  public function getShip($id) {
    $conn = r\connect('localhost', 28015, 'stats');
    $killExists = r\table('shipTypes')->get(intval($id))->run($conn);
    foreach($killExists as $kill) {
      $toEncode[] = $kill;
    }
    $conn->close();
    return $toEncode;
  }

  public function getStats($period, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $key = md5(strtoupper('periodStats_'.$period.'_'.$year.'_'.$month));
    $killExists = r\table('generatedStats')->get($key)->run($conn);
    if($killExists != null) {
      $conn->close();
      return $killExists;
    } else {
      $conn->close();
      return null;
    }
  }

  public function cacheEntityStats($period, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $key = md5(strtoupper('entityStats_'.$period.'_'.$year.'_'.$month));
    $killExists = r\table('generatedEntityStats')->get($key)->run($conn);
    if($killExists != null) {
      $toEncode = $killExists;
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
      $conn->close();
      return $toEncode;
    } else {
      $conn->close();
      return null;
    }
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
    $conn->close();
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
    $conn->close();
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
    $conn->close();
    return $toEncode;
  }
}
