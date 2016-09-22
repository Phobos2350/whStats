<?php
require_once(dirname(__FILE__)."../../vendor/danielmewes/php-rql/rdb/rdb.php");
date_default_timezone_set('Etc/GMT');

foreach ($argv as $i => $arg) {
    if ($arg == "getKills") {
      getKills();
    }
    if ($arg == "getKillsPeriod") {
      getKillsPeriod($argv[1]);
    }
}

function getKills()
{
    $currentTime = 0;
    $fromHour = 0;
    $continue = true;
    $killID = 0;
    $loopCount = 0;
    $url = "http://redisq.zkillboard.com/listen.php";
    // Connect to localhost
    $conn = r\connect('localhost', 28015, 'stats');

    printf("\n\n---------------------------------------------------------------------------------------------------------\n");
    $currentTime = date('Y-m-d H:i:s', time());
    printf("GetKillsRedis Initiated at {$currentTime}\n");

    while ($continue) {
        $currentTime = date('Y-m-d H:i:s', time());
        //printf("Time {$currentTime}\n");
        $json = null;
        $output = null;

        $content = file_get_contents("http://redisq.zkillboard.com/listen.php");
        $json = json_decode($content, true);

        if($json == null) {
          printf("Sleeping...\n");
          sleep(15);
          continue;
        } else {
          $output = $json;
        }

        if ($output["package"] !== null) {
            //printf("Kills found, analysing...\n");
            $package = $output["package"];
            $kill = $package["killmail"];
            $killID = $kill["killID"];
            $killTime = $kill["killTime"];
            isset($kill["solarSystem"]["id"]) ? $systemID = $kill["solarSystem"]["id"] : $systemID = 0;
            isset($kill["solarSystem"]["name"]) ? $systemName = $kill["solarSystem"]["name"] :  $systemName = "";

            if(null !== r\table('whSystems')->get($systemID)->run($conn)) {

              printf("ID: {$killID} | Time: {$killTime} | System: {$systemName}\n");

              $killExists = r\table('whKills')->get($killID)->run($conn);

              if ($killExists !== null) {
                  printf("xxx Seen kill - $killID xxx\n");
                  continue;
              }

              $killTime = DateTime::createFromFormat('Y.m.d H:i:s', $killTime);
              $timeFormat = date_format($killTime, "Y-m-d\TH:i:sP");
              $isoDate = r\ISO8601($timeFormat, ['default_timezone' => 'UTC']);

              $killFormatted = array();
              $killFormatted['killID'] = $killID;
              $killFormatted['solarSystemID'] = $systemID;
              $killFormatted['killTime'] = $isoDate;
              $killFormatted['moonID'] = 0;
              $killFormatted['position'] = $kill['victim']['position'];
              $killFormatted['zkb'] = $package['zkb'];

              $victimFormatted = array();
              isset($kill["victim"]["character"]["id"]) ? $victimID = intval($kill["victim"]["character"]["id"]) : $victimID = 0;
              isset($kill["victim"]["character"]["name"]) ? $victimName = $kill["victim"]["character"]["name"] : $victimName = "";
              isset($kill["victim"]["corporation"]["id"]) ? $victimCorpID = intval($kill["victim"]["corporation"]["id"]) : $victimCorpID = 0;
              isset($kill["victim"]["corporation"]["name"]) ? $victimCorpName = $kill["victim"]["corporation"]["name"] : $victimCorpName = "";
              isset($kill["victim"]["alliance"]["id"]) ? $victimAllianceID = intval($kill["victim"]["alliance"]["id"]) : $victimAllianceID = 0;
              isset($kill["victim"]["alliance"]["name"]) ? $victimAllianceName = $kill["victim"]["alliance"]["name"] : $victimAllianceName = "";
              isset($kill["victim"]["shipType"]["id"]) ? $victimShipType = intval($kill["victim"]["shipType"]["id"]) : $victimShipType = 0;
              isset($kill["victim"]["faction"]["id"]) ? $victimFactionID = intval($kill["victim"]["faction"]["id"]) : $victimFactionID = 0;
              isset($kill["victim"]["faction"]['name']) ? $victimFactionName = $kill["victim"]["faction"]['name'] : $victimFactionName = "";
              isset($kill["victim"]["damageTaken"]) ? $victimDamageTaken = intval($kill["victim"]["damageTaken"]) : $victimDamageTaken = 0;

              $victimFormatted['shipTypeID'] = $victimShipType;
              $victimFormatted['characterID'] = $victimID;
              $victimFormatted['characterName'] = $victimName;
              $victimFormatted['corporationID'] = $victimCorpID;
              $victimFormatted['corporationName'] = $victimCorpName;
              $victimFormatted['allianceID'] = $victimAllianceID;
              $victimFormatted['allianceName'] = $victimAllianceName;
              $victimFormatted['factionID'] = $victimFactionID;
              $victimFormatted['factionName'] = $victimFactionName;
              $victimFormatted['damageTaken'] = $victimDamageTaken;

              $killFormatted['victim'] = $victimFormatted;

              $exists = r\table("characters")->get($victimID)->run($conn);
              if($exists == null) {
                $result = r\table("characters")->insert(array(
                  'allianceID' => $victimAllianceID,
                  'allianceName' => $victimAllianceName,
                  'characterID' => $victimID,
                  'characterName' => $victimName,
                  'corporationID' => $victimCorpID,
                  'corporationName' => $victimCorpName
                ), array('conflict' => 'replace'))->run($conn);
              } else {
                $result = r\table("characters")->get($victimID)->update(array(
                  'allianceID' => $victimAllianceID,
                  'allianceName' => $victimAllianceName,
                  'characterID' => $victimID,
                  'characterName' => $victimName,
                  'corporationID' => $victimCorpID,
                  'corporationName' => $victimCorpName
                ))->run($conn);
              }

              $exists = r\table("entities")->get($victimCorpID)->run($conn);
              if($exists == null) {
                $result = r\table("entities")->insert(array(
                    'allianceID' => $victimAllianceID,
                    'allianceName' => $victimAllianceName,
                    'corporationID' => $victimCorpID,
                    'corporationName' => $victimCorpName
                  ), array('conflict' => 'replace'))->run($conn);
              } else {
                $result = r\table("entities")->get($victimCorpID)->update(array(
                    'allianceID' => $victimAllianceID,
                    'allianceName' => $victimAllianceName,
                    'corporationID' => $victimCorpID,
                    'corporationName' => $victimCorpName
                  ))->run($conn);
              }

              $formattedAttackers = array();
              foreach($kill['attackers'] as $attacker) {
                isset($attacker['character']['id']) ? $attackerID = intval($attacker['character']['id']) : $attackerID = 0;
                isset($attacker['character']['name']) ? $attackerName = $attacker['character']['name'] : $attackerName = "";
                isset($attacker['corporation']['id']) ? $attackerCorpID = intval($attacker['corporation']['id']) : $attackerCorpID = 0 ;
                isset($attacker['corporation']['name']) ? $attackerCorpName = $attacker['corporation']['name'] : $attackerCorpName = "";
                isset($attacker['alliance']['id']) ? $attackerAllianceID = intval($attacker['alliance']['id']) : $attackerAllianceID = 0;
                isset($attacker['alliance']['name']) ? $attackerAllianceName = $attacker['alliance']['name'] : $attackerAllianceName = "";
                isset($attacker['shipType']['id']) ? $attackerShipType = intval($attacker['shipType']['id']) : $attackerShipType = 0;
                isset($attacker['faction']['id']) ? $attackerFactionID = intval($attacker['faction']['id']) : $attackerFactionID = 0;
                isset($attacker['faction']['name']) ? $attackerFactionName = $attacker['faction']['name'] : $attackerFactionName = "";
                isset($attacker['securityStatus']) ? $attackerSecStatus = intval($attacker['securityStatus']) : $attackerSecStatus = 0;
                isset($attacker['damageDone']) ? $attackerDmg = intval($attacker['damageDone']) : $attackerDmg = 0;
                isset($attacker['finalBlow']) ? $attackerFinal = intval($attacker['finalBlow']) : $attackerFinal = false;
                isset($attacker['weaponType']['id']) ? $attackerWep = intval($attacker['weaponType']['id']) : $attackerWep = 0;

                $thisAttacker['characterID'] = $attackerID;
                $thisAttacker['characterName'] = $attackerName;
                $thisAttacker['corporationID'] = $attackerCorpID;
                $thisAttacker['corporationName'] = $attackerCorpName;
                $thisAttacker['allianceID'] = $attackerAllianceID;
                $thisAttacker['allianceName'] = $attackerAllianceName;
                $thisAttacker['factionID'] = $attackerFactionID;
                $thisAttacker['factionName'] = $attackerFactionName;
                $thisAttacker['securityStatus'] = $attackerSecStatus;
                $thisAttacker['damageDone'] = $attackerDmg;
                $thisAttacker['finalBlow'] = $attackerFinal;
                $thisAttacker['weaponTypeID'] = $attackerWep;
                $thisAttacker['shipTypeID'] = $attackerShipType;

                array_push($formattedAttackers, $thisAttacker);

                if($attackerID != 0) {
                  $exists = r\table("characters")->get($attackerID)->run($conn);
                  if($exists == null) {
                    $result = r\table("characters")->insert(array(
                      'allianceID' => $attackerAllianceID,
                      'allianceName' => $attackerAllianceName,
                      'characterID' => $attackerID,
                      'characterName' => $attackerName,
                      'corporationID' => $attackerCorpID,
                      'corporationName' => $attackerCorpName
                    ), array('conflict' => 'replace'))->run($conn);
                  } else {
                    $result = r\table("characters")->get($attackerID)->update(array(
                      'allianceID' => $attackerAllianceID,
                      'allianceName' => $attackerAllianceName,
                      'characterID' => $attackerID,
                      'characterName' => $attackerName,
                      'corporationID' => $attackerCorpID,
                      'corporationName' => $attackerCorpName
                    ))->run($conn);
                  }
                }

                $exists = r\table("entities")->get($attackerCorpID)->run($conn);
                if($exists == null) {
                  $result = r\table("entities")->insert(array(
                    'allianceID' => $attackerAllianceID,
                    'allianceName' => $attackerAllianceName,
                    'corporationID' => $attackerCorpID,
                    'corporationName' => $attackerCorpName
                  ), array('conflict' => 'replace'))->run($conn);
                } else {
                  $result = r\table("entities")->get($attackerCorpID)->update(array(
                    'allianceID' => $attackerAllianceID,
                    'allianceName' => $attackerAllianceName,
                    'corporationID' => $attackerCorpID,
                    'corporationName' => $attackerCorpName
                  ))->run($conn);
                }
              }

              $killFormatted['attackers'] = $formattedAttackers;

              $formattedItems = array();
              foreach($kill['victim']['items'] as $item) {
                isset($item['itemType']['id']) ? $thisItem['typeID'] = intval($item['itemType']['id']) : $thisItem['typeID'] = 0;
                isset($item['flag']) ? $thisItem['flag'] = intval($item['flag']) : $thisItem['flag'] = 0;
                isset($item['quantityDropped']) ? $thisItem['qtyDropped'] = intval($item['quantityDropped']) : $thisItem['qtyDropped'] = 0;
                isset($item['quantityDestroyed']) ? $thisItem['qtyDestroyed'] = intval($item['quantityDestroyed']) : $thisItem['qtyDestroyed'] = 0;
                isset($item['singleton']) ? $thisItem['singleton'] = intval($item['singleton']) : $thisItem['singleton'] = 0;

                array_push($formattedItems, $thisItem);
              }

              $killFormatted['items'] = $formattedItems;

              $result = r\table("whKills")->insert($killFormatted)->run($conn);
              echo "Insert result: {$killID}\n";
            } else {
              //printf("Not a WH...\n");
              continue;
            }
        }
    }
    return 0;
}

