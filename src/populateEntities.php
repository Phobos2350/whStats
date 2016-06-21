<?php
    include 'simple_html_dom.php';
    date_default_timezone_set('Etc/GMT');

    foreach ($argv as $i=>$arg )
    {
        if ( $arg == "getKills" )
        {
            getKills($argv[$i+1], $argv[$i+2]);
        }
    }

    function file_get_contents_retry($url, $attemptsRemaining=3) {
        printf("Attempting to retrieve {$url}, {$attemptsRemaining} attempts remaining.\n");
        $content = file_get_contents($url);
        $attemptsRemaining--;

        if( empty($content) && $attemptsRemaining > 0 ) {
            return file_get_contents_retry($url, $attemptsRemaining);
        }

        return $content;
    }

    function formatValue($n)
    {
       if ( !is_numeric($n) )
           return 0;
       return round(($n/1000000),1);
    }

    function getKills($updateType, $fromDate) {
      printf("\n\n---------------------------------------------------------------------------------------------------------\n");
      printf("Called Function getKills with parameter {$updateType}\n");
      $servername = "localhost";
      $username = "whdata";
      $password = "whdata";
      $db = "killstats";
      $currentTime = 0;
      $fromHour = 0;
      $toHour = 0;
      $continue = TRUE;
      $lastKill = 0;
      $loopCount = 0;
      $killSeen = array();
      $newKills = array();
      $updateKills = array();
      $killTimeFormatted = 0;

      $currentTime = date('Y-m-d H:i:s', time());
      $fromHour = date('YmdH00', strtotime($fromDate));
      $toHour = date_format(date_create($currentTime), 'YmdH00');
      printf("WHStats Check at {$currentTime}\n");
      printf("FromHour: {$fromHour} - ToHour: {$toHour}\n");
      $conn = new mysqli($servername, $username, $password, $db);

      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      $lastKillQuery = "SELECT killID FROM lastKill";
      $lastKill = $conn->query($lastKillQuery)->fetch_assoc();
      $lastKill = $lastKill["killID"];
      printf("Last Kill - ".$lastKill."\n");

      $fullRefreshQuery = "SELECT fromDate FROM lastKill";
      $fullRefresh = $conn->query($fullRefreshQuery)->fetch_assoc();
      $fullRefresh = $fullRefresh["fromDate"];

      if($updateType == "purgeEntityData") {
        printf("{$updateType}, purging entitystats table...\n");
        $entityPurgeQuery = "TRUNCATE TABLE entitystatsru";
        $entityPurgeResult = $conn->query($entityPurgeQuery);
        $entityPurgeQuery = "TRUNCATE TABLE entitystatsau";
        $entityPurgeResult = $conn->query($entityPurgeQuery);
        $entityPurgeQuery = "TRUNCATE TABLE entitystatseu";
        $entityPurgeResult = $conn->query($entityPurgeQuery);
        $entityPurgeQuery = "TRUNCATE TABLE entitystatsus";
        $entityPurgeResult = $conn->query($entityPurgeQuery);
      }
      if($updateType == "newWeek") {

      }
      if($updateType == "newDay") {

      }

      if($updateType == "newDay" || $updateType == "newWeek" || $updateType == "newMonth"|| $updateType == "purgeEntityData") {
        // Let's clear the previous hour's seen kills
        printf("{$updateType}, purging killseen table...\n");
        $killSeen = array();
        $killSeenQuery = "TRUNCATE TABLE entitykillseen";
        $killSeenResult = $conn->query($killSeenQuery);
      }

      while($continue) {
        printf("Loop {$loopCount}\n");
        $json = NULL;
        $kills = NULL;
        if($loopCount >= 75) {
          break;
        }
        if($loopCount == 0) {
          $fromHour = date('YmdHi', strtotime($fromHour));
        } else {
          $fromHour = date('YmdHi', strtotime($killTimeFormatted));
        }
        $url = "https://zkillboard.com/api/kills/w-space/imit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
        //$url = "https://zkillboard.com/api/kills/no-items/no-attackers/w-space/limit/200/orderDirection/asc/afterKillID/".$lastKill."/";
        if (!$json = file_get_contents_retry($url)) {
          $error = error_get_last();
          printf("HTTP request failed. Error was: " . $error['message'] . "\n Exiting");
          break;
        }
        $kills = json_decode($json, true);
        if(empty($kills)) {
          $url = "https://zkillboard.com/api/kills/w-space/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
          printf("1st attempt at hour kills failed, retrying...\n");
          $kills = json_decode(file_get_contents_retry($url), true);
          if(empty($kills)) {
            $url = "https://zkillboard.com/api/kills/w-space/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
            printf("2nd attempt at hour kills failed, retrying...\n");
            $kills = json_decode(file_get_contents_retry($url), true);
            if(empty($kills) && $loopCount == 0) {
              $lastKill += 1;
              $url = "https://zkillboard.com/api/kills/w-space/no-items/no-attackers/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
              $kills = json_decode(file_get_contents_retry($url), true);
              printf("Incrementing lastKill for last attempt...\n");
              if(empty($kills)) {
                printf("zKill Failure from {$lastKill}, finishing hourKills...\n");
                return 1;
              }
            } else {
              printf("All attempts failed from {$lastKill}, finishing hourKills...\n");
              break;
            }
          }
        }
        if(!empty($kills)) {
          printf("Kills found, analysing...\n");

          foreach($kills as $kill) {

            $shipName = 0;
            $shipType = 0;
            $shipTech = 0;
            $shipRace = 0;
            $class = 0;
            $entitiesInvolved = array();

            $killID = $kill["killID"];
            $iskValue = formatValue($kill["zkb"]["totalValue"]);
            $systemID = $kill["solarSystemID"];
            $killTime = $kill["killTime"];
            $killTimeFormatted = date_format(date_create($killTime),"YmdHi");
            $typeID = $kill["victim"]["shipTypeID"];

            $victimName = $kill["victim"]["characterName"];
            $victimID = $kill["victim"]["characterID"];
            $victimCorpName = $kill["victim"]["corporationName"];
            $victimCorpID = $kill["victim"]["corporationID"];
            $victimAllianceName = $kill["victim"]["allianceName"];
            $victimAllianceID = $kill["victim"]["allianceID"];

            $attackersArray = array_key_exists("attackers", $kill) ? $kill["attackers"] : NULL;
            $involved = count($attackersArray);

            if(in_array($lastKill, $killSeen)) {
              printf("Seen kill - $lastKill\n");
              continue;
            }

            printf("--New Kill - $lastKill--\n");
            //printf("\n{$systemID} - {$typeID} - {$iskValue} ISK - {$involved} Involved\n");

            $typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$typeID}";
            $shipResult = $conn->query($typeQuery);
            if($shipResult->num_rows > 0) {
              while($shipRow = $shipResult->fetch_assoc()) {
                $shipName = $shipRow["name"];
                $shipType = $shipRow["type"];
                $shipTech = $shipRow["tech"];
                $shipRace = $shipRow["race"];
                //printf("{$shipName} - {$shipType} - {$shipTech} - {$shipRace}\n");
              }
            } else {
              printf("Unknown TypeID - {$typeID}\n");
              $lastKill++;
              continue;
            }

            $systemQuery = "SELECT class FROM whsystems WHERE solarSystemID = {$systemID}";
            $systemResult = $conn->query($systemQuery);
            if($systemResult->num_rows > 0) {
              while($systemRow = $systemResult->fetch_assoc()) {
                $class = $systemRow["class"];
              }
            }
            // Thera
            if($class == 30) {
              $class = 7;
            }
            // Shattereds
            if($class == 31 || $class == 32 || $class == 33 || $class == 34 || $class == 35 || $class == 36) {
              $class = 8;
            }
            // Frig Holes
            if($class == 41 || $class == 42 || $class == 43 ) {
              $class = 9;
            }

            if($class == 0 || $class == NULL) {
              continue;
            }

            //Record attackers
            foreach ($attackersArray as $attacker) {
              $attackerShipID = $attacker["shipTypeID"];

              $typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$attackerShipID}";
              $shipResult = $conn->query($typeQuery);
              if(!$shipResult === false && $shipResult->num_rows > 0) {
                while($shipRow = $shipResult->fetch_assoc()) {
                  $attackerShipName = $shipRow["name"];
                  $attackerShipType = $shipRow["type"];
                  $attackerShipTech = $shipRow["tech"];
                  $attackerShipRace = $shipRow["race"];
                  //printf("Ship: {$shipName} - Class: {$shipType} - Tech: {$shipTech} - Race: {$shipRace}\n");
                }
              } else {
                $attackerShipType = NULL;
                $attackerShipTech = NULL;
                $attackerShipRace = NULL;
              }
              $attackerWeaponType = $attacker["weaponTypeID"];
              $attackerCorpName = $attacker["corporationName"];
              $attackerCorpID = $attacker["corporationID"];
              $attackerAllyName = $attacker["allianceName"];
              $attackerAllyID = $attacker["allianceID"];

              // See if we have stats for this corp/alliance, if not add trader_ht_trendmode$typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$attackerShipID}";

              if($attackerAllyID != 0) {
                $entityID = $attackerAllyID;
                $entityName = $attackerAllyName;
                $entityType = "Alliance";
              } else {
                $entityID = $attackerCorpID;
                $entityName = $attackerCorpName;
                $entityType = "Corporation";
              }

              // Check if it's a Citadel, which doesn't have an alliance...
              if($attackerShipID == 35833 || $attackerShipID == 35832 || $attackerShipID == 35834 || $attackerShipID == 40340) {
                printf("Citadel Detected, trying to get alliance of corp {$entityID}\n");
                $corpURL = "https://zkillboard.com/corporation/{$entityID}/";
                try {
                  $html = file_get_html($corpURL);
                } catch (Exception $e) {
                  printf("Error Retrieving HTML data from {$corpURL}\n");
                }

                if($html) {
                  $table = $html->find('table', 1);
                  if($table) {
                    $rows = $table->find('tr', 2);
                    $entityID = filter_var($rows->children(1)->children(0)->href, FILTER_SANITIZE_NUMBER_INT);
                    printf("Citadel Detected - Alliance {$entityID}\n");
                    }
                 }
              }

              $tz = NULL;
              // Check TZ 00.00 - 0600 us, 0600 - 1200 auz, 1200-1800 rus, 1800 - 0000 eu
              $killTimeTZ = date_format(date_create($killTime),"H");
              // Start with US
              if($killTimeTZ >= "00" && $killTimeTZ < "06") {
                printf("**** US TZ KILL - {$killTimeFormatted} ****\n");
                $tz = "us";
              }
              // Now AU
              if($killTimeTZ >= "06" && $killTimeTZ < "12") {
                printf("**** AU TZ KILL - {$killTimeFormatted} ****\n");
                $tz = "au";
              }
              // Now RU
              if($killTimeTZ >= "12" && $killTimeTZ < "18") {
                printf("**** RU TZ KILL - {$killTimeFormatted} ****\n");
                $tz = "ru";
              }
              // Now EU
              if($killTimeTZ >= "18" && $killTimeTZ < "24") {
                printf("**** EU TZ KILL - {$killTimeFormatted} ****\n");
                $tz = "eu";
              }

              if(!$entityID == NULL || !$entityID == 0) {
                printf("Pushing Entity {$entityID} onto Array \n");
                array_push($entitiesInvolved, $entityID);
              }
              $entityQuery = "SELECT * FROM entitystats{$tz} WHERE entityID = {$entityID}";
              $entityResult = $conn->query($entityQuery);
              if(!$entityResult === false && $entityResult->num_rows == 0) {
                printf("Entity {$entityID} not in DB, adding...\n");
                $entityQuery = 'INSERT INTO entitystats'.$tz.' (entityID, entityName, entityType) VALUES ("'.$entityID.'", "'.$entityName.'", "'.$entityType.'")';
                $entityResult = $conn->query($entityQuery);
              } else {
                printf("Entity {$entityID} already exists in DB\n");
              }

              // First get and increment the entitie's use of this shiptype
              if($attackerShipTech == "T1") {
                //Frigs
                if($attackerShipType == "Mining Frigate" || $attackerShipType == "Frigates" || $attackerShipType == "Mining Frigate" || $attackerShipType == "Rookie Ships") {
                  $frigQuery = "UPDATE entitystats{$tz} SET t1FrigUse = t1FrigUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($frigQuery);
                  printf("Query: {$frigQuery}\n");
                }
                if($attackerShipType == "Faction Frigates") {
                  $frigQuery = "UPDATE entitystats{$tz} SET factionFrigUse = factionFrigUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($frigQuery);
                  printf("Query: {$frigQuery}\n");
                }
                //Destroyers
                if($attackerShipType == "Destroyers") {
                  $destroyerQuery = "UPDATE entitystats{$tz} SET t1DestroyerUse = t1DestroyerUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($destroyerQuery);
                  printf("Query: {$destroyerQuery}\n");
                }
                //Cruisers
                if($attackerShipType == "Cruisers") {
                  $cruiserQuery = "UPDATE entitystats{$tz} SET t1CruiserUse = t1CruiserUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($cruiserQuery);
                  printf("Query: {$cruiserQuery}\n");
                }
                if($attackerShipType == "Faction Cruisers") {
                  $cruiserQuery = "UPDATE entitystats{$tz} SET factionCruiserUse = factionCruiserUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($cruiserQuery);
                  printf("Query: {$cruiserQuery}\n");
                }
                //BCs
                if($attackerShipType == "Battlecruisers" || $attackerShipType == "Battlecruisers (Attack)" || $attackerShipType == "Special Edition Battlecruiser") {
                  $bcQuery = "UPDATE entitystats{$tz} SET t1BCUse = t1BCUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($bcQuery);
                  printf("Query: {$bcQuery}\n");
                }
                if($attackerShipType == "Faction Battlecruisers") {
                  $bcQuery = "UPDATE entitystats{$tz} SET factionBCUse = factionBCUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($bcQuery);
                  printf("Query: {$bcQuery}\n");
                }
                //BSs
                if($attackerShipType == "Battleships") {
                  $bsQuery = "UPDATE entitystats{$tz} SET t1BattleshipUse = t1BattleshipUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($bsQuery);
                  printf("Query: {$bsQuery}\n");
                }
                if($attackerShipType == "Faction Battleships") {
                  $bsQuery = "UPDATE entitystats{$tz} SET factionBattleshipUse = factionBattleshipUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($bsQuery);
                  printf("Query: {$bsQuery}\n");
                }
                //Caps
                //Carriers
                if($attackerShipType == "Carriers") {
                  $carrierQuery = "UPDATE entitystats{$tz} SET carrierUse = carrierUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($carrierQuery);
                  printf("Query: {$carrierQuery}\n");
                  if($attackerShipName == "Archon") {
                    $carrierQuery = "UPDATE entitystats{$tz} SET archonUse = archonUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($carrierQuery);
                    printf("Query: {$carrierQuery}\n");
                  }
                  if($attackerShipName == "Nidhoggur") {
                    $carrierQuery = "UPDATE entitystats{$tz} SET nidUse = nidUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($carrierQuery);
                    printf("Query: {$carrierQuery}\n");
                  }
                  if($attackerShipName == "Chimera") {
                    $carrierQuery = "UPDATE entitystats{$tz} SET chimeraUse = chimeraUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($carrierQuery);
                    printf("Query: {$carrierQuery}\n");
                  }
                  if($attackerShipName == "Thanatos") {
                    $carrierQuery = "UPDATE entitystats{$tz} SET thanatosUse = thanatosUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($carrierQuery);
                    printf("Query: {$carrierQuery}\n");
                  }
                }
                //Dreadnoughts
                if($attackerShipType == "Dreadnoughts") {
                  $dreadQuery = "UPDATE entitystats{$tz} SET dreadUse = dreadUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($dreadQuery);
                  printf("Query: {$dreadQuery}\n");
                  if($attackerShipName == "Naglfar") {
                    $dreadQuery = "UPDATE entitystats{$tz} SET nagUse = nagUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($dreadQuery);
                    printf("Query: {$dreadQuery}\n");
                  }
                  if($attackerShipName == "Moros") {
                    $dreadQuery = "UPDATE entitystats{$tz} SET morosUse = morosUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($dreadQuery);
                    printf("Query: {$dreadQuery}\n");
                  }
                  if($attackerShipName == "Phoenix") {
                    $dreadQuery = "UPDATE entitystats{$tz} SET phoenixUse = phoenixUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($dreadQuery);
                    printf("Query: {$dreadQuery}\n");
                  }
                  if($attackerShipName == "Revelation") {
                    $dreadQuery = "UPDATE entitystats{$tz} SET revUse = revUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($dreadQuery);
                    printf("Query: {$dreadQuery}\n");
                  }
                }
                //Force Auxiliaries
                if($attackerShipType == "Force Auxiliary") {
                  $faxQuery = "UPDATE entitystats{$tz} SET dreadUse = faxUse + 1 WHERE faxUse = {$entityID}";
                  $conn->query($faxQuery);
                  printf("Query: {$faxQuery}\n");
                  if($attackerShipName == "Apostle") {
                    $faxQuery = "UPDATE entitystats{$tz} SET apostleUse = apostleUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($faxQuery);
                    printf("Query: {$faxQuery}\n");
                  }
                  if($attackerShipName == "Lif") {
                    $faxQuery = "UPDATE entitystats{$tz} SET lifUse = lifUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($faxQuery);
                    printf("Query: {$faxQuery}\n");
                  }
                  if($attackerShipName == "Minokawa") {
                    $faxQuery = "UPDATE entitystats{$tz} SET minokawaUse = minokawaUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($faxQuery);
                    printf("Query: {$faxQuery}\n");
                  }
                  if($attackerShipName == "Ninazu") {
                    $faxQuery = "UPDATE entitystats{$tz} SET ninazuUse = ninazuUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($faxQuery);
                    printf("Query: {$faxQuery}\n");
                  }
                }
              }

              if($attackerShipTech == "T2") {
                if($attackerShipType == "Assault Frigates" || $attackerShipType == "Covert Ops" || $attackerShipType == "Electronic Attack Frigates" ||
                   $attackerShipType == "Interceptors" || $attackerShipType == "Logistics Frigate" || $attackerShipType == "Stealth Bombers") {
                     $t2FrigQuery = "UPDATE entitystats{$tz} SET t2FrigUse = t2FrigUse + 1 WHERE entityID = {$entityID}";
                     $conn->query($t2FrigQuery);
                     printf("Query: {$t2FrigQuery}\n");
                     if($attackerShipType == "Logistics Frigate") {
                       $frigLogiQuery = "UPDATE entitystats{$tz} SET frigLogiUse = frigLogiUse + 1 WHERE entityID = {$entityID}";
                       $conn->query($frigLogiQuery);
                       printf("Query: {$frigLogiQuery}\n");
                     }
                 }
                 if($attackerShipType == "Interdictors" || $attackerShipType == "	Command Destroyers") {
                    $t2DesQuery = "UPDATE entitystats{$tz} SET t2DestroyerUse = t2DestroyerUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($t2DesQuery);
                    printf("Query: {$t2DesQuery}\n");
                 }
                 if($attackerShipType == "Force Recon" || $attackerShipType == "Heavy Assault Cruisers" || $attackerShipType == "Heavy Interdictors" ||
                    $attackerShipType == "Logistics Cruisers" || $attackerShipType == "Recon Ships" ) {
                    $t2CruiserQuery = "UPDATE entitystats{$tz} SET t2CruiserUse = t2CruiserUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($t2CruiserQuery);
                    printf("Query: {$t2CruiserQuery}\n");
                    if($attackerShipType == "Logistics Cruisers") {
                      $t2CruiserQuery = "UPDATE entitystats{$tz} SET cruiserLogiUse = cruiserLogiUse + 1 WHERE entityID = {$entityID}";
                      $conn->query($t2CruiserQuery);
                      printf("Query: {$t2CruiserQuery}\n");
                    }
                  }
                  if($attackerShipType == "Command Ships") {
                    $t2BCQuery = "UPDATE entitystats{$tz} SET t2BCUse = t2BCUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($t2BCQuery);
                    printf("Query: {$t2BCQuery}\n");
                  }
                  if($attackerShipType == "Marauders" || $attackerShipType == "Black Ops") {
                    $t2BSQuery = "UPDATE entitystats{$tz} SET t2BattleshipUse = t2BattleshipUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($t2BSQuery);
                    printf("Query: {$t2BSQuery}\n");
                  }
              }

              if($attackerShipTech == "T3") {
                if($attackerShipType == "Tactical Destroyers") {
                  $t3DesQuery = "UPDATE entitystats{$tz} SET t3DestroyerUse = t3DestroyerUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t3DesQuery);
                  printf("Query: {$t3DesQuery}\n");
                }
                if($attackerShipType == "Strategic Cruisers") {
                  $t3CruiserQuery = "UPDATE entitystats{$tz} SET t3CruiserUse = t3CruiserUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t3CruiserQuery);
                  printf("Query: {$t3CruiserQuery}\n");
                }
              }

              // Neuts
              if($attackerWeaponType == "533" || $attackerWeaponType == "4471" || $attackerWeaponType == "4473" || $attackerWeaponType == "4475" || $attackerWeaponType == "4477" ||
                 $attackerWeaponType == "12265" || $attackerWeaponType == "12267" || $attackerWeaponType == "12269" || $attackerWeaponType == "12271" || $attackerWeaponType == "12271" ||
                 $attackerWeaponType == "13003" || $attackerWeaponType == "14160" || $attackerWeaponType == "14162" || $attackerWeaponType == "14164" || $attackerWeaponType == "14166" ||
                 $attackerWeaponType == "14168" || $attackerWeaponType == "14170" || $attackerWeaponType == "14832" || $attackerWeaponType == "14834" || $attackerWeaponType == "14836" ||
                 $attackerWeaponType == "14838" || $attackerWeaponType == "14840" || $attackerWeaponType == "14842" || $attackerWeaponType == "14844" || $attackerWeaponType == "14846" ||
                 $attackerWeaponType == "15794" || $attackerWeaponType == "15796" || $attackerWeaponType == "15798" || $attackerWeaponType == "15800" || $attackerWeaponType == "15802" ||
                 $attackerWeaponType == "15804" || $attackerWeaponType == "16465" || $attackerWeaponType == "16467" || $attackerWeaponType == "16469" || $attackerWeaponType == "16471" ||
                 $attackerWeaponType == "16473" || $attackerWeaponType == "16475" || $attackerWeaponType == "16477" || $attackerWeaponType == "16479" || $attackerWeaponType == "37622" ||
                 $attackerWeaponType == "37623" || $attackerWeaponType == "37624" || $attackerWeaponType == "37625" || $attackerWeaponType == "37626" || $attackerWeaponType == "37627" ||
                 $attackerWeaponType == "37628" || $attackerWeaponType == "37629" || $attackerWeaponType == "37630" || $attackerWeaponType == "37631" || $attackerWeaponType == "40659" ||
                 $attackerWeaponType == "40660" || $attackerWeaponType == "40661" || $attackerWeaponType == "40662" || $attackerWeaponType == "40663" || $attackerWeaponType == "40664") {
                   $neutsQuery = "UPDATE entitystats{$tz} SET neutsUse = neutsUse + 1 WHERE entityID = {$entityID}";
                   $conn->query($neutsQuery);
                   printf("Query: {$neutsQuery}\n");
              }
              //Jams
              if($attackerWeaponType == "28729" || $attackerWeaponType == "28731" || $attackerWeaponType == "28733" || $attackerWeaponType == "28735" || $attackerWeaponType == "28737" ||
                 $attackerWeaponType == "19923" || $attackerWeaponType == "19925" || $attackerWeaponType == "19927" || $attackerWeaponType == "19929" || $attackerWeaponType == "19931" ||
                 $attackerWeaponType == "19933" || $attackerWeaponType == "19935" || $attackerWeaponType == "19937" || $attackerWeaponType == "19939" || $attackerWeaponType == "19942" ||
                 $attackerWeaponType == "19944" || $attackerWeaponType == "19946" || $attackerWeaponType == "19948" || $attackerWeaponType == "19950" || $attackerWeaponType == "19952" ||
                 $attackerWeaponType == "20199" || $attackerWeaponType == "20201" || $attackerWeaponType == "20203" || $attackerWeaponType == "20205" || $attackerWeaponType == "20207" ||
                 $attackerWeaponType == "20573" || $attackerWeaponType == "20574" || $attackerWeaponType == "20575" || $attackerWeaponType == "20576" || $attackerWeaponType == "20577" ||
                 $attackerWeaponType == "20578" || $attackerWeaponType == "20579" || $attackerWeaponType == "20580" || $attackerWeaponType == "5359" || $attackerWeaponType == "9518" ||
                 $attackerWeaponType == "9519" || $attackerWeaponType == "9520" || $attackerWeaponType == "9521" || $attackerWeaponType == "9522" || $attackerWeaponType == "2559" ||
                 $attackerWeaponType == "2563" || $attackerWeaponType == "2567" || $attackerWeaponType == "2571" || $attackerWeaponType == "2575" || $attackerWeaponType == "1948" ||
                 $attackerWeaponType == "1955" || $attackerWeaponType == "1956" || $attackerWeaponType == "1957" || $attackerWeaponType == "1958") {
                   $jamsQuery = "UPDATE entitystats{$tz} SET jamsUse = jamsUse + 1 WHERE entityID = {$entityID}";
                   $conn->query($jamsQuery);
                   printf("Query: {$jamsQuery}\n");
              }
              //Damps
              if($attackerWeaponType == "1968" || $attackerWeaponType == "1969" || $attackerWeaponType == "5299" || $attackerWeaponType == "5300" || $attackerWeaponType == "5301" ||
                 $attackerWeaponType == "5302" || $attackerWeaponType == "22943" || $attackerWeaponType == "22945" || $attackerWeaponType == "32413") {
                   $dampQuery = "UPDATE entitystats{$tz} SET	dampsUse = dampsUse + 1 WHERE entityID = {$entityID}";
                   $conn->query($dampQuery);
                   printf("Query: {$dampQuery}\n");
              }
            }

            $entitiesCount = array_count_values($entitiesInvolved);
            foreach ($entitiesCount as $entity => $count) {
              // Check Avg Fleet Size
              $query = "SELECT avgFleetSize, whKills, largestFleetSize FROM entitystats{$tz} WHERE entityID = {$entity}";
              $result = $conn->query($query);
              if($result->num_rows > 0) {
                  $row = $result->fetch_assoc();
                  $whKills = $row["whKills"];
                  $avgFleet = $row["avgFleetSize"];
                  $largestFleet = $row["largestFleetSize"];
              } else {
                  $whKills = 1;
                  $avgFleet = 1;
                  $largestFleet = 1;
              }
              $oldTotal = $avgFleet * $whKills;
              $newAvg = ($oldTotal + $count) / ($whKills + 1);
              $updateQuery = "UPDATE entitystats{$tz} SET avgFleetSize = {$newAvg} WHERE entityID = {$entity}";
              $conn->query($updateQuery);
              printf("Query: {$updateQuery}\n");
              // Update
              $largestFleet > $count ? $largestFleet : $largestFleet = $count;
              $updateQuery = "UPDATE entitystats{$tz} SET largestFleetSize = {$largestFleet} WHERE entityID = {$entity}";
              $conn->query($updateQuery);
              printf("Query: {$updateQuery}\n");
              if($class == 1) {
                $classQuery = "UPDATE entitystats{$tz} SET	c1Kills = c1Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 2) {
                $classQuery = "UPDATE entitystats{$tz} SET	c2Kills = c2Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 3) {
                $classQuery = "UPDATE entitystats{$tz} SET	c3Kills = c3Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 4) {
                $classQuery = "UPDATE entitystats{$tz} SET	c4Kills = c4Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 5) {
                $classQuery = "UPDATE entitystats{$tz} SET	c5Kills = c5Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 6) {
                $classQuery = "UPDATE entitystats{$tz} SET c6Kills = c6Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 7) {
                $classQuery = "UPDATE entitystats{$tz} SET	c7Kills = c7Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 8) {
                $classQuery = "UPDATE entitystats{$tz} SET	c8Kills = c8Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }
              if($class == 9) {
                $classQuery = "UPDATE entitystats{$tz} SET	c9Kills = c9Kills + 1 WHERE entityID = {$entity}";
                $conn->query($classQuery);
                printf("Query: {$classQuery}\n");
                $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                $conn->query($whKillsQuery);
                printf("Query: {$whKillsQuery}\n");
              }

              // Last Seen Kill
              $lastKillQuery = "UPDATE entitystats{$tz} SET lastKill = {$killID} WHERE entityID = {$entity}";
              $conn->query($lastKillQuery);
              printf("Query: {$lastKillQuery}\n");
            }

            // Repeat for Victim
            // ---------------------------------------------------------------------------
            // Now Count the Victim
            $entityID = $victimCorpID;
            if($victimAllianceID != 0) {
              $entityID = $victimAllianceID;
              $entityName = $victimAllianceName;
              $entityType = "Alliance";
            } else {
              $entityID = $victimCorpID;
              $entityName = $victimCorpName;
              $entityType = "Corporation";
            }

            $entityQuery = "SELECT * FROM entitystats{$tz} WHERE entityID = {$entityID}";
            $entityResult = $conn->query($entityQuery);
            if(!$entityResult === false && $entityResult->num_rows == 0) {
              printf("Entity {$entityID} not in DB, adding...\n");
              $entityQuery = 'INSERT INTO entitystats'.$tz.' (entityID, entityName, entityType) VALUES ("'.$entityID.'", "'.$entityName.'", "'.$entityType.'")';
              $entityResult = $conn->query($entityQuery);
              printf("Query: {$entityQuery}\n");
            } else {
              printf("Entity {$entityID} already exists in DB\n");
            }

            // First get and increment the entitie's use of this shiptype
            if($shipTech == "T1") {
              //Frigs
              if($shipType == "Mining Frigate" || $shipType == "Frigates" || $shipType == "Mining Frigate" || $shipType == "Rookie Ships") {
                $frigQuery = "UPDATE entitystats{$tz} SET t1FrigUse = t1FrigUse + 1 WHERE entityID = {$entityID}";
                $conn->query($frigQuery);
                printf("Query: {$frigQuery}\n");
              }
              if($shipType == "Faction Frigates") {
                $frigQuery = "UPDATE entitystats{$tz} SET factionFrigUse = factionFrigUse + 1 WHERE entityID = {$entityID}";
                $conn->query($frigQuery);
                printf("Query: {$frigQuery}\n");
              }
              //Destroyers
              if($shipType == "Destroyers") {
                $destroyerQuery = "UPDATE entitystats{$tz} SET t1DestroyerUse = t1DestroyerUse + 1 WHERE entityID = {$entityID}";
                $conn->query($destroyerQuery);
                printf("Query: {$destroyerQuery}\n");
              }
              //Cruisers
              if($shipType == "Cruisers") {
                $cruiserQuery = "UPDATE entitystats{$tz} SET t1CruiserUse = t1CruiserUse + 1 WHERE entityID = {$entityID}";
                $conn->query($cruiserQuery);
                printf("Query: {$cruiserQuery}\n");
              }
              if($shipType == "Faction Cruisers") {
                $cruiserQuery = "UPDATE entitystats{$tz} SET factionCruiserUse = factionCruiserUse + 1 WHERE entityID = {$entityID}";
                $conn->query($cruiserQuery);
                printf("Query: {$cruiserQuery}\n");
              }
              //BCs
              if($shipType == "Battlecruisers" || $shipType == "Battlecruisers (Attack)" || $shipType == "Special Edition Battlecruiser") {
                $bcQuery = "UPDATE entitystats{$tz} SET t1BCUse = t1BCUse + 1 WHERE entityID = {$entityID}";
                $conn->query($bcQuery);
                printf("Query: {$bcQuery}\n");
              }
              if($shipType == "Faction Battlecruisers") {
                $bcQuery = "UPDATE entitystats{$tz} SET factionBCUse = factionBCUse + 1 WHERE entityID = {$entityID}";
                $conn->query($bcQuery);
                printf("Query: {$bcQuery}\n");
              }
              //BSs
              if($shipType == "Battleships") {
                $bsQuery = "UPDATE entitystats{$tz} SET t1BattleshipUse = t1BattleshipUse + 1 WHERE entityID = {$entityID}";
                $conn->query($bsQuery);
                printf("Query: {$bsQuery}\n");
              }
              if($shipType == "Faction Battleships") {
                $bsQuery = "UPDATE entitystats{$tz} SET factionBattleshipUse = factionBattleshipUse + 1 WHERE entityID = {$entityID}";
                $conn->query($bsQuery);
                printf("Query: {$bsQuery}\n");
              }
              //Caps
              //Carriers
              if($shipType == "Carriers") {
                $carrierQuery = "UPDATE entitystats{$tz} SET carrierUse = carrierUse + 1 WHERE entityID = {$entityID}";
                $conn->query($carrierQuery);
                printf("Query: {$carrierQuery}\n");
                if($attackerShipName == "Archon") {
                  $carrierQuery = "UPDATE entitystats{$tz} SET archonUse = archonUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($carrierQuery);
                  printf("Query: {$carrierQuery}\n");
                }
                if($attackerShipName == "Nidhoggur") {
                  $carrierQuery = "UPDATE entitystats{$tz} SET nidUse = nidUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($carrierQuery);
                  printf("Query: {$carrierQuery}\n");
                }
                if($attackerShipName == "Chimera") {
                  $carrierQuery = "UPDATE entitystats{$tz} SET chimeraUse = chimeraUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($carrierQuery);
                  printf("Query: {$carrierQuery}\n");
                }
                if($attackerShipName == "Thanatos") {
                  $carrierQuery = "UPDATE entitystats{$tz} SET thanatosUse = thanatosUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($carrierQuery);
                  printf("Query: {$carrierQuery}\n");
                }
              }
              //Dreadnoughts
              if($shipType == "Dreadnoughts") {
                $dreadQuery = "UPDATE entitystats{$tz} SET dreadUse = dreadUse + 1 WHERE entityID = {$entityID}";
                $conn->query($dreadQuery);
                printf("Query: {$dreadQuery}\n");
                if($attackerShipName == "Naglfar") {
                  $dreadQuery = "UPDATE entitystats{$tz} SET nagUse = nagUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($dreadQuery);
                  printf("Query: {$dreadQuery}\n");
                }
                if($attackerShipName == "Moros") {
                  $dreadQuery = "UPDATE entitystats{$tz} SET morosUse = morosUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($dreadQuery);
                  printf("Query: {$dreadQuery}\n");
                }
                if($attackerShipName == "Phoenix") {
                  $dreadQuery = "UPDATE entitystats{$tz} SET phoenixUse = phoenixUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($dreadQuery);
                  printf("Query: {$dreadQuery}\n");
                }
                if($attackerShipName == "Revelation") {
                  $dreadQuery = "UPDATE entitystats{$tz} SET revUse = revUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($dreadQuery);
                  printf("Query: {$dreadQuery}\n");
                }
              }
              //Force Auxiliaries
              if($shipType == "Force Auxiliary") {
                $faxQuery = "UPDATE entitystats{$tz} SET dreadUse = faxUse + 1 WHERE faxUse = {$entityID}";
                $conn->query($faxQuery);
                printf("Query: {$faxQuery}\n");
                if($attackerShipName == "Apostle") {
                  $faxQuery = "UPDATE entitystats{$tz} SET apostleUse = apostleUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($faxQuery);
                  printf("Query: {$faxQuery}\n");
                }
                if($attackerShipName == "Lif") {
                  $faxQuery = "UPDATE entitystats{$tz} SET lifUse = lifUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($faxQuery);
                  printf("Query: {$faxQuery}\n");
                }
                if($attackerShipName == "Minokawa") {
                  $faxQuery = "UPDATE entitystats{$tz} SET minokawaUse = minokawaUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($faxQuery);
                  printf("Query: {$faxQuery}\n");
                }
                if($attackerShipName == "Ninazu") {
                  $faxQuery = "UPDATE entitystats{$tz} SET ninazuUse = ninazuUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($faxQuery);
                  printf("Query: {$faxQuery}\n");
                }
              }
            }

            if($shipTech == "T2") {
              if($shipType == "Assault Frigates" || $shipType == "Covert Ops" || $shipType == "Electronic Attack Frigates" ||
                 $shipType == "Interceptors" || $shipType == "Logistics Frigate" || $shipType == "Stealth Bombers") {
                   $t2FrigQuery = "UPDATE entitystats{$tz} SET t2FrigUse = t2FrigUse + 1 WHERE entityID = {$entityID}";
                   $conn->query($t2FrigQuery);
                   printf("Query: {$t2FrigQuery}\n");
                   if($shipType == "Logistics Frigate") {
                     $frigLogiQuery = "UPDATE entitystats{$tz} SET frigLogiUse = frigLogiUse + 1 WHERE entityID = {$entityID}";
                     $conn->query($frigLogiQuery);
                     printf("Query: {$frigLogiQuery}\n");
                   }
               }
               if($shipType == "Interdictors" || $shipType == "	Command Destroyers") {
                  $t2DesQuery = "UPDATE entitystats{$tz} SET t2DestroyerUse = t2DestroyerUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t2DesQuery);
                  printf("Query: {$t2DesQuery}\n");
               }
               if($shipType == "Force Recon" || $shipType == "Heavy Assault Cruisers" || $shipType == "Heavy Interdictors" ||
                  $shipType == "Logistics Cruisers" || $shipType == "Recon Ships" ) {
                  $t2CruiserQuery = "UPDATE entitystats{$tz} SET t2CruiserUse = t2CruiserUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t2CruiserQuery);
                  printf("Query: {$t2CruiserQuery}\n");
                  if($shipType == "Logistics Cruisers") {
                    $t2CruiserQuery = "UPDATE entitystats{$tz} SET cruiserLogiUse = cruiserLogiUse + 1 WHERE entityID = {$entityID}";
                    $conn->query($t2CruiserQuery);
                    printf("Query: {$t2CruiserQuery}\n");
                  }
                }
                if($shipType == "Command Ships") {
                  $t2BCQuery = "UPDATE entitystats{$tz} SET t2BCUse = t2BCUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t2BCQuery);
                  printf("Query: {$t2BCQuery}\n");
                }
                if($shipType == "Marauders" || $shipType == "Black Ops") {
                  $t2BSQuery = "UPDATE entitystats{$tz} SET t2BattleshipUse = t2BattleshipUse + 1 WHERE entityID = {$entityID}";
                  $conn->query($t2BSQuery);
                  printf("Query: {$t2BSQuery}\n");
                }
            }

            if($shipTech == "T3") {
              if($shipType == "Tactical Destroyers") {
                $t3DesQuery = "UPDATE entitystats{$tz} SET t3DestroyerUse = t3DestroyerUse + 1 WHERE entityID = {$entityID}";
                $conn->query($t3DesQuery);
                printf("Query: {$t3DesQuery}\n");
              }
              if($shipType == "Strategic Cruisers") {
                $t3CruiserQuery = "UPDATE entitystats{$tz} SET t3CruiserUse = t3CruiserUse + 1 WHERE entityID = {$entityID}";
                $conn->query($t3CruiserQuery);
                printf("Query: {$t3CruiserQuery}\n");
              }
            }

            // // Neuts
            // if($attackerWeaponType == "533" || $attackerWeaponType == "4471" || $attackerWeaponType == "4473" || $attackerWeaponType == "4475" || $attackerWeaponType == "4477" ||
            //    $attackerWeaponType == "12265" || $attackerWeaponType == "12267" || $attackerWeaponType == "12269" || $attackerWeaponType == "12271" || $attackerWeaponType == "12271" ||
            //    $attackerWeaponType == "13003" || $attackerWeaponType == "14160" || $attackerWeaponType == "14162" || $attackerWeaponType == "14164" || $attackerWeaponType == "14166" ||
            //    $attackerWeaponType == "14168" || $attackerWeaponType == "14170" || $attackerWeaponType == "14832" || $attackerWeaponType == "14834" || $attackerWeaponType == "14836" ||
            //    $attackerWeaponType == "14838" || $attackerWeaponType == "14840" || $attackerWeaponType == "14842" || $attackerWeaponType == "14844" || $attackerWeaponType == "14846" ||
            //    $attackerWeaponType == "15794" || $attackerWeaponType == "15796" || $attackerWeaponType == "15798" || $attackerWeaponType == "15800" || $attackerWeaponType == "15802" ||
            //    $attackerWeaponType == "15804" || $attackerWeaponType == "16465" || $attackerWeaponType == "16467" || $attackerWeaponType == "16469" || $attackerWeaponType == "16471" ||
            //    $attackerWeaponType == "16473" || $attackerWeaponType == "16475" || $attackerWeaponType == "16477" || $attackerWeaponType == "16479" || $attackerWeaponType == "37622" ||
            //    $attackerWeaponType == "37623" || $attackerWeaponType == "37624" || $attackerWeaponType == "37625" || $attackerWeaponType == "37626" || $attackerWeaponType == "37627" ||
            //    $attackerWeaponType == "37628" || $attackerWeaponType == "37629" || $attackerWeaponType == "37630" || $attackerWeaponType == "37631" || $attackerWeaponType == "40659" ||
            //    $attackerWeaponType == "40660" || $attackerWeaponType == "40661" || $attackerWeaponType == "40662" || $attackerWeaponType == "40663" || $attackerWeaponType == "40664" ) {
            //      $neutsQuery = "UPDATE entitystats{$tz} SET neutsUse = neutsUse + 1 WHERE entityID = {$entityID}";
            //      $conn->query($neutsQuery);
            //      printf("Query: {$neutsQuery}\n");
            // }
            //Jams
            // if($attackerWeaponType == "28729" || $attackerWeaponType == "28731" || $attackerWeaponType == "28733" || $attackerWeaponType == "28735" || $attackerWeaponType == "28737" ||
            //    $attackerWeaponType == "19923" || $attackerWeaponType == "19925" || $attackerWeaponType == "19927" || $attackerWeaponType == "19929" || $attackerWeaponType == "19931" ||
            //    $attackerWeaponType == "19933" || $attackerWeaponType == "19935" || $attackerWeaponType == "19937" || $attackerWeaponType == "19939" || $attackerWeaponType == "19942" ||
            //    $attackerWeaponType == "19944" || $attackerWeaponType == "19946" || $attackerWeaponType == "19948" || $attackerWeaponType == "19950" || $attackerWeaponType == "19952" ||
            //    $attackerWeaponType == "20199" || $attackerWeaponType == "20201" || $attackerWeaponType == "20203" || $attackerWeaponType == "20205" || $attackerWeaponType == "20207" ||
            //    $attackerWeaponType == "20573" || $attackerWeaponType == "20574" || $attackerWeaponType == "20575" || $attackerWeaponType == "20576" || $attackerWeaponType == "20577" ||
            //    $attackerWeaponType == "20578" || $attackerWeaponType == "20579" || $attackerWeaponType == "20580" || $attackerWeaponType == "5359" || $attackerWeaponType == "9518" ||
            //    $attackerWeaponType == "9519" || $attackerWeaponType == "9520" || $attackerWeaponType == "9521" || $attackerWeaponType == "9522" || $attackerWeaponType == "2559" ||
            //    $attackerWeaponType == "2563" || $attackerWeaponType == "2567" || $attackerWeaponType == "2571" || $attackerWeaponType == "2575" || $attackerWeaponType == "1948" ||
            //    $attackerWeaponType == "1955" || $attackerWeaponType == "1956" || $attackerWeaponType == "1957" || $attackerWeaponType == "1958") {
            //      $jamsQuery = "UPDATE entitystats{$tz} SET jamsUse = jamsUse + 1 WHERE entityID = {$entityID}";
            //      $conn->query($jamsQuery);
            //      printf("Query: {$jamsQuery}\n");
            // }
            //Damps
            //if($attackerWeaponType == "1968" || $attackerWeaponType == "1969" || $attackerWeaponType == "5299" || $attackerWeaponType == "5300" || $attackerWeaponType == "5301" ||
            //   $attackerWeaponType == "5302" || $attackerWeaponType == "22943" || $attackerWeaponType == "22945" || $attackerWeaponType == "32413") {
            //     $dampQuery = "UPDATE entitystats{$tz} SET	dampsUse = dampsUse + 1 WHERE entityID = {$entityID}";
            //     $conn->query($dampQuery);
            //     printf("Query: {$dampQuery}\n");
            //}

            // $query = "SELECT avgFleetSize, whKills, largestFleetSize FROM entitystats{$tz} WHERE entityID = {$entity}";
            // $result = $conn->query($query);
            // printf("Query: {$query}\n");
            // if($result->num_rows > 0) {
            //     $row = $result->fetch_assoc();
            //     $whKills = $row["whKills"];
            //     $avgFleet = $row["avgFleetSize"];
            //     $largestFleet = $row["largestFleetSize"];
            // } else {
            //     $whKills = 1;
            //     $avgFleet = 1;
            //     $largestFleet = 0;
            // }
            // $oldTotal = $avgFleet * $whKills;
            // $newAvg = ($oldTotal + $involved) / ($whKills + 1);
            // $updateQuery = "UPDATE entitystats{$tz} SET avgFleetSize = {$newAvg} WHERE entityID = {$entity}";
            // $conn->query($updateQuery);
            // printf("Query: {$updateQuery}\n");
            // // Update
            // $largestFleet > $count ? $largestFleet : $largestFleet = $count;
            // $updateQuery = "UPDATE entitystats{$tz} SET largestFleetSize = {$largestFleet} WHERE entityID = {$entity}";
            // $conn->query($updateQuery);
            // printf("Query: {$updateQuery}\n");
            //
            // if($class == 1) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c1Kills = c1Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 2) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c2Kills = c2Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 3) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c3Kills = c3Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 4) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c4Kills = c4Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 5) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c5Kills = c5Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 6) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c6Kills = c6Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 7) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c7Kills = c7Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 8) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c8Kills = c8Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }
            // if($class == 9) {
            //   $classQuery = "UPDATE entitystats{$tz} SET	c9Kills = c9Kills + 1 WHERE entityID = {$entity}";
            //   $conn->query($classQuery);
            //   printf("Query: {$classQuery}\n");
            //   $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
            //   $conn->query($whKillsQuery);
            //   printf("Query: {$whKillsQuery}\n");
            // }

            printf("Pushing $killID onto newKills and seenKills\n");
            array_push($newKills, $killID);
            array_push($killSeen, $killID);
          }
          if(count($newKills) >= (190 * ($loopCount + 1))) {
            $continue = TRUE;
            printf("Continuing from kill {$killID}\n");
          } else {
            $continue = FALSE;
            printf("No more kills between {$fromHour} and {$toHour} - Last kill: {$killID}\n");
          }

            // Last Seen Kill
            $lastKillQuery = "UPDATE entitystats{$tz} SET lastKill = {$killID} WHERE entityID = {$entity}";
            $conn->query($lastKillQuery);
            printf("Query: {$lastKillQuery}\n");
      }
      else {
        $continue = FALSE;
        printf("All kills retrieved to {$killID}\n");
      }
      $loopCount++;
    }

    // Last Seen Kill
    $lastKillQuery = "UPDATE entitystats{$tz} SET lastKill = {$killID} WHERE entityID = {$entity}";
    $conn->query($lastKillQuery);
    printf("Query: {$lastKillQuery}\n");

    foreach($newKills as $newKill) {
      $newKillQuery = "INSERT INTO entitykillseen (killID) VALUES ({$newKill})";
      $conn->query($newKillQuery);
    }

    $conn->close();
    printf("DONE\n");
    return 0;
  }
?>
