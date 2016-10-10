<?php
require_once(dirname(__FILE__).'../../vendor/autoload.php');
require_once(dirname(__FILE__)."../../vendor/danielmewes/php-rql/rdb/rdb.php");
ini_set('memory_limit', '1024M');
date_default_timezone_set('Etc/GMT');

class RethinkQueries {

  public function __construct(){
    $this->subValues = array(
      "hour" => 3600,
      "day" => 86400,
      "week" => 604800
    );
    $this->classArray = array(
      0 => 0,
      30 => 7,
      31 => 8,
      32 => 8,
      33 => 8,
      34 => 8,
      35 => 8,
      36 => 8,
      41 => 9,
      42 => 9,
      43 => 9
    );
  }

  public function getClass($class) {
    if($class > 0 && $class < 7) {
      return $class;
    }
    return $this->classArray[$class];
  }

  public function getKills($limit, $period, $year, $month, $page) {
    $conn = r\connect('localhost', 28015, 'stats');
    $subVal = 0;
    $page -= 1;
    if($page < 0) {
      $conn->close();
      return "Please Enter a Valid Page Value of 1 or Greater";
    }
    if ($period != "month") {
      $subVal = $this->subValues[strval($period)];
      $killExists = r\table('whKills')
      ->between(
        r\now()->sub($subVal),
        r\now(),
        array('index' => 'killTime')
      )
      ->skip($limit * $page)
      ->limit($limit)
      ->run($conn);
      $count = r\table('whKills')
      ->between(
        r\now()->sub($subVal),
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
    $endDay = intval(date("t", mktime(0,0,0,$month,1,$year)), 10);
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

  public function getShip($shipID) {
    $conn = r\connect('localhost', 28015, 'stats');
    $killExists = r\table('shipTypes')->get(intval($shipID))->run($conn);
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
    }
    $conn->close();
    return null;
  }

  public function getEntityStats($period, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $key = md5(strtoupper('entityStats_'.$period.'_'.$year.'_'.$month));
    $killExists = r\table('generatedEntityStats')->get($key)->run($conn);
    if($killExists != null) {
      $toEncode = $killExists;
      if(isset($toEncode['stats']['ALL'])) {
        usort($toEncode['stats']['ALL'], function($left, $right) {
          return $right['totalISK'] <=> $left['totalISK'];
        });
        $toEncode['stats']['ALL'] = array_slice($toEncode['stats']['ALL'], 0, 100, true);
      }
      if(isset($toEncode['stats']['US'])) {
        usort($toEncode['stats']['US'], function($left, $right) {
          return $right['totalISK'] <=> $left['totalISK'];
        });
        $toEncode['stats']['US'] = array_slice($toEncode['stats']['US'], 0, 100, true);
      }
      if(isset($toEncode['stats']['AU'])) {
        usort($toEncode['stats']['AU'], function($left, $right) {
          return $right['totalISK'] <=> $left['totalISK'];
        });
        $toEncode['stats']['AU'] = array_slice($toEncode['stats']['AU'], 0, 100, true);
      }
      if(isset($toEncode['stats']['EU'])) {
        usort($toEncode['stats']['EU'], function($left, $right) {
          return $right['totalISK'] <=> $left['totalISK'];
        });
        $toEncode['stats']['EU'] = array_slice($toEncode['stats']['EU'], 0, 100, true);
      }
      $conn->close();
      return $toEncode;
    }
    $conn->close();
    return null;
  }

  public function getEntityStatsMonthByID($entityID, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $combinedResults = array();
    $toEncode = array();
    $type = r\table('entities')->get($entityID)->run($conn);
    $queryType = array('index' => 'attacker_corporationID');
    $entityType = 'corporationID';
    $toEncode['entityName'] = $type['corporationName'];
    $toEncode['entityType'] = 'corporation';
    if($type == null) {
      $queryType = array('index' => 'attacker_allianceID');
      $entityType = 'allianceID';
      $entityName = r\table('entities')->getAll($entityID, array('index' => 'allianceID'))->limit(1)->pluck('allianceName')->run($conn);
      $entityName = $entityName->toArray();
      $toEncode['entityName'] = $entityName[0]['allianceName'];
      $toEncode['entityType'] = 'alliance';
    }

    $combinedResults = r\table('whKills')->getAll($entityID, $queryType)
    ->filter(function($aKill) use(&$year, &$month){
      return $aKill('killTime')->year()->eq($year)
      ->rAnd(
        $aKill('killTime')->month()->eq($month)
      );
    })
    ->concatMap(function($aKill) use(&$entityID, &$entityType){
      return $aKill('attackers')
        ->filter(function($thisAttacker) use(&$entityID, &$entityType) {
          return $thisAttacker($entityType)->eq($entityID);
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
    }
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
      $systemClass = $this->getClass($systemData['class']);
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
                $shipName = strval($ship['shipTypeID']);
                $shipType = $ship['shipType'];
                !array_key_exists($shipName, $returnArray[$mainCharID]) ? $returnArray[$mainCharID]['shipsFlown'][$shipName] = $ship['reduction'] : $returnArray[$mainCharID]['shipsFlown'][$shipName] += $ship['reduction'];
                !array_key_exists($shipName, $returnArray[$mainCharID]) ? $returnArray[$mainCharID]['shipTypes'][$shipType] = $ship['reduction'] : $returnArray[$mainCharID]['shipTypes'][$shipType] += $ship['reduction'];
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
          !array_key_exists($shipName, $returnArray[$kill['characterID']]) ? $returnArray[$kill['characterID']]['shipsFlown'][$shipName] = $ship['reduction'] : $returnArray[$kill['characterID']]['shipsFlown'][$shipName] += $ship['reduction'];
          !array_key_exists($shipName, $returnArray[$kill['characterID']]) ? $returnArray[$kill['characterID']]['shipTypes'][$shipType] = $ship['reduction'] : $returnArray[$kill['characterID']]['shipTypes'][$shipType] += $ship['reduction'];
        }
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

  public function getPilotStatsMonthByID($pilotID, $year, $month) {
    $conn = r\connect('localhost', 28015, 'stats');
    $combinedResults = array();
    $toEncode = array();

    $combinedResults = r\table('whKills')->getAll($pilotID, array('index' => 'attacker_characterID'))
    ->filter(function($aKill) use(&$year, &$month){
      return $aKill('killTime')->year()->eq($year)
      ->rAnd(
        $aKill('killTime')->month()->eq($month)
      );
    })
    ->concatMap(function($aKill) use(&$pilotID){
      return $aKill('attackers')
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
      );
    })
    ->map(function($each) {
      return array(
        'killID' => $each('killID'),
        'killTime' => $each('killTime'),
        'systemID' => $each('systemID'),
        'value' => $each('value'),
        'killsArray' => $each('reduction')
      );
    })
    ->run($conn);

    for($i = 1; $i < 10; $i++) {
      $toEncode['ALL']['c'.$i.'Kills'] = 0;
      $toEncode['US']['c'.$i.'Kills'] = 0;
      $toEncode['AU']['c'.$i.'Kills'] = 0;
      $toEncode['EU']['c'.$i.'Kills'] = 0;
    }
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
      $systemClass = $this->getClass($systemData['class']);
      if ($systemClass == 0 || $systemClass == null) {
          continue;
      }
      $toEncode['ALL']['c'.$systemClass.'Kills'] += 1;
      $toEncode[$killTimezone]['c'.$systemClass.'Kills'] += 1;
      $toEncode['ALL']['totalKills'] += 1;
      $toEncode[$killTimezone]['totalKills'] += 1;
      $toEncode['ALL']['totalISK'] += $kill['value'];
      $toEncode[$killTimezone]['totalISK'] += $kill['value'];
    }
    $conn->close();
    return $toEncode;
  }

  public function getSystemStatsID($systemID) {
    $conn = r\connect('localhost', 28015, 'stats');
    $killIDArray = array();
    $killTimesArray = array();
    $toEncode = array(
      'iskHour' => 0,
      'isk3Hours' => 0,
      'iskDay' => 0,
      'iskMonth' => 0,
      'pvpKillsHour' => 0,
      'pvpKills3Hour' => 0,
      'pvpKillsDay' => 0,
      'pvpKillsMonth' => 0,
      'npcKillsHour' => 0,
      'npcKills3Hour' => 0,
      'npcKillsDay' => 0,
      'npcKillsMonth' => 0,
      'killIDs' => array(),
      'killTimes' => array()
    );

    $systemDetails = r\table('whSystems')->getAll($systemID, array('index' => 'systemName'))->run($conn);
    $systemDetails = $systemDetails->toArray();
    if($systemDetails !== null) {
      $systemID = intval($systemDetails[0]['systemID'], 10);
      $year = intval(date("Y"),10);
      $month = intval(date("m"),10);
      $monthResults = r\table('whKills')->getAll($systemID, array('index' => 'solarSystemID'))
      ->filter(function($aKill) use(&$year, &$month){
        return $aKill('killTime')->year()->eq($year)
        ->rAnd(
          $aKill('killTime')->month()->eq($month)
        );
      })->run($conn);
      foreach($monthResults as $kill) {
        $killTime = $kill['killTime']->getTimestamp();
        array_push($killIDArray, $kill['killID']);
        array_push($killTimesArray, $kill['killTime']);
        if($killTime > strtotime('-1 hour')) {
          $toEncode['iskHour'] += $kill['zkb']['totalValue'];
          if(count($kill['attackers']) == 1) {
            if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
              $toEncode['npcKillsHour'] += 1;
            } else {
              $toEncode['pvpKillsHour'] += 1;
            }
          } else {
            $npcSeen = false;
            foreach($kill['attackers'] as $attacker) {
              if($attacker['factionName'] == "Unknown" || $attacker['factionName'] == "Drifters" || $attacker['factionName'] == "Serpentis") {
                $npcSeen = true;
              }
            }
            $npcSeen ? $toEncode['npcKillsHour'] += 1 : $toEncode['pvpKillsHour'] += 1;
          }
        }
        if($killTime > strtotime('-3 hours')) {
          $toEncode['isk3Hours'] += $kill['zkb']['totalValue'];
          if(count($kill['attackers']) == 1) {
            if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
              $toEncode['npcKills3Hour'] += 1;
            } else {
              $toEncode['pvpKills3Hour'] += 1;
            }
          } else {
            $npcSeen = false;
            foreach($kill['attackers'] as $attacker) {
              if($attacker['factionName'] == "Unknown" || $attacker['factionName'] == "Drifters" || $attacker['factionName'] == "Serpentis") {
                $npcSeen = true;
              }
            }
            $npcSeen ? $toEncode['npcKills3Hour'] += 1 : $toEncode['pvpKills3Hour'] += 1;
          }
        }
        if($killTime > strtotime('-1 day')) {
          $toEncode['iskDay'] += $kill['zkb']['totalValue'];
          if(count($kill['attackers']) == 1) {
            if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
              $toEncode['npcKillsDay'] += 1;
            } else {
              $toEncode['pvpKillsDay'] += 1;
            }
          } else {
            $npcSeen = false;
            foreach($kill['attackers'] as $attacker) {
              if($attacker['factionName'] == "Unknown" || $attacker['factionName'] == "Drifters" || $attacker['factionName'] == "Serpentis") {
                $npcSeen = true;
              }
            }
            $npcSeen ? $toEncode['npcKillsDay'] += 1 : $toEncode['pvpKillsDay'] += 1;
          }
        }
        $toEncode['iskMonth'] += $kill['zkb']['totalValue'];
        if(count($kill['attackers']) == 1) {
          if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
            $toEncode['npcKillsMonth'] += 1;
          } else {
            $toEncode['pvpKillsMonth'] += 1;
          }
        } else {
          $npcSeen = false;
          foreach($kill['attackers'] as $attacker) {
            if($attacker['factionName'] == "Unknown" || $attacker['factionName'] == "Drifters" || $attacker['factionName'] == "Serpentis") {
              $npcSeen = true;
            }
          }
          $npcSeen ? $toEncode['npcKillsMonth'] += 1 : $toEncode['pvpKillsMonth'] += 1;
        }
      }
      $conn->close();
      $toEncode['killIDs'] = $killIDArray;
      $toEncode['killTimes'] = $killTimesArray;
      return $toEncode;
    }
    return null;
  }

  public function getSystemByName($name) {
    $conn = r\connect('localhost', 28015, 'stats');
    $query = "(?i)^{$name}";
    $result = r\table('whSystems')->filter(function($system) use (&$query) {
      return $system('systemName')->match($query);
    })->run($conn);
    $systemArray = array();
    foreach($result as $system) {
      array_push($systemArray, $system);
    }
    $conn->close();
    return $systemArray;
  }

  public function getPilotByName($name) {
    $conn = r\connect('localhost', 28015, 'stats');
    $query = "(?i)^{$name}";
    $result = r\table('characters')->filter(function($char) use (&$query) {
      return $char('characterName')->match($query);
    })->run($conn);
    $pilotArray = array();
    foreach($result as $pilot) {
      array_push($pilotArray, $pilot);
    }
    $conn->close();
    return $pilotArray;
  }

  public function getAllianceByName($name) {
    $conn = r\connect('localhost', 28015, 'stats');
    $query = "(?i)^{$name}";
    $result = r\table('entities')->filter(function($entity) use (&$query) {
      return $entity('allianceName')->match($query);
    })->run($conn);
    $allyArray = array();
    foreach($result as $entity) {
      array_push($allyArray, $entity);
    }
    $conn->close();
    return $allyArray;
  }

  public function getCorpByName($name) {
    $conn = r\connect('localhost', 28015, 'stats');
    $query = "(?i)^{$name}";
    $result = r\table('entities')->filter(function($entity) use (&$query) {
      return $entity('corporationName')->match($query);
    })->run($conn);
    $corpArray = array();
    foreach($result as $entity) {
      array_push($corpArray, $entity);
    }
    $conn->close();
    return $corpArray;
  }
}