function getKillsPeriod($startDate)
{
    $currentTime = 0;
    $fromHour = 0;
    $continue = true;
    $killID = 0;
    $loopCount = 0;
    $url = "https://zkillboard.com/api/history/$startDate";
    $page = 1;
    // Connect to localhost
    $conn = r\connect('localhost', 28015, 'stats');

    printf("\n\n---------------------------------------------------------------------------------------------------------\n");
    $currentTime = date('Y-m-d H:i:s', time());
    printf("getKillsPeriod Initiated at {$currentTime}\n");

    while ($continue) {
        $currentTime = date('Y-m-d H:i:s', time());
        //printf("Time {$currentTime}\n");
        $json = null;
        $output = null;

        $content = file_get_contents($url);
        $json = json_decode($content, true);
        print_r($json);
        exit();

        if($json == null) {
          printf("Sleeping...\n");
          sleep(15);
          continue;
        } else {
          $output = $json;
        }

        if ($output !== null) {
            //printf("Kills found, analysing...\n");
            foreach($output as $kill) {
              print_r($kill);
              exit();
              $killExists = r\table('whKills')->get($killID)->run($conn);

              if ($killExists !== null) {
                  //printf("xxx Seen kill - $killID xxx\n");
                  continue;
              }
              $killID = $kill["killID"];
              $killTime = $kill["killTime"];
              isset($kill["solarSystemID"]) ? $systemID = $kill["solarSystemID"] : $systemID = 0;

              if(null !== r\table('whSystems')->get($systemID)->run($conn)) {

                //printf("ID: {$killID} | Time: {$killTime} | System: {$systemID}\n");
                $killTime = DateTime::createFromFormat('Y-m-d H:i:s', $killTime);
                $timeFormat = date_format($killTime, "Y-m-d\TH:i:sP");
                $isoDate = r\ISO8601($timeFormat, ['default_timezone' => 'UTC']);

                $killFormatted = array();
                $killFormatted['killID'] = $killID;
                $killFormatted['solarSystemID'] = $systemID;
                $killFormatted['killTime'] = $isoDate;
                $killFormatted['moonID'] = 0;
                $killFormatted['position'] = $kill['position'];
                $killFormatted['zkb'] = $kill['zkb'];

                $victimFormatted = array();
                isset($kill["victim"]["characterID"]) ? $victimID = intval($kill["victim"]["characterID"]) : $victimID = 0;
                isset($kill["victim"]["characterName"]) ? $victimName = $kill["victim"]["characterName"]: $victimName = "";
                isset($kill["victim"]["corporationID"]) ? $victimCorpID = intval($kill["victim"]["corporationID"]) : $victimCorpID = 0;
                isset($kill["victim"]["corporationName"]) ? $victimCorpName = $kill["victim"]["corporationName"] : $victimCorpName = "";
                isset($kill["victim"]["allianceID"]) ? $victimAllianceID = intval($kill["victim"]["allianceID"]) : $victimAllianceID = 0;
                isset($kill["victim"]["allianceName"]) ? $victimAllianceName = $kill["victim"]["allianceName"] : $victimAllianceName = "";
                isset($kill["victim"]["shipTypeID"]) ? $victimShipType = intval($kill["victim"]["shipTypeID"]) : $victimShipType = 0;
                isset($kill["victim"]["factionID"]) ? $victimFactionID = intval($kill["victim"]["factionID"]) : $victimFactionID = 0;
                isset($kill["victim"]["factionName"]) ? $victimFactionName = $kill["victim"]["factionName"] : $victimFactionName = "";
                isset($kill["victim"]["damageTaken"]) ? $victimDamageTaken = intval($kill["victim"]["damageTaken"]) : $victimDamageTaken = 0;

                $victimFormatted['shipTypeID'] = $victimShipType;
                $victimFormatted['characterID'] = $victimID;
                $victimFormatted['characterName'] = $victimName;
                $victimFormatted['corporationID'] = $victimCorpID;
                $victimFormatted['corporationName'] = $victimCorpName;
                $victimFormatted['allianceID'] = $victimAllianceID;
                $victimFormatted['allianceName'] = $victimAllianceName;
                $victimFormatted['factionID'] = $victimFactionID;
                $victimFormatted['factionName'] = $victimFactionName;
                $victimFormatted['damageTaken'] = $victimDamageTaken;

                $killFormatted['victim'] = $victimFormatted;

                $exists = r\table("characters")->get($victimID)->run($conn);
                if($exists == null) {
                  $result = r\table("characters")->insert(array(
                    'allianceID' => $victimAllianceID,
                    'allianceName' => $victimAllianceName,
                    'characterID' => $victimID,
                    'characterName' => $victimName,
                    'corporationID' => $victimCorpID,
                    'corporationName' => $victimCorpName
                  ), array('conflict' => 'replace'))->run($conn);
                } else {
                  $result = r\table("characters")->get($victimID)->update(array(
                    'allianceID' => $victimAllianceID,
                    'allianceName' => $victimAllianceName,
                    'characterID' => $victimID,
                    'characterName' => $victimName,
                    'corporationID' => $victimCorpID,
                    'corporationName' => $victimCorpName
                  ))->run($conn);
                }

                $exists = r\table("entities")->get($victimCorpID)->run($conn);
                if($exists == null) {
                  $result = r\table("entities")->insert(array(
                      'allianceID' => $victimAllianceID,
                      'allianceName' => $victimAllianceName,
                      'corporationID' => $victimCorpID,
                      'corporationName' => $victimCorpName
                    ), array('conflict' => 'replace'))->run($conn);
                } else {
                  $result = r\table("entities")->get($victimCorpID)->update(array(
                      'allianceID' => $victimAllianceID,
                      'allianceName' => $victimAllianceName,
                      'corporationID' => $victimCorpID,
                      'corporationName' => $victimCorpName
                    ))->run($conn);
                }

                $formattedAttackers = array();
                foreach($kill['attackers'] as $attacker) {
                  isset($attacker['characterID']) ? $attackerID = intval($attacker['characterID']) : $attackerID = 0;
                  isset($attacker['characterName']) ? $attackerName = $attacker['characterName'] : $attackerName = "";
                  isset($attacker['corporationID']) ? $attackerCorpID = intval($attacker['corporationID']) : $attackerCorpID = 0 ;
                  isset($attacker['corporationName']) ? $attackerCorpName = $attacker['corporationName'] : $attackerCorpName = "";
                  isset($attacker['allianceID']) ? $attackerAllianceID = intval($attacker['allianceID']) : $attackerAllianceID = 0;
                  isset($attacker['allianceName']) ? $attackerAllianceName = $attacker['allianceName'] : $attackerAllianceName = "";
                  isset($attacker['shipTypeID']) ? $attackerShipType = intval($attacker['shipTypeID']) : $attackerShipType = 0;
                  isset($attacker['factionID']) ? $attackerFactionID = intval($attacker['factionID']) : $attackerFactionID = 0;
                  isset($attacker['factionName']) ? $attackerFactionName = $attacker['factionName'] : $attackerFactionName = "";
                  isset($attacker['securityStatus']) ? $attackerSecStatus = intval($attacker['securityStatus']) : $attackerSecStatus = 0;
                  isset($attacker['damageDone']) ? $attackerDmg = intval($attacker['damageDone']) : $attackerDmg = 0;
                  isset($attacker['finalBlow']) ? $attackerFinal = intval($attacker['finalBlow']) : $attackerFinal = false;
                  isset($attacker['weaponTypeID']) ? $attackerWep = intval($attacker['weaponTypeID']) : $attackerWep = 0;

                  $thisAttacker['characterID'] = $attackerID;
                  $thisAttacker['characterName'] = $attackerName;
                  $thisAttacker['corporationID'] = $attackerCorpID;
                  $thisAttacker['corporationName'] = $attackerCorpName;
                  $thisAttacker['allianceID'] = $attackerAllianceID;
                  $thisAttacker['allianceName'] = $attackerAllianceName;
                  $thisAttacker['factionID'] = $attackerFactionID;
                  $thisAttacker['factionName'] = $attackerFactionName;
                  $thisAttacker['securityStatus'] = $attackerSecStatus;
                  $thisAttacker['damageDone'] = $attackerDmg;
                  $thisAttacker['finalBlow'] = $attackerFinal;
                  $thisAttacker['weaponTypeID'] = $attackerWep;
                  $thisAttacker['shipTypeID'] = $attackerShipType;

                  array_push($formattedAttackers, $thisAttacker);

                  if($attackerID != 0) {
                    $exists = r\table("characters")->get($attackerID)->run($conn);
                    if($exists == null) {
                      $result = r\table("characters")->insert(array(
                        'allianceID' => $attackerAllianceID,
                        'allianceName' => $attackerAllianceName,
                        'characterID' => $attackerID,
                        'characterName' => $attackerName,
                        'corporationID' => $attackerCorpID,
                        'corporationName' => $attackerCorpName
                      ), array('conflict' => 'replace'))->run($conn);
                    } else {
                      $result = r\table("characters")->get($attackerID)->update(array(
                        'allianceID' => $attackerAllianceID,
                        'allianceName' => $attackerAllianceName,
                        'characterID' => $attackerID,
                        'characterName' => $attackerName,
                        'corporationID' => $attackerCorpID,
                        'corporationName' => $attackerCorpName
                      ))->run($conn);
                    }
                  }

                  $exists = r\table("entities")->get($attackerCorpID)->run($conn);
                  if($exists == null) {
                    $result = r\table("entities")->insert(array(
                      'allianceID' => $attackerAllianceID,
                      'allianceName' => $attackerAllianceName,
                      'corporationID' => $attackerCorpID,
                      'corporationName' => $attackerCorpName
                    ), array('conflict' => 'replace'))->run($conn);
                  } else {
                    $result = r\table("entities")->get($attackerCorpID)->update(array(
                      'allianceID' => $attackerAllianceID,
                      'allianceName' => $attackerAllianceName,
                      'corporationID' => $attackerCorpID,
                      'corporationName' => $attackerCorpName
                    ))->run($conn);
                  }
                }

                $killFormatted['attackers'] = $formattedAttackers;

                $formattedItems = array();
                foreach($kill['items'] as $item) {
                  isset($item['typeID']) ? $thisItem['typeID'] = intval($item['typeID']) : $thisItem['typeID'] = 0;
                  isset($item['flag']) ? $thisItem['flag'] = intval($item['flag']) : $thisItem['flag'] = 0;
                  isset($item['qtyDropped']) ? $thisItem['qtyDropped'] = intval($item['qtyDropped']) : $thisItem['qtyDropped'] = 0;
                  isset($item['qtyDestroyed']) ? $thisItem['qtyDestroyed'] = intval($item['qtyDestroyed']) : $thisItem['qtyDestroyed'] = 0;
                  isset($item['singleton']) ? $thisItem['singleton'] = intval($item['singleton']) : $thisItem['singleton'] = 0;

                  array_push($formattedItems, $thisItem);
                }

                $killFormatted['items'] = $formattedItems;

                $result = r\table("whKills")->insert($killFormatted)->run($conn);
                echo "Insert result: {$killID}\n";
              } else {
                //printf("Not a WH...\n");
                continue;
              }
          }
        }
    }
    return 0;
}
