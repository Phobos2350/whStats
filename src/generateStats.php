<?php
require_once 'rethinkQueries.php';
//require_once 'simple_html_dom.php';
date_default_timezone_set('Etc/GMT');

class GenerateStats {

  public function __construct(){
    $this->rethinkQueries = new RethinkQueries();
  }

  public function formatValue($n) {
    if (!is_numeric($n)) {
      return 0;
    }
    return ceil(($n/1000000));
  }

  public function listenForChanges() {
    $conn = r\connect('localhost', 28015, 'stats');
    $feed = r\table('whKills')->changes()->run($conn);
    $lastCheck = strtotime('-5 minutes');
    $lastDayCheck = strtotime('-5 minutes');
    $lastWeekCheck = strtotime('-5 minutes');
    $lastMonthCheck = strtotime('-5 minutes');
    foreach($feed as $change) {
      $killTime = $change['new_val']['killTime'];
      $killTime = $killTime->getTimestamp();
      if($killTime >= strtotime('-1 hour') || (date("i")%5) == 0) {
        if(strtotime("-2 minutes") >= $lastCheck) {
          $killTimeFormatted = date('Y.m.d H:i:s', $killTime);
          printf("[H] Called - ID: {$change['new_val']['killID']} | Time: {$killTimeFormatted}\n");
          $data = array('period' => 'hour', 'year' => 0, 'month' => 0);
          $this->rethinkQueries->queueTask('genStats', '', '', $data);
          $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_all_hour')), array('tz' => 'all', 'period' => 'hour'));
          // if (date("H") >= 0 && date("H") < 8) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_us_hour')), array('tz' => 'us', 'period' => 'hour'));
          // } elseif (date("H") >= 8 && date("H") < 16) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_au_hour')), array('tz' => 'au', 'period' => 'hour'));
          // } elseif (date("H") >= 16 && date("H") < 24) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_eu_hour')), array('tz' => 'eu', 'period' => 'hour'));
          // }
          $lastCheck = strtotime("now");
        }
      }
      if($killTime >= strtotime('-1 day') && $killTime < strtotime('-1 hour') || (date("i")%5) == 0) {
        if(strtotime("-5 minutes") >= $lastDayCheck) {
          $killTimeFormatted = date('Y.m.d H:i:s', $killTime);
          printf("[D] Called - ID: {$change['new_val']['killID']} | Time: {$killTimeFormatted}\n");
          $data = array('period' => 'day', 'year' => 0, 'month' => 0);
          $this->rethinkQueries->queueTask('genStats', '', '', $data);
          $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_all_day')), array('tz' => 'all', 'period' => 'day'));
          // if(intval(date('H', $killTime), 10) >= 0 && intval(date('H', $killTime), 10) < 8) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_us_day')), array('tz' => 'us', 'period' => 'day'));
          // }
          // if(intval(date('H', $killTime), 10) >= 8 && intval(date('H', $killTime), 10) < 16) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_au_day')), array('tz' => 'au', 'period' => 'day'));
          // }
          // if(intval(date('H', $killTime), 10) >= 16 && intval(date('H', $killTime), 10) < 24) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_eu_day')), array('tz' => 'eu', 'period' => 'day'));
          // }
          $lastDayCheck = strtotime("now");
        }
      }
      if($killTime >= strtotime('-1 week') && $killTime < strtotime('-1 day') || (date("i")%15) == 0) {
        if(strtotime("-15 minutes") >= $lastWeekCheck) {
          $killTimeFormatted = date('Y.m.d H:i:s', $killTime);
          printf("[W] Called - ID: {$change['new_val']['killID']} | Time: {$killTimeFormatted}\n");
          $data = array('period' => 'week', 'year' => 0, 'month' => 0);
          $this->rethinkQueries->queueTask('genStats', '', '', $data);
          $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_all_day')), array('tz' => 'all', 'period' => 'week'));
          // if(intval(date('H', $killTime), 10) >= 0 && intval(date('H', $killTime), 10) < 8) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_us_week')), array('tz' => 'us', 'period' => 'week'));
          // }
          // if(intval(date('H', $killTime), 10) >= 8 && intval(date('H', $killTime), 10) < 16) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_au_week')), array('tz' => 'au', 'period' => 'week'));
          // }
          // if(intval(date('H', $killTime), 10) >= 16 && intval(date('H', $killTime), 10) < 24) {
          //   $this->rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', md5(strtoupper('entityStats_eu_week')), array('tz' => 'eu', 'period' => 'week'));
          // }
          $lastWeekCheck = strtotime("now");
        }
      }
      if($killTime >= strtotime('-1 month') && $killTime < strtotime('-1 week') || (date("i")%30) == 0) {
        if(strtotime("-30 minutes") >= $lastMonthCheck) {
          $killTimeFormatted = date('Y.m.d H:i:s', $killTime);
          printf("[M] Called - ID: {$change['new_val']['killID']} | Time: {$killTimeFormatted}\n");
          $month = intval(date('m'), 10);
          $year = intval(date('Y'), 10);
          $data = array('period' => 'month', 'year' => $year, 'month' => $month);
          $this->rethinkQueries->queueTask('genStats', '', '', $data);
          $tz = 'all';
          $key = md5(strtoupper('entityStats_'.$tz.'_'.$year.'_'.$month));
          $data = array('tz' => $tz, 'year' => $year, 'month' => $month);
          $this->rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
          // if(intval(date('H', $killTime), 10) >= 0 && intval(date('H', $killTime), 10) < 8) {
          //   $tz = 'us';
          //   $key = md5(strtoupper('entityStats_'.$tz.'_'.$year.'_'.$month));
          //   $data = array('tz' => $tz, 'year' => $year, 'month' => $month);
          //   $this->rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
          // }
          // if(intval(date('H', $killTime), 10) >= 8 && intval(date('H', $killTime), 10) < 16) {
          //   $tz = 'au';
          //   $key = md5(strtoupper('entityStats_'.$tz.'_'.$year.'_'.$month));
          //   $data = array('tz' => $tz, 'year' => $year, 'month' => $month);
          //   $this->rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
          // }
          // if(intval(date('H', $killTime), 10) >= 16 && intval(date('H', $killTime), 10) < 24) {
          //   $tz = 'eu';
          //   $key = md5(strtoupper('entityStats_'.$tz.'_'.$year.'_'.$month));
          //   $data = array('tz' => $tz, 'year' => $year, 'month' => $month);
          //   $this->rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
          // }
          $lastMonthCheck = strtotime("now");
        }
      }
    }
  }

  public function populateTables() {
    $data = array('period' => 'week', 'year' => 0, 'month' => 0);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2015, 'month' => 12);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 1);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 2);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 3);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 4);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 5);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 6);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 7);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 8);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
    $data = array('period' => 'month', 'year' => 2016, 'month' => 9);
    $this->rethinkQueries->queueTask('genStats', '', '', $data);
  }

  public function genStats($period, $year, $month) {
    $limit = 1500;
    $time = date('Y-m-d H:i');
    $conn = r\connect('localhost', 28015, 'stats');
    $killsArray = array();
    $dummyArray = array(
      'activeSystems' => array(),
      'biggestKill' => array(
        'killID' => null,
        'shipName' => null,
        'shipType' => null,
        'value' => null
      ),
      'biggestSoloKill' => array(
        'killID' => null,
        'shipName' => null,
        'shipType' => null,
        'value' => null
      ),
      'biggestNPCKill' => array(
        'killID' => null,
        'shipName' => null,
        'shipType' => null,
        'value' => null
      ),
      'class' => 0,
      'items' => array(),
      'kills' => array(
        'shipNames' => array(),
        'shipRaces' => array(),
        'shipTechs' => array(),
        'typeIDs' => array(),
        'typeNames' => array()
      ),
      'topSystem' => null,
      'totalISK' => 0,
      'totalKills' => 0
    );
    $statsArray = array();
    for($i=1; $i<10; $i++) {
      $statsArray[$i] = $dummyArray;
      $statsArray[$i]['class'] = $i;
    }
    if($period == "month") {
      if($year == "this" && $month == "this") {
      	$year = intval(date('Y'), 10);
      	$month = intval(date('m'), 10);
      }
      $statsQuery = $this->rethinkQueries->getMonthKills($limit, $year, $month, 1);
    }
    else {
      $statsQuery = $this->rethinkQueries->getPeriodKills($limit, $period, 1);
    }
    for($page = 1; $page <= $statsQuery['numPages']; $page++) {
      if($period == "month") {
        if($year == "this" && $month == "this") {
          $year = intval(date('Y'), 10);
          $month = intval(date('m'), 10);
        }
        $pageQuery = $this->rethinkQueries->getMonthKills($limit, $year, $month, $page);
      }
      else {
        $pageQuery = $this->rethinkQueries->getPeriodKills($limit, $period, $page);
      }
      $killsArray = $pageQuery['kills'];
      $activeSystemsArray = array();

      foreach ($killsArray as $kill) {
        $system = r\table('whSystems')->get($kill['solarSystemID'])->run($conn);
        $time = date("H00", $kill['killTime']->getTimestamp());
        $class = $system['class'];
        // Thera
        if ($class == 30) {
            $class = 7;
        }
        // Shattereds
        if ($class == 31 || $class == 32 || $class == 33 || $class == 34 || $class == 35 || $class == 36) {
            $class = 8;
        }
        // Frig Holes
        if ($class == 41 || $class == 42 || $class == 43) {
            $class = 9;
        }

        if ($class == 0 || $class == null) {
            continue;
        }
        $ship = r\table('shipTypes')->get(intval($kill["victim"]["shipTypeID"], 10))->run($conn);
        $itemsArray = $kill["items"];
        $attackersArray = $kill["attackers"];
        if(!isset($statsArray[$class]['class'])) { $statsArray[$class]['class'] = $class; };
        !isset($statsArray[$class]['totalKills']) ? $statsArray[$class]['totalKills'] = 1 : $statsArray[$class]['totalKills'] += 1;
        if($ship['shipType'] == "Dreadnoughts" || $ship['shipType'] == "Carriers" || $ship['shipType'] == "Force Auxiliary" || $ship['shipType'] == "Capital Industrial Ships") {
          !isset($statsArray[$class]['capKills']) ? $statsArray[$class]['capKills'] = 1 : $statsArray[$class]['capKills'] += 1;
        }
        !isset($statsArray[$class]['totalISK']) ? $statsArray[$class]['totalISK'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10)) : $statsArray[$class]['totalISK'] += $this->formatValue(intval($kill['zkb']['totalValue'], 10));

        if($ship['shipType'] != 'Structure' && $ship['shipType'] != 'Citadel') {
          if(count($kill['attackers']) == 1) {
            if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
              if(!isset($statsArray[$class]['biggestNPCKill'])) {
                $statsArray[$class]['biggestNPCKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                $statsArray[$class]['biggestNPCKill']['killID'] = $kill['killID'];
                $statsArray[$class]['biggestNPCKill']['shipName'] = $ship['shipName'];
                $statsArray[$class]['biggestNPCKill']['shipType'] = $ship['shipType'];
                $statsArray[$class]['biggestNPCKill']['typeID'] = $ship['shipTypeID'];
              } else {
                if($this->formatValue(intval($kill['zkb']['totalValue'], 10)) > $statsArray[$class]['biggestNPCKill']['value']) {
                  $statsArray[$class]['biggestNPCKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                  $statsArray[$class]['biggestNPCKill']['killID'] = $kill['killID'];
                  $statsArray[$class]['biggestNPCKill']['shipName'] = $ship['shipName'];
                  $statsArray[$class]['biggestNPCKill']['shipType'] = $ship['shipType'];
                  $statsArray[$class]['biggestNPCKill']['typeID'] = $ship['shipTypeID'];
                }
              }
            } else {
              if(!isset($statsArray[$class]['biggestSoloKill'])) {
                $statsArray[$class]['biggestSoloKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                $statsArray[$class]['biggestSoloKill']['killID'] = $kill['killID'];
                $statsArray[$class]['biggestSoloKill']['shipName'] = $ship['shipName'];
                $statsArray[$class]['biggestSoloKill']['shipType'] = $ship['shipType'];
                $statsArray[$class]['biggestSoloKill']['typeID'] = $ship['shipTypeID'];
              } else {
                if($this->formatValue(intval($kill['zkb']['totalValue'], 10)) > $statsArray[$class]['biggestSoloKill']['value']) {
                  $statsArray[$class]['biggestSoloKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                  $statsArray[$class]['biggestSoloKill']['killID'] = $kill['killID'];
                  $statsArray[$class]['biggestSoloKill']['shipName'] = $ship['shipName'];
                  $statsArray[$class]['biggestSoloKill']['shipType'] = $ship['shipType'];
                  $statsArray[$class]['biggestSoloKill']['typeID'] = $ship['shipTypeID'];
                }
              }
            }
          } else {
            if($kill['attackers'][0]['factionName'] == "Unknown" || $kill['attackers'][0]['factionName'] == "Drifters" || $kill['attackers'][0]['factionName'] == "Serpentis") {
              foreach($kill['attackers'] as $attacker) {
                if(!$attacker['factionName'] == "Unknown" || !$attacker['factionName'] == "Drifters" || !$attacker['factionName'] == "Serpentis") {
                  $npcOnly = false;
                  break;
                } else {
                  $npcOnly = true;
                }
              }
            } else {
              $npcOnly = false;
            }
            if($npcOnly) {
              if(!isset($statsArray[$class]['biggestNPCKill'])) {
                $statsArray[$class]['biggestNPCKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                $statsArray[$class]['biggestNPCKill']['killID'] = $kill['killID'];
                $statsArray[$class]['biggestNPCKill']['shipName'] = $ship['shipName'];
                $statsArray[$class]['biggestNPCKill']['shipType'] = $ship['shipType'];
                $statsArray[$class]['biggestNPCKill']['typeID'] = $ship['shipTypeID'];
              } else {
                if($this->formatValue(intval($kill['zkb']['totalValue'], 10)) > $statsArray[$class]['biggestNPCKill']['value']) {
                  $statsArray[$class]['biggestNPCKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
                  $statsArray[$class]['biggestNPCKill']['killID'] = $kill['killID'];
                  $statsArray[$class]['biggestNPCKill']['shipName'] = $ship['shipName'];
                  $statsArray[$class]['biggestNPCKill']['shipType'] = $ship['shipType'];
                  $statsArray[$class]['biggestNPCKill']['typeID'] = $ship['shipTypeID'];
                }
              }
            }
          }
          if(!isset($statsArray[$class]['biggestKill'])) {
            $statsArray[$class]['biggestKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
            $statsArray[$class]['biggestKill']['killID'] = $kill['killID'];
            $statsArray[$class]['biggestKill']['shipName'] = $ship['shipName'];
            $statsArray[$class]['biggestKill']['shipType'] = $ship['shipType'];
            $statsArray[$class]['biggestKill']['typeID'] = $ship['shipTypeID'];
          } else {
            if($this->formatValue(intval($kill['zkb']['totalValue'], 10)) > $statsArray[$class]['biggestKill']['value']) {
              $statsArray[$class]['biggestKill']['value'] = $this->formatValue(intval($kill['zkb']['totalValue'], 10));
              $statsArray[$class]['biggestKill']['killID'] = $kill['killID'];
              $statsArray[$class]['biggestKill']['shipName'] = $ship['shipName'];
              $statsArray[$class]['biggestKill']['shipType'] = $ship['shipType'];
              $statsArray[$class]['biggestKill']['typeID'] = $ship['shipTypeID'];
            }
          }
        }

        !isset($statsArray[$class]['kills']['typeIDs'][$ship['shipTypeID']]) ? $statsArray[$class]['kills']['typeIDs'][$ship['shipTypeID']] = 1 : $statsArray[$class]['kills']['typeIDs'][$ship['shipTypeID']] += 1;
        !isset($statsArray[$class]['kills']['typeNames'][$ship['shipType']]) ? $statsArray[$class]['kills']['typeNames'][$ship['shipType']] = 1 : $statsArray[$class]['kills']['typeNames'][$ship['shipType']] += 1;
        !isset($statsArray[$class]['kills']['shipNames'][$ship['shipName']]) ? $statsArray[$class]['kills']['shipNames'][$ship['shipName']] = 1 : $statsArray[$class]['kills']['shipNames'][$ship['shipName']] += 1;
        !isset($statsArray[$class]['kills']['shipRaces'][$ship['shipRace']]) ? $statsArray[$class]['kills']['shipRaces'][$ship['shipRace']] = 1 : $statsArray[$class]['kills']['shipRaces'][$ship['shipRace']] += 1;
        !isset($statsArray[$class]['kills']['shipTechs'][$ship['shipTech']]) ? $statsArray[$class]['kills']['shipTechs'][$ship['shipTech']] = 1 : $statsArray[$class]['kills']['shipTechs'][$ship['shipTech']] += 1;

        !isset($statsArray[$class]['activeSystems'][$system['systemID']]['systemID']) ? $statsArray[$class]['activeSystems'][$system['systemID']]['systemID'] = $system['systemID'] : $statsArray[$class]['activeSystems'][$system['systemID']]['systemID'];
        !isset($statsArray[$class]['activeSystems'][$system['systemID']]['systemName']) ? $statsArray[$class]['activeSystems'][$system['systemID']]['systemName'] = $system['systemName'] : $statsArray[$class]['activeSystems'][$system['systemID']]['systemID'];
        !isset($statsArray[$class]['activeSystems'][$system['systemID']]['totalKills']) ? $statsArray[$class]['activeSystems'][$system['systemID']]['totalKills'] = 1 : $statsArray[$class]['activeSystems'][$system['systemID']]['totalKills'] += 1;

        !isset($statsArray[$class]['period'][$time]) ? $statsArray[$class]['period'][$time] = 1 : $statsArray[$class]['period'][$time] += 1;

        foreach($itemsArray as $item) {
          !isset($statsArray[$class]['items'][$item['typeID']]) ? $statsArray[$class]['items'][$item['typeID']] = ($item["qtyDestroyed"] + $item["qtyDropped"]) : $statsArray[$class]['kills']['typeIDs'][$ship['shipTypeID']] += ($item["qtyDestroyed"] + $item["qtyDropped"]);
        }
      }
      foreach($statsArray as $classStats) {
        $classID = $classStats['class'];
        $topSystemKills = 0;
        $topSystemID = null;
        foreach($classStats['activeSystems'] as $activeSystem) {
          if($activeSystem['totalKills'] > $topSystemKills) {
            $topSystemKills = $activeSystem['totalKills'];
            $topSystemID = $activeSystem['systemID'];
          }
        }
        !isset($statsArray[$classID]['topSystem']) ? $statsArray[$classID]['topSystem'] = $topSystemID : $statsArray[$classID]['topSystem'] = $topSystemID;
      }
    }

    if($period == "month") {
      $key = md5(strtoupper('periodStats_'.$year.'_'.$month));
    }
    else {
      $key = md5(strtoupper('periodStats_'.$period));
    }
    $record = array('key' => $key, 'stats' => $statsArray);
    $documentExists = r\table('generatedStats')->get($key)->run($conn);
    if($documentExists != null) {
      $result = r\table('generatedStats')->get($key)->replace($record)->run($conn);
    } else {
      $result = r\table('generatedStats')->insert($record)->run($conn);
    }
  }
}
