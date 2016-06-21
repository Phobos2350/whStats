<?php
    include 'simple_html_dom.php';
    date_default_timezone_set('Etc/GMT');

foreach ($argv as $i => $arg) {
    if ($arg == "getKills") {
        getKills($argv[$i+1]);
    }
}

function file_get_contents_retry($url, $attemptsRemaining = 3)
{
    printf("Attempting to retrieve {$url}, {$attemptsRemaining} attempts remaining.\n");
    $content = file_get_contents($url);
    $attemptsRemaining--;

    if (empty($content) && $attemptsRemaining > 0) {
        return file_get_contents_retry($url, $attemptsRemaining);
    }

    return $content;
}

function formatValue($n)
{
    if (!is_numeric($n)) {
        return 0;
    }
    return round(($n/1000000), 1);
}

function getKills($updateType)
{
    printf("\n\n---------------------------------------------------------------------------------------------------------\n");
    printf("Called Function getKills with parameter {$updateType}\n");
    $servername = "localhost";
    $username = "whdata";
    $password = "whdata";
    $db = "killstats";
    $currentTime = 0;
    $fromHour = 0;
    $toHour = 0;
    $continue = true;
    $killID = 0;
    $loopCount = 0;
    $killSeen = array();
    $newKills = array();
    $hourKills = array();

    for ($i = 1; $i < 10; $i++) {
        $hourKills["C{$i}"] = array(
        "hourKills" => 0,
        "industrialKillsHour" => 0,
        "t1KillsHour" => 0,
        "t2KillsHour" => 0,
        "t3KillsHour" => 0,
        "capKillsHour" => 0,
        "battleshipKillsHour" => 0,
          "cruiserKillsHour" => 0,
        "frigateKillsHour" => 0,
        "forceAuxKillsHour" => 0,
        "dreadKillsHour" => 0,
        "carrierKillsHour" => 0,
        "podKillsHour" => 0,
        "structureKillsHour" => 0,
        "citadelKillsHour" => 0,
        "hourISK" => 0,
        "smallKillsHour" => 0,
        "fleetKillsHour" => 0,
        "logiKillsHour" => 0,
        "factionKillsHour" => 0
        );
    }
  //print_r($hourKills);

    $currentTime = date('Y-m-d H:i:s', time());
    $fromHour = date('YmdH00', strtotime($currentTime . '- 1 hour'));
    $toHour = date_format(date_create($currentTime), 'YmdH00');
    printf("WHStats Check at {$currentTime}\n");
    printf("FromHour: {$fromHour} - ToHour: {$toHour}\n");
    $conn = new mysqli($servername, $username, $password, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $killIDQuery = "SELECT killID FROM lastKill";
    $killID = $conn->query($killIDQuery)->fetch_assoc();
    $killID = $killID["killID"];
    printf("Last Kill - ".$killID."\n");

    $fullRefreshQuery = "SELECT fromDate FROM lastKill";
    $fullRefresh = $conn->query($fullRefreshQuery)->fetch_assoc();
    $fullRefresh = $fullRefresh["fromDate"];

    if ($updateType == "cleanRefresh") {
        printf("Performing a complete refresh from {$fullRefresh}\nPurging totalkills table\n");
        $purgeStatsQuery = "TRUNCATE TABLE totalkills";
        $conn->query($purgeStatsQuery);
        for ($i = 1; $i < 10; $i++) {
            $cleanDBQuery = "INSERT INTO totalkills (class) VALUES ({$i})";
            $conn->query($cleanDBQuery);
          //printf("Creating row in totalkills - {$cleanDBQuery}\n");
        }
    }
    if ($updateType == "newMonth" || $updateType == "cleanRefresh") {
        // Let's clear the previous hour's seen kills
        printf("New Month, purging monthlykills table...\n");
        $purgeMonthlyQuery = "TRUNCATE TABLE monthlykills";
        $conn->query($purgeMonthlyQuery);
        for ($i = 1; $i < 10; $i++) {
            $cleanMonthlyQuery = "INSERT INTO monthlykills (class) VALUES ({$i})";
            $conn->query($cleanMonthlyQuery);
          //printf("Creating row in monthlykills - {$cleanMonthlyQuery}\n");
        }
    }
    if ($updateType == "newWeek" || $updateType == "cleanRefresh") {
        // Let's clear the previous hour's seen kills
        printf("New Week, purging weeklykills table...\n");
        $purgeWeeklyQuery = "TRUNCATE TABLE weeklykills";
        $conn->query($purgeWeeklyQuery);
        for ($i = 1; $i < 10; $i++) {
            $cleanWeeklyQuery = "INSERT INTO weeklykills (class) VALUES ({$i})";
            $conn->query($cleanWeeklyQuery);
          //printf("Creating row in weeklykills - {$cleanWeeklyQuery}\n");
        }
    }
    if ($updateType == "newDay" || $updateType == "cleanRefresh") {
        // Let's clear the previous hour's seen kills
        printf("New Day, purging dailykills table...\n");
        $purgeDailyQuery = "TRUNCATE TABLE dailykills";
        $conn->query($purgeDailyQuery);
        for ($i = 1; $i < 10; $i++) {
            $cleanDailyQuery = "INSERT INTO dailykills (class) VALUES ({$i})";
            $conn->query($cleanDailyQuery);
          //printf("Creating row in dailykills - {$cleanDailyQuery}\n");
        }
    }
    if ($updateType == "newHour" || $updateType == "cleanRefresh") {
        // Let's clear the previous hour's seen kills
        printf("New Hour, purging killseen table...\n");
        $killSeen = array();
        $killSeenQuery = "TRUNCATE TABLE killseen";
        $killSeenResult = $conn->query($killSeenQuery);
    }
    if ($updateType == "refreshCurrent") {
        $killSeenQuery = "SELECT * FROM killseen";
        $killSeenResult = $conn->query($killSeenQuery);
        // Make sure we get first row... no idea why it breaks
        $killSeenRow = $killSeenResult->fetch_assoc();
        if ($killSeenResult->num_rows > 0) {
            do {
                array_push($killSeen, $killSeenRow["killID"]);
            } while ($killSeenRow = $killSeenResult->fetch_assoc());
        } else {
            printf("killSeen db table Empty!\n");
        }
          //printf("killSeen Array:\n");
          //print_r($killSeen);
          //printf("\nkillSeen Finished\n");
    }


    while ($continue) {
        printf("Loop {$loopCount}\n");
        $json = null;
        $kills = null;
        if ($loopCount >= 50) {
            break;
        }
        if ($updateType == "cleanRefresh") {
            if ($loopCount == 0) {
                $fromHour = date('YmdHi', strtotime($fullRefresh));
            } else {
                $fromHour = date('YmdHi', strtotime($killTimeFormatted));
            }
        } else {
            $fromHour = date('YmdHi', strtotime($fromHour . '+' . $loopCount * 5 . ' minutes'));
        }
          $url = "https://zkillboard.com/api/kills/w-space/no-items/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
          //$url = "https://zkillboard.com/api/kills/no-items/no-attackers/w-space/limit/200/orderDirection/asc/afterKillID/".$lastKill."/";
        if (!$json = file_get_contents_retry($url)) {
            $error = error_get_last();
            printf("HTTP request failed. Error was: " . $error['message'] . "\n Exiting");
            break;
        }
          $kills = json_decode($json, true);
        if (empty($kills)) {
            $url = "https://zkillboard.com/api/kills/w-space/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
            printf("1st attempt at hour kills failed, retrying...\n");
            $kills = json_decode(file_get_contents_retry($url), true);
            if (empty($kills)) {
                $url = "https://zkillboard.com/api/kills/w-space/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
                printf("2nd attempt at hour kills failed, retrying...\n");
                $kills = json_decode(file_get_contents_retry($url), true);
                if (empty($kills) && $loopCount == 0) {
                    $lastKill += 1;
                    $url = "https://zkillboard.com/api/kills/w-space/no-items/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
                    $kills = json_decode(file_get_contents_retry($url), true);
                    printf("Incrementing lastKill for last attempt...\n");
                    if (empty($kills)) {
                        printf("zKill Failure from {$killID}, finishing hourKills...\n");
                        return 1;
                    }
                } else {
                    printf("All attempts failed from {$killID}, finishing hourKills...\n");
                    break;
                }
            }
        }
        if (!empty($kills)) {
            printf("Kills found, analysing...\n");

            foreach ($kills as $kill) {
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
                $killTimeFormatted = date_format(date_create($killTime), "YmdHi");
                $typeID = $kill["victim"]["shipTypeID"];

                $victimName = $kill["victim"]["characterName"];
                $victimID = $kill["victim"]["characterID"];
                $victimCorpName = $kill["victim"]["corporationName"];
                $victimCorpID = $kill["victim"]["corporationID"];
                $victimAllianceName = $kill["victim"]["allianceName"];
                $victimAllianceID = $kill["victim"]["allianceID"];

                $attackersArray = array_key_exists("attackers", $kill) ? $kill["attackers"] : null;
                $involved = count($attackersArray);

                if (in_array($killID, $killSeen)) {
                    printf("xxx Seen kill - $killID xxx\n");
                    continue;
                }

                printf("*** New Kill - $killID ***\n");
                //printf("\n{$systemID} - {$typeID} - {$iskValue} ISK - {$involved} Involved\n");

                $typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$typeID}";
                $shipResult = $conn->query($typeQuery);
                if ($shipResult->num_rows > 0) {
                    while ($shipRow = $shipResult->fetch_assoc()) {
                        $shipName = $shipRow["name"];
                        $shipType = $shipRow["type"];
                        $shipTech = $shipRow["tech"];
                        $shipRace = $shipRow["race"];
                        //printf("{$shipName} - {$shipType} - {$shipTech} - {$shipRace}\n");
                    }
                } else {
                    printf("--- Unknown TypeID - {$typeID} ---\n");
                    continue;
                }

                $systemQuery = "SELECT class FROM whsystems WHERE solarSystemID = {$systemID}";
                $systemResult = $conn->query($systemQuery);
                if ($systemResult->num_rows > 0) {
                    while ($systemRow = $systemResult->fetch_assoc()) {
                        $class = $systemRow["class"];
                    }
                }
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

                $tz = null;
                // Check TZ 00.00 - 0600 us, 0600 - 1200 auz, 1200-1800 rus, 1800 - 0000 eu
                $killTimeTZ = date_format(date_create($killTime), "H");
                // Start with US
                if ($killTimeTZ >= "00" && $killTimeTZ < "06") {
                    printf("**** US TZ KILL ****\n");
                    $tz = "us";
                }
                // Now AU
                if ($killTimeTZ >= "06" && $killTimeTZ < "12") {
                    printf("**** AU TZ KILL ****\n");
                    $tz = "au";
                }
                // Now RU
                if ($killTimeTZ >= "12" && $killTimeTZ < "18") {
                    printf("**** RU TZ KILL ****\n");
                    $tz = "ru";
                }
                // Now EU
                if ($killTimeTZ >= "18" && $killTimeTZ < "24") {
                    printf("**** EU TZ KILL ****\n");
                    $tz = "eu";
                }

                //Record attackers
                foreach ($attackersArray as $attacker) {
                    $attackerShipID = $attacker["shipTypeID"];

                    $typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$attackerShipID}";
                    $shipResult = $conn->query($typeQuery);
                    if (!$shipResult === false && $shipResult->num_rows > 0) {
                        while ($shipRow = $shipResult->fetch_assoc()) {
                            $attackerShipName = $shipRow["name"];
                            $attackerShipType = $shipRow["type"];
                            $attackerShipTech = $shipRow["tech"];
                            $attackerShipRace = $shipRow["race"];
                            //printf("Ship: {$shipName} - Class: {$shipType} - Tech: {$shipTech} - Race: {$shipRace}\n");
                        }
                    } else {
                        $attackerShipType = null;
                        $attackerShipTech = null;
                        $attackerShipRace = null;
                    }
                    $attackerWeaponType = $attacker["weaponTypeID"];
                    $attackerCorpName = $attacker["corporationName"];
                    $attackerCorpID = $attacker["corporationID"];
                    $attackerAllyName = $attacker["allianceName"];
                    $attackerAllyID = $attacker["allianceID"];

                    // See if we have stats for this corp/alliance, if not add trader_ht_trendmode$typeQuery = "SELECT * FROM shiptypes WHERE typeID = {$attackerShipID}";

                    if ($attackerAllyID != 0) {
                        $entityID = $attackerAllyID;
                        $entityName = $attackerAllyName;
                        $entityType = "Alliance";
                    } else {
                        $entityID = $attackerCorpID;
                        $entityName = $attackerCorpName;
                        $entityType = "Corporation";
                    }

                    // Check if it's a Citadel, which doesn't have an alliance...
                    if ($attackerShipID == 35833 || $attackerShipID == 35832 || $attackerShipID == 35834 || $attackerShipID == 40340) {
                        printf("Citadel Detected, trying to get alliance of corp {$entityID}\n");
                        $corpURL = "https://zkillboard.com/corporation/{$entityID}/";
                        $corpWhoURL = "http://evewho.com/api.php?type=corporation&id={$entityID}";
                        $corpDetails = json_decode(file_get_contents_retry($corpWhoURL), true);
                        if (!empty($corpDetails)) {
                            $entityID = $corpDetails["info"]["alliance_id"];
                            printf("Citadel Corp is in alliance, setting entityID to {$entityID}\n");
                        }

                        // try {
                        //   $html = file_get_html($corpURL);
                        // } catch (Exception $e) {
                        //   printf("Error Retrieving HTML data from {$corpURL}\n");
                        // }
                        //
                        // if($html) {
                        //   $table = $html->find('table', 1);
                        //   if($table) {
                        //     $rows = $table->find('tr', 2);
                        //     $entityID = filter_var($rows->children(1)->children(0)->href, FILTER_SANITIZE_NUMBER_INT);
                        //     printf("Citadel Detected - Alliance {$entityID}\n");
                        //     }
                        //  }
                    }

                    if (!$entityID == null || !$entityID == 0) {
                        printf("Pushing Entity {$entityID} onto Array \n");
                        array_push($entitiesInvolved, $entityID);
                    }
                    $entityQuery = "SELECT * FROM entitystats{$tz} WHERE entityID = {$entityID}";
                    $entityResult = $conn->query($entityQuery);
                    if (!$entityResult === false && $entityResult->num_rows == 0) {
                        printf("Entity {$entityID} not in DB, adding...\n");
                        $entityQuery = "INSERT INTO entitystats{$tz} (entityID, entityName, entityType) VALUES ({$entityID}, '{$entityName}', '{$entityType}')";
                        $entityResult = $conn->query($entityQuery);
                    } else {
                        printf("Entity {$entityID} already exists in DB\n");
                    }

                    // First get and increment the entitie's use of this shiptype
                    if ($attackerShipTech == "T1") {
                        //Frigs
                        if ($attackerShipType == "Mining Frigate" || $attackerShipType == "Frigates" || $attackerShipType == "Mining Frigate" || $attackerShipType == "Rookie Ships") {
                            $frigQuery = "UPDATE entitystats{$tz} SET t1FrigUse = t1FrigUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($frigQuery);
                            //printf("Query: {$frigQuery}\n");
                        }
                        if ($attackerShipType == "Faction Frigates") {
                            $frigQuery = "UPDATE entitystats{$tz} SET factionFrigUse = factionFrigUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($frigQuery);
                            //printf("Query: {$frigQuery}\n");
                        }
                        //Destroyers
                        if ($attackerShipType == "Destroyers") {
                            $destroyerQuery = "UPDATE entitystats{$tz} SET t1DestroyerUse = t1DestroyerUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($destroyerQuery);
                            //printf("Query: {$destroyerQuery}\n");
                        }
                        //Cruisers
                        if ($attackerShipType == "Cruisers") {
                            $cruiserQuery = "UPDATE entitystats{$tz} SET t1CruiserUse = t1CruiserUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($cruiserQuery);
                            // printf("Query: {$cruiserQuery}\n");
                        }
                        if ($attackerShipType == "Faction Cruisers") {
                            $cruiserQuery = "UPDATE entitystats{$tz} SET factionCruiserUse = factionCruiserUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($cruiserQuery);
                            // printf("Query: {$cruiserQuery}\n");
                        }
                        //BCs
                        if ($attackerShipType == "Battlecruisers" || $attackerShipType == "Battlecruisers (Attack)" || $attackerShipType == "Special Edition Battlecruiser") {
                            $bcQuery = "UPDATE entitystats{$tz} SET t1BCUse = t1BCUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($bcQuery);
                            // printf("Query: {$bcQuery}\n");
                        }
                        if ($attackerShipType == "Faction Battlecruisers") {
                            $bcQuery = "UPDATE entitystats{$tz} SET factionBCUse = factionBCUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($bcQuery);
                            // printf("Query: {$bcQuery}\n");
                        }
                        //BSs
                        if ($attackerShipType == "Battleships") {
                            $bsQuery = "UPDATE entitystats{$tz} SET t1BattleshipUse = t1BattleshipUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($bsQuery);
                            // printf("Query: {$bsQuery}\n");
                        }
                        if ($attackerShipType == "Faction Battleships") {
                            $bsQuery = "UPDATE entitystats{$tz} SET factionBattleshipUse = factionBattleshipUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($bsQuery);
                            // printf("Query: {$bsQuery}\n");
                        }
                        //Caps
                        //Carriers
                        if ($attackerShipType == "Carriers") {
                            $carrierQuery = "UPDATE entitystats{$tz} SET carrierUse = carrierUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($carrierQuery);
                            // printf("Query: {$carrierQuery}\n");
                            if ($attackerShipName == "Archon") {
                                $carrierQuery = "UPDATE entitystats{$tz} SET archonUse = archonUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($carrierQuery);
                                // printf("Query: {$carrierQuery}\n");
                            }
                            if ($attackerShipName == "Nidhoggur") {
                                $carrierQuery = "UPDATE entitystats{$tz} SET nidUse = nidUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($carrierQuery);
                                // printf("Query: {$carrierQuery}\n");
                            }
                            if ($attackerShipName == "Chimera") {
                                $carrierQuery = "UPDATE entitystats{$tz} SET chimeraUse = chimeraUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($carrierQuery);
                                // printf("Query: {$carrierQuery}\n");
                            }
                            if ($attackerShipName == "Thanatos") {
                                $carrierQuery = "UPDATE entitystats{$tz} SET thanatosUse = thanatosUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($carrierQuery);
                                // printf("Query: {$carrierQuery}\n");
                            }
                        }
                        //Dreadnoughts
                        if ($attackerShipType == "Dreadnoughts") {
                            $dreadQuery = "UPDATE entitystats{$tz} SET dreadUse = dreadUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($dreadQuery);
                            // printf("Query: {$dreadQuery}\n");
                            if ($attackerShipName == "Naglfar") {
                                $dreadQuery = "UPDATE entitystats{$tz} SET nagUse = nagUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($dreadQuery);
                                // printf("Query: {$dreadQuery}\n");
                            }
                            if ($attackerShipName == "Moros") {
                                $dreadQuery = "UPDATE entitystats{$tz} SET morosUse = morosUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($dreadQuery);
                                // printf("Query: {$dreadQuery}\n");
                            }
                            if ($attackerShipName == "Phoenix") {
                                $dreadQuery = "UPDATE entitystats{$tz} SET phoenixUse = phoenixUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($dreadQuery);
                                // printf("Query: {$dreadQuery}\n");
                            }
                            if ($attackerShipName == "Revelation") {
                                $dreadQuery = "UPDATE entitystats{$tz} SET revUse = revUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($dreadQuery);
                                // printf("Query: {$dreadQuery}\n");
                            }
                        }
                        //Force Auxiliaries
                        if ($attackerShipType == "Force Auxiliary") {
                            $faxQuery = "UPDATE entitystats{$tz} SET dreadUse = faxUse + 1 WHERE faxUse = {$entityID}";
                            $conn->query($faxQuery);
                            // printf("Query: {$faxQuery}\n");
                            if ($attackerShipName == "Apostle") {
                                $faxQuery = "UPDATE entitystats{$tz} SET apostleUse = apostleUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($faxQuery);
                                // printf("Query: {$faxQuery}\n");
                            }
                            if ($attackerShipName == "Lif") {
                                $faxQuery = "UPDATE entitystats{$tz} SET lifUse = lifUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($faxQuery);
                                // printf("Query: {$faxQuery}\n");
                            }
                            if ($attackerShipName == "Minokawa") {
                                $faxQuery = "UPDATE entitystats{$tz} SET minokawaUse = minokawaUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($faxQuery);
                                // printf("Query: {$faxQuery}\n");
                            }
                            if ($attackerShipName == "Ninazu") {
                                $faxQuery = "UPDATE entitystats{$tz} SET ninazuUse = ninazuUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($faxQuery);
                                // printf("Query: {$faxQuery}\n");
                            }
                        }
                    }

                    if ($attackerShipTech == "T2") {
                        if ($attackerShipType == "Assault Frigates" || $attackerShipType == "Covert Ops" || $attackerShipType == "Electronic Attack Frigates" ||
                         $attackerShipType == "Interceptors" || $attackerShipType == "Logistics Frigate" || $attackerShipType == "Stealth Bombers") {
                             $t2FrigQuery = "UPDATE entitystats{$tz} SET t2FrigUse = t2FrigUse + 1 WHERE entityID = {$entityID}";
                             $conn->query($t2FrigQuery);
                            //  printf("Query: {$t2FrigQuery}\n");
                            if ($attackerShipType == "Logistics Frigate") {
                                $frigLogiQuery = "UPDATE entitystats{$tz} SET frigLogiUse = frigLogiUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($frigLogiQuery);
                             //  printf("Query: {$frigLogiQuery}\n");
                            }
                        }
                        if ($attackerShipType == "Interdictors" || $attackerShipType == "	Command Destroyers") {
                            $t2DesQuery = "UPDATE entitystats{$tz} SET t2DestroyerUse = t2DestroyerUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t2DesQuery);
                            // printf("Query: {$t2DesQuery}\n");
                        }
                        if ($attackerShipType == "Force Recon" || $attackerShipType == "Heavy Assault Cruisers" || $attackerShipType == "Heavy Interdictors" ||
                            $attackerShipType == "Logistics Cruisers" || $attackerShipType == "Recon Ships" ) {
                            $t2CruiserQuery = "UPDATE entitystats{$tz} SET t2CruiserUse = t2CruiserUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t2CruiserQuery);
                            // printf("Query: {$t2CruiserQuery}\n");
                            if ($attackerShipType == "Logistics Cruisers") {
                                $t2CruiserQuery = "UPDATE entitystats{$tz} SET cruiserLogiUse = cruiserLogiUse + 1 WHERE entityID = {$entityID}";
                                $conn->query($t2CruiserQuery);
                                // printf("Query: {$t2CruiserQuery}\n");
                            }
                        }
                        if ($attackerShipType == "Command Ships") {
                            $t2BCQuery = "UPDATE entitystats{$tz} SET t2BCUse = t2BCUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t2BCQuery);
                            // printf("Query: {$t2BCQuery}\n");
                        }
                        if ($attackerShipType == "Marauders" || $attackerShipType == "Black Ops") {
                            $t2BSQuery = "UPDATE entitystats{$tz} SET t2BattleshipUse = t2BattleshipUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t2BSQuery);
                            // printf("Query: {$t2BSQuery}\n");
                        }
                    }

                    if ($attackerShipTech == "T3") {
                        if ($attackerShipType == "Tactical Destroyers") {
                            $t3DesQuery = "UPDATE entitystats{$tz} SET t3DestroyerUse = t3DestroyerUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t3DesQuery);
                            // printf("Query: {$t3DesQuery}\n");
                        }
                        if ($attackerShipType == "Strategic Cruisers") {
                            $t3CruiserQuery = "UPDATE entitystats{$tz} SET t3CruiserUse = t3CruiserUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t3CruiserQuery);
                            // printf("Query: {$t3CruiserQuery}\n");
                        }
                    }

                    // Neuts
                    if ($attackerWeaponType == "533" || $attackerWeaponType == "4471" || $attackerWeaponType == "4473" || $attackerWeaponType == "4475" || $attackerWeaponType == "4477" ||
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
                        //  printf("Query: {$neutsQuery}\n");
                    }
                    //Jams
                    if ($attackerWeaponType == "28729" || $attackerWeaponType == "28731" || $attackerWeaponType == "28733" || $attackerWeaponType == "28735" || $attackerWeaponType == "28737" ||
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
                        //  printf("Query: {$jamsQuery}\n");
                    }
                    //Damps
                    if ($attackerWeaponType == "1968" || $attackerWeaponType == "1969" || $attackerWeaponType == "5299" || $attackerWeaponType == "5300" || $attackerWeaponType == "5301" ||
                     $attackerWeaponType == "5302" || $attackerWeaponType == "22943" || $attackerWeaponType == "22945" || $attackerWeaponType == "32413") {
                         $dampQuery = "UPDATE entitystats{$tz} SET	dampsUse = dampsUse + 1 WHERE entityID = {$entityID}";
                         $conn->query($dampQuery);
                        //  printf("Query: {$dampQuery}\n");
                    }
                }

                $entitiesCount = array_count_values($entitiesInvolved);
                foreach ($entitiesCount as $entity => $count) {
                    // Check Avg Fleet Size
                    $query = "SELECT avgFleetSize, whKills, largestFleetSize FROM entitystats{$tz} WHERE entityID = {$entity}";
                    $result = $conn->query($query);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $whKills = $row["whKills"];
                        $avgFleet = $row["avgFleetSize"];
                        $largestFleet = $row["largestFleetSize"];
                    } else {
                        $whKills = 1;
                        $avgFleet = 1;
                        $largestFleet = 0;
                    }
                    $oldTotal = $avgFleet * $whKills;
                    $newAvg = ($oldTotal + $count) / ($whKills + 1);
                    $updateQuery = "UPDATE entitystats{$tz} SET avgFleetSize = {$newAvg} WHERE entityID = {$entity}";
                    $conn->query($updateQuery);
                    // printf("Query: {$updateQuery}\n");
                    // Update
                    $largestFleet > $count ? $largestFleet : $largestFleet = $count;
                    $updateQuery = "UPDATE entitystats{$tz} SET largestFleetSize = {$largestFleet} WHERE entityID = {$entity}";
                    $conn->query($updateQuery);
                    // printf("Query: {$updateQuery}\n");
                    if ($class == 1) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c1Kills = c1Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 2) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c2Kills = c2Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 3) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c3Kills = c3Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 4) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c4Kills = c4Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 5) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c5Kills = c5Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 6) {
                        $classQuery = "UPDATE entitystats{$tz} SET c6Kills = c6Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 7) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c7Kills = c7Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 8) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c8Kills = c8Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }
                    if ($class == 9) {
                        $classQuery = "UPDATE entitystats{$tz} SET	c9Kills = c9Kills + 1 WHERE entityID = {$entity}";
                        $conn->query($classQuery);
                        // printf("Query: {$classQuery}\n");
                        $whKillsQuery = "UPDATE entitystats{$tz} SET	whkills = whkills + 1 WHERE entityID = {$entity}";
                        $conn->query($whKillsQuery);
                        // printf("Query: {$whKillsQuery}\n");
                    }

                    // Last Seen Kill
                    $lastKillQuery = "UPDATE entitystats{$tz} SET lastKill = {$killID} WHERE entityID = {$entity}";
                    $conn->query($lastKillQuery);
                    // printf("Query: {$lastKillQuery}\n");
                }

                // Repeat for Victim
                // ---------------------------------------------------------------------------
                // Now Count the Victim
                $entityID = $victimCorpID;
                if ($victimAllianceID != 0) {
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
                if (!$entityResult === false && $entityResult->num_rows == 0) {
                    printf("Entity {$entityID} not in DB, adding...\n");
                    $entityQuery = "INSERT INTO entitystats{$tz} (entityID, entityName, entityType) VALUES ({$entityID}, '{$entityName}', '{$entityType}')";
                    $entityResult = $conn->query($entityQuery);
                    printf("Query: {$entityQuery}\n");
                } else {
                    printf("Entity {$entityID} already exists in DB\n");
                }

                // First get and increment the entitie's use of this shiptype
                if ($shipTech == "T1") {
                    //Frigs
                    if ($shipType == "Mining Frigate" || $shipType == "Frigates" || $shipType == "Mining Frigate" || $shipType == "Rookie Ships") {
                        $frigQuery = "UPDATE entitystats{$tz} SET t1FrigUse = t1FrigUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($frigQuery);
                        // printf("Query: {$frigQuery}\n");
                    }
                    if ($shipType == "Faction Frigates") {
                        $frigQuery = "UPDATE entitystats{$tz} SET factionFrigUse = factionFrigUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($frigQuery);
                        //printf("Query: {$frigQuery}\n");
                    }
                    //Destroyers
                    if ($shipType == "Destroyers") {
                        $destroyerQuery = "UPDATE entitystats{$tz} SET t1DestroyerUse = t1DestroyerUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($destroyerQuery);
                        //printf("Query: {$destroyerQuery}\n");
                    }
                    //Cruisers
                    if ($shipType == "Cruisers") {
                        $cruiserQuery = "UPDATE entitystats{$tz} SET t1CruiserUse = t1CruiserUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($cruiserQuery);
                        //printf("Query: {$cruiserQuery}\n");
                    }
                    if ($shipType == "Faction Cruisers") {
                        $cruiserQuery = "UPDATE entitystats{$tz} SET factionCruiserUse = factionCruiserUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($cruiserQuery);
                        //printf("Query: {$cruiserQuery}\n");
                    }
                    //BCs
                    if ($shipType == "Battlecruisers" || $shipType == "Battlecruisers (Attack)" || $shipType == "Special Edition Battlecruiser") {
                        $bcQuery = "UPDATE entitystats{$tz} SET t1BCUse = t1BCUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($bcQuery);
                        //printf("Query: {$bcQuery}\n");
                    }
                    if ($shipType == "Faction Battlecruisers") {
                        $bcQuery = "UPDATE entitystats{$tz} SET factionBCUse = factionBCUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($bcQuery);
                        //printf("Query: {$bcQuery}\n");
                    }
                    //BSs
                    if ($shipType == "Battleships") {
                        $bsQuery = "UPDATE entitystats{$tz} SET t1BattleshipUse = t1BattleshipUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($bsQuery);
                        //printf("Query: {$bsQuery}\n");
                    }
                    if ($shipType == "Faction Battleships") {
                        $bsQuery = "UPDATE entitystats{$tz} SET factionBattleshipUse = factionBattleshipUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($bsQuery);
                        //printf("Query: {$bsQuery}\n");
                    }
                    //Caps
                    //Carriers
                    if ($shipType == "Carriers") {
                        $carrierQuery = "UPDATE entitystats{$tz} SET carrierUse = carrierUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($carrierQuery);
                        //printf("Query: {$carrierQuery}\n");
                        if ($attackerShipName == "Archon") {
                            $carrierQuery = "UPDATE entitystats{$tz} SET archonUse = archonUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($carrierQuery);
                            //printf("Query: {$carrierQuery}\n");
                        }
                        if ($attackerShipName == "Nidhoggur") {
                            $carrierQuery = "UPDATE entitystats{$tz} SET nidUse = nidUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($carrierQuery);
                            //printf("Query: {$carrierQuery}\n");
                        }
                        if ($attackerShipName == "Chimera") {
                            $carrierQuery = "UPDATE entitystats{$tz} SET chimeraUse = chimeraUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($carrierQuery);
                            //printf("Query: {$carrierQuery}\n");
                        }
                        if ($attackerShipName == "Thanatos") {
                            $carrierQuery = "UPDATE entitystats{$tz} SET thanatosUse = thanatosUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($carrierQuery);
                            //printf("Query: {$carrierQuery}\n");
                        }
                    }
                    //Dreadnoughts
                    if ($shipType == "Dreadnoughts") {
                        $dreadQuery = "UPDATE entitystats{$tz} SET dreadUse = dreadUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($dreadQuery);
                        //printf("Query: {$dreadQuery}\n");
                        if ($attackerShipName == "Naglfar") {
                            $dreadQuery = "UPDATE entitystats{$tz} SET nagUse = nagUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($dreadQuery);
                            //printf("Query: {$dreadQuery}\n");
                        }
                        if ($attackerShipName == "Moros") {
                            $dreadQuery = "UPDATE entitystats{$tz} SET morosUse = morosUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($dreadQuery);
                            //printf("Query: {$dreadQuery}\n");
                        }
                        if ($attackerShipName == "Phoenix") {
                            $dreadQuery = "UPDATE entitystats{$tz} SET phoenixUse = phoenixUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($dreadQuery);
                            //printf("Query: {$dreadQuery}\n");
                        }
                        if ($attackerShipName == "Revelation") {
                            $dreadQuery = "UPDATE entitystats{$tz} SET revUse = revUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($dreadQuery);
                            //printf("Query: {$dreadQuery}\n");
                        }
                    }
                    //Force Auxiliaries
                    if ($shipType == "Force Auxiliary") {
                        $faxQuery = "UPDATE entitystats{$tz} SET dreadUse = faxUse + 1 WHERE faxUse = {$entityID}";
                        $conn->query($faxQuery);
                        //printf("Query: {$faxQuery}\n");
                        if ($attackerShipName == "Apostle") {
                            $faxQuery = "UPDATE entitystats{$tz} SET apostleUse = apostleUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($faxQuery);
                            //printf("Query: {$faxQuery}\n");
                        }
                        if ($attackerShipName == "Lif") {
                            $faxQuery = "UPDATE entitystats{$tz} SET lifUse = lifUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($faxQuery);
                            //printf("Query: {$faxQuery}\n");
                        }
                        if ($attackerShipName == "Minokawa") {
                            $faxQuery = "UPDATE entitystats{$tz} SET minokawaUse = minokawaUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($faxQuery);
                            //printf("Query: {$faxQuery}\n");
                        }
                        if ($attackerShipName == "Ninazu") {
                            $faxQuery = "UPDATE entitystats{$tz} SET ninazuUse = ninazuUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($faxQuery);
                            //printf("Query: {$faxQuery}\n");
                        }
                    }
                }

                if ($shipTech == "T2") {
                    if ($shipType == "Assault Frigates" || $shipType == "Covert Ops" || $shipType == "Electronic Attack Frigates" ||
                     $shipType == "Interceptors" || $shipType == "Logistics Frigate" || $shipType == "Stealth Bombers") {
                         $t2FrigQuery = "UPDATE entitystats{$tz} SET t2FrigUse = t2FrigUse + 1 WHERE entityID = {$entityID}";
                         $conn->query($t2FrigQuery);
                         //printf("Query: {$t2FrigQuery}\n");
                        if ($shipType == "Logistics Frigate") {
                            $frigLogiQuery = "UPDATE entitystats{$tz} SET frigLogiUse = frigLogiUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($frigLogiQuery);
                          //printf("Query: {$frigLogiQuery}\n");
                        }
                    }
                    if ($shipType == "Interdictors" || $shipType == "	Command Destroyers") {
                        $t2DesQuery = "UPDATE entitystats{$tz} SET t2DestroyerUse = t2DestroyerUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t2DesQuery);
                        //printf("Query: {$t2DesQuery}\n");
                    }
                    if ($shipType == "Force Recon" || $shipType == "Heavy Assault Cruisers" || $shipType == "Heavy Interdictors" ||
                        $shipType == "Logistics Cruisers" || $shipType == "Recon Ships" ) {
                        $t2CruiserQuery = "UPDATE entitystats{$tz} SET t2CruiserUse = t2CruiserUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t2CruiserQuery);
                        //printf("Query: {$t2CruiserQuery}\n");
                        if ($shipType == "Logistics Cruisers") {
                            $t2CruiserQuery = "UPDATE entitystats{$tz} SET cruiserLogiUse = cruiserLogiUse + 1 WHERE entityID = {$entityID}";
                            $conn->query($t2CruiserQuery);
                            //printf("Query: {$t2CruiserQuery}\n");
                        }
                    }
                    if ($shipType == "Command Ships") {
                        $t2BCQuery = "UPDATE entitystats{$tz} SET t2BCUse = t2BCUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t2BCQuery);
                        //printf("Query: {$t2BCQuery}\n");
                    }
                    if ($shipType == "Marauders" || $shipType == "Black Ops") {
                        $t2BSQuery = "UPDATE entitystats{$tz} SET t2BattleshipUse = t2BattleshipUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t2BSQuery);
                        //printf("Query: {$t2BSQuery}\n");
                    }
                }

                if ($shipTech == "T3") {
                    if ($shipType == "Tactical Destroyers") {
                        $t3DesQuery = "UPDATE entitystats{$tz} SET t3DestroyerUse = t3DestroyerUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t3DesQuery);
                        //printf("Query: {$t3DesQuery}\n");
                    }
                    if ($shipType == "Strategic Cruisers") {
                        $t3CruiserQuery = "UPDATE entitystats{$tz} SET t3CruiserUse = t3CruiserUse + 1 WHERE entityID = {$entityID}";
                        $conn->query($t3CruiserQuery);
                        //printf("Query: {$t3CruiserQuery}\n");
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
                //     $dampQuery = "UPDATE entitystats{$tz} SET  dampsUse = dampsUse + 1 WHERE entityID = {$entityID}";
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
                //   $classQuery = "UPDATE entitystats{$tz} SET   c1Kills = c1Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 2) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c2Kills = c2Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 3) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c3Kills = c3Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 4) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c4Kills = c4Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 5) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c5Kills = c5Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 6) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c6Kills = c6Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 7) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c7Kills = c7Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 8) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c8Kills = c8Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                // if($class == 9) {
                //   $classQuery = "UPDATE entitystats{$tz} SET   c9Kills = c9Kills + 1 WHERE entityID = {$entity}";
                //   $conn->query($classQuery);
                //   printf("Query: {$classQuery}\n");
                //   $whKillsQuery = "UPDATE entitystats{$tz} SET whkills = whkills + 1 WHERE entityID = {$entity}";
                //   $conn->query($whKillsQuery);
                //   printf("Query: {$whKillsQuery}\n");
                // }
                //
                // // Last Seen Kill
                // $lastKillQuery = "UPDATE entitystats{$tz} SET lastKill = {$killID} WHERE entityID = {$entity}";
                // $conn->query($lastKillQuery);
                // printf("Query: {$lastKillQuery}\n");

                // Now do daily stats etc.

                if ($involved > 4) {
                    $hourTime = date_format(date_create($killTime), "YmdH00");
                    $relatedURL = "https://zkillboard.com/related/{$systemID}/{$hourTime}/";

                    try {
                        $html = file_get_html($relatedURL);
                    } catch (Exception $e) {
                        printf("Error Retrieving HTML data from {$relatedURL}\n");
                    }

                    if ($html) {
                        $table = $html->find('table', 0);
                        if ($table) {
                            $rows = $table->find('tr');
                            $teamA = filter_var($rows[0]->children(0)->plaintext, FILTER_SANITIZE_NUMBER_INT);
                            $teamB = filter_var($rows[0]->children(1)->plaintext, FILTER_SANITIZE_NUMBER_INT);
                            //printf("Team A {$teamA} - Team B {$teamB}\n");
                            if ($teamA > 4 && $teamB > 4) {
                                printf("FLEET kill with {$teamA} Team A vs {$teamB} Team B!\n");
                                $hourKills["C{$class}"]["fleetKillsHour"] += 1;
                                $fleetQuery = "UPDATE totalkills SET fleetKillsTotal = fleetKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($fleetQuery);
                                $fleetQueryDaily = "UPDATE dailykills SET fleetKillsTotal = fleetKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($fleetQueryDaily);
                                $fleetQueryWeekly = "UPDATE weeklykills SET fleetKillsTotal = fleetKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($fleetQueryWeekly);
                                $fleetQueryMonthly = "UPDATE monthlykills SET fleetKillsTotal = fleetKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($fleetQueryWeekly);
                                //printf("{$fleetQuery}\n");
                            } else {
                                //printf("SMALL kill with {$teamA} Team A vs {$teamB} Team B!\n");
                                $hourKills["C{$class}"]["smallKillsHour"] += 1;
                                $smallQuery = "UPDATE totalkills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($smallQuery);
                                $smallQueryDaily = "UPDATE dailykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($smallQueryDaily);
                                $smallQueryWeekly = "UPDATE weeklykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($smallQueryWeekly);
                                $smallQueryMonthly = "UPDATE monthlykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                                $conn->query($smallQueryMonthly);
                                //printf("{$smallQuery}\n");
                            }
                        } else {
                            //printf("--HTML PARSE ERROR-- SMALL kill with {$involved} involved!\n");
                            $hourKills["C{$class}"]["smallKillsHour"] += 1;
                            $smallQuery = "UPDATE totalkills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                            $conn->query($smallQuery);
                            $smallQueryDaily = "UPDATE dailykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                            $conn->query($smallQueryDaily);
                            $smallQueryWeekly = "UPDATE weeklykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                            $conn->query($smallQueryWeekly);
                            $smallQueryMonthly = "UPDATE monthlykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                            $conn->query($smallQueryMonthly);
                            //printf("{$smallQuery}\n");
                        }
                    } else {
                        //printf("--NO HTML-- DEFAULT SMALL kill with {$involved} involved!\n");
                        $hourKills["C{$class}"]["smallKillsHour"] += 1;
                        $smallQuery = "UPDATE totalkills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($smallQuery);
                        $smallQueryDaily = "UPDATE dailykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($smallQueryDaily);
                        $smallQueryWeekly = "UPDATE weeklykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($smallQueryWeekly);
                        $smallQueryMonthly = "UPDATE monthlykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($smallQueryMonthly);
                        //printf("{$smallQuery}\n");
                    }
                } else {
                    //printf("SMALL kill with < 5 involved\n");
                    $hourKills["C{$class}"]["smallKillsHour"] += 1;
                    $smallQuery = "UPDATE totalkills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($smallQuery);
                    $smallQueryDaily = "UPDATE dailykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($smallQueryDaily);
                    $smallQueryWeekly = "UPDATE weeklykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($smallQueryWeekly);
                    $smallQueryMonthly = "UPDATE monthlykills SET smallKillsTotal = smallKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($smallQueryMonthly);
                    //printf("{$smallQuery}\n");
                }

                if ($iskValue > 0) {
                    $hourKills["C{$class}"]["hourISK"] += $iskValue;
                    $iskQuery = "UPDATE totalkills SET totalISK = totalISK + {$iskValue} WHERE class = {$class}";
                    $conn->query($iskQuery);
                    $iskQueryDaily = "UPDATE dailykills SET totalISK = totalISK + {$iskValue} WHERE class = {$class}";
                    $conn->query($iskQueryDaily);
                    $iskQueryWeekly = "UPDATE weeklykills SET totalISK = totalISK + {$iskValue} WHERE class = {$class}";
                    $conn->query($iskQueryWeekly);
                    $iskQueryMonthly = "UPDATE monthlykills SET totalISK = totalISK + {$iskValue} WHERE class = {$class}";
                    $conn->query($iskQueryMonthly);
                    //printf("{$iskQuery}\n");
                }

                if ($shipType == "Structure") {
                    $hourKills["C{$class}"]["structureKillsHour"] += 1;
                    $structQuery = "UPDATE totalkills SET structureKillsTotal = structureKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($structQuery);
                    $structQueryDaily = "UPDATE dailykills SET structureKillsTotal = structureKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($structQueryDaily);
                    $structQueryWeelkly = "UPDATE weeklykills SET structureKillsTotal = structureKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($structQueryWeelkly);
                    $structQueryMonthly = "UPDATE monthlykills SET structureKillsTotal = structureKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($structQueryMonthly);
                    //printf("{$structQuery}\n");
                }

                if ($shipType == "Citadel") {
                    $hourKills["C{$class}"]["citadelKillsHour"] += 1;
                    $citadelQuery = "UPDATE totalkills SET citadelKillsTotal = citadelKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($citadelQuery);
                    $citadelQueryDaily = "UPDATE dailykills SET citadelKillsTotal = citadelKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($citadelQueryDaily);
                    $citadelQueryWeekly = "UPDATE weeklykills SET citadelKillsTotal = citadelKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($citadelQueryWeekly);
                    $citadelQueryMonthly = "UPDATE monthlykills SET citadelKillsTotal = citadelKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($citadelQueryMonthly);
                    //printf("{$citadelQuery}\n");
                }

                if ($shipType == "Capsule" || $shipType == "Capsule - Genolution 'Auroral' 197-variant") {
                    $hourKills["C{$class}"]["podKillsHour"] += 1;
                    $podQuery = "UPDATE totalkills SET podKillsTotal = podKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($podQuery);
                    $podQueryDaily = "UPDATE dailykills SET podKillsTotal = podKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($podQueryDaily);
                    $podQueryWeekly = "UPDATE weeklykills SET podKillsTotal = podKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($podQueryWeekly);
                    $podQueryMonthly = "UPDATE monthlykills SET podKillsTotal = podKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($podQueryMonthly);
                    //printf("{$podQuery}\n");
                }

                if ($shipTech == "T1" && $shipType != "Capsule" && $shipType != "Capsule - Genolution 'Auroral' 197-variant" && $shipType != "Structure" &&
                $shipType != "Citadel" && $shipType != "Dreadnoughts" && $shipType != "Force Auxiliary" && $shipType != "Carriers" && $shipType != "Capital Industrial Ships" &&
                $shipType != "Fighters") {
                    if ($shipRace == "Faction") {
                        $hourKills["C{$class}"]["factionKillsHour"] += 1;
                        $tech1Query = "UPDATE totalkills SET factionKillsTotal = factionKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1Query);
                        $tech1QueryDaily = "UPDATE dailykills SET factionKillsTotal = factionKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryDaily);
                        $tech1QueryWeekly = "UPDATE weeklykills SET factionKillsTotal = factionKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryWeekly);
                        $tech1QueryMonthly = "UPDATE monthlykills SET factionKillsTotal = factionKillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryMonthly);
                        //printf("{$tech1Query}\n");
                    } else {
                        $hourKills["C{$class}"]["t1KillsHour"] += 1;
                        $tech1Query = "UPDATE totalkills SET t1KillsTotal = t1KillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1Query);
                        $tech1QueryDaily = "UPDATE dailykills SET t1KillsTotal = t1KillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryDaily);
                        $tech1QueryWeekly = "UPDATE weeklykills SET t1KillsTotal = t1KillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryWeekly);
                        $tech1QueryMonthly = "UPDATE monthlykills SET t1KillsTotal = t1KillsTotal + 1 WHERE class = {$class}";
                        $conn->query($tech1QueryMonthly);
                        //printf("{$tech1Query}\n");
                    }
                }

                if ($shipTech == "T2") {
                    $hourKills["C{$class}"]["t2KillsHour"] += 1;
                    $tech2Query = "UPDATE totalkills SET t2KillsTotal = t2KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech2Query);
                    $tech2QueryDaily = "UPDATE dailykills SET t2KillsTotal = t2KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech2QueryDaily);
                    $tech2QueryWeekly = "UPDATE weeklykills SET t2KillsTotal = t2KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech2QueryWeekly);
                    $tech2QueryMonthly = "UPDATE monthlykills SET t2KillsTotal = t2KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech2QueryMonthly);
                    //printf("{$tech2Query}\n");
                }

                if ($shipTech == "T3") {
                    $hourKills["C{$class}"]["t3KillsHour"] += 1;
                    $tech3Query = "UPDATE totalkills SET t3KillsTotal = t3KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech3Query);
                    $tech3QueryDaily = "UPDATE dailykills SET t3KillsTotal = t3KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech3QueryDaily);
                    $tech3QueryWeekly = "UPDATE weeklykills SET t3KillsTotal = t3KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech3QueryWeekly);
                    $tech3QueryMonthly = "UPDATE monthlykills SET t3KillsTotal = t3KillsTotal + 1 WHERE class = {$class}";
                    $conn->query($tech3QueryMonthly);
                    //printf("{$tech3Query}\n");
                }

                if ($shipType == "Industrial Ships" || $shipType == "Transport Ships" || $shipType == "Mining Barges" || $shipType == "Exhumer Barges" || $shipType == "Mining Frigate") {
                    $hourKills["C{$class}"]["industrialKillsHour"] += 1;
                    $indyQuery = "UPDATE totalkills SET industrialKillsTotal = industrialKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($indyQuery);
                    $indyQueryDaily = "UPDATE dailykills SET industrialKillsTotal = industrialKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($indyQueryDaily);
                    $indyQueryWeekly = "UPDATE weeklykills SET industrialKillsTotal = industrialKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($indyQueryWeekly);
                    $indyQueryMonthly = "UPDATE monthlykills SET industrialKillsTotal = industrialKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($indyQueryMonthly);
                    //printf("{$indyQuery}\n");
                }

                if ($shipType == "Logistics Cruisers") {
                    $hourKills["C{$class}"]["logiKillsHour"] += 1;
                    $logiQuery = "UPDATE totalkills SET logiKillsTotal = logiKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($logiQuery);
                    $logiQueryDaily = "UPDATE dailykills SET logiKillsTotal = logiKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($logiQueryDaily);
                    $logiQueryWeekly = "UPDATE weeklykills SET logiKillsTotal = logiKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($logiQueryWeekly);
                    $logiQueryMonthly = "UPDATE monthlykills SET logiKillsTotal = logiKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($logiQueryMonthly);
                    //printf("{$logiQuery}\n");
                }

                if ($shipType == "Capital Industrial Ships" || $shipType == "Jump Freighters") {
                    $hourKills["C{$class}"]["capKillsHour"] += 1;
                    $capKillsTotalQuery = "UPDATE totalkills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQuery);
                    $capKillsTotalQueryDaily = "UPDATE dailykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryDaily);
                    $capKillsTotalQueryWeekly = "UPDATE weeklykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryWeekly);
                    $capKillsTotalQueryMonthly = "UPDATE monthlykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryMonthly);
                    //printf("{$capKillsTotalQuery}\n");
                }

                if ($shipType == "Force Auxiliary") {
                    $hourKills["C{$class}"]["forceAuxKillsHour"] += 1;
                    $hourKills["C{$class}"]["capKillsHour"] += 1;
                    $forceAuxKillsTotalQuery = "UPDATE totalkills SET forceAuxKillsTotal = forceAuxKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($forceAuxKillsTotalQuery);
                    $forceAuxKillsTotalQueryDaily = "UPDATE dailykills SET forceAuxKillsTotal = forceAuxKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($forceAuxKillsTotalQueryDaily);
                    $forceAuxKillsTotalQueryWeekly = "UPDATE weeklykills SET forceAuxKillsTotal = forceAuxKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($forceAuxKillsTotalQueryWeekly);
                    $forceAuxKillsTotalQueryMonthly = "UPDATE monthlykills SET forceAuxKillsTotal = forceAuxKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($forceAuxKillsTotalQueryMonthly);
                    //printf("{$forceAuxKillsTotalQuery}\n");
                    $capKillsTotalQuery = "UPDATE totalkills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQuery);
                    $capKillsTotalQueryDaily = "UPDATE dailykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryDaily);
                    $capKillsTotalQueryWeekly = "UPDATE weeklykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryWeekly);
                    $capKillsTotalQueryMonthly = "UPDATE monthlykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryMonthly);
                    //printf("{$capKillsTotalQuery}\n");
                }

                if ($shipType == "Dreadnoughts") {
                    $hourKills["C{$class}"]["dreadKillsHour"] += 1;
                    $hourKills["C{$class}"]["capKillsHour"] += 1;
                    $dreadKillsTotalQuery = "UPDATE totalkills SET dreadKillsTotal = dreadKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($dreadKillsTotalQuery);
                    $dreadKillsTotalQueryDaily = "UPDATE dailykills SET dreadKillsTotal = dreadKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($dreadKillsTotalQueryDaily);
                    $dreadKillsTotalQueryWeekly = "UPDATE weeklykills SET dreadKillsTotal = dreadKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($dreadKillsTotalQueryWeekly);
                    $dreadKillsTotalQueryMonthly = "UPDATE monthlykills SET dreadKillsTotal = dreadKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($dreadKillsTotalQueryMonthly);
                    //printf("{$dreadKillsTotalQuery}\n");
                    $capKillsTotalQuery2 = "UPDATE totalkills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQuery2);
                    $capKillsTotalQueryDaily2 = "UPDATE dailykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryDaily2);
                    $capKillsTotalQueryWeekly2 = "UPDATE weeklykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryWeekly2);
                    $capKillsTotalQueryMonthly2 = "UPDATE monthlykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryMonthly2);
                    //printf("{$capKillsTotalQuery2}\n");
                }

                if ($shipType == "Carriers") {
                    $hourKills["C{$class}"]["carrierKillsHour"] += 1;
                    $hourKills["C{$class}"]["capKillsHour"] += 1;
                    $carrierKillsTotalQuery = "UPDATE totalkills SET carrierKillsTotal = carrierKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($carrierKillsTotalQuery);
                    $carrierKillsTotalQueryDaily = "UPDATE dailykills SET carrierKillsTotal = carrierKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($carrierKillsTotalQueryDaily);
                    $carrierKillsTotalQueryWeekly = "UPDATE weeklykills SET carrierKillsTotal = carrierKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($carrierKillsTotalQueryWeekly);
                    $carrierKillsTotalQueryMonthly = "UPDATE monthlykills SET carrierKillsTotal = carrierKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($carrierKillsTotalQueryMonthly);
                    //printf("{$carrierKillsTotalQuery}\n");
                    $capKillsTotalQuery3 = "UPDATE totalkills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQuery3);
                    $capKillsTotalQueryDaily3 = "UPDATE dailykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryDaily3);
                    $capKillsTotalQueryWeekly3 = "UPDATE weeklykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryWeekly3);
                    $capKillsTotalQueryMonthly3 = "UPDATE monthlykills SET capKillsTotal = capKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($capKillsTotalQueryMonthly3);
                    //printf("{$capKillsTotalQuery3}\n");
                }

                if ($shipType == "Battleships" || $shipType == "Faction Battleships" || $shipType == "Black Ops" || $shipType == "Marauders") {
                    $hourKills["C{$class}"]["battleshipKillsHour"] += 1;
                    $battleShipsQuery = "UPDATE totalkills SET battleshipKillsTotal = battleshipKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($battleShipsQuery);
                    $battleShipsQueryDaily = "UPDATE dailykills SET battleshipKillsTotal = battleshipKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($battleShipsQueryDaily);
                    $battleShipsQueryWeekly = "UPDATE weeklykills SET battleshipKillsTotal = battleshipKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($battleShipsQueryWeekly);
                    $battleShipsQueryMonthly = "UPDATE monthlykills SET battleshipKillsTotal = battleshipKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($battleShipsQueryMonthly);
                    //printf("{$battleShipsQuery}\n");
                }

                if ($shipType == "Battlecruisers" || $shipType == "Battlecruisers (Attack)" || $shipType == "Command Ships" || $shipType == "Cruisers" || $shipType == "Faction Cruisers" ||
                 $shipType == "Heavy Assault Cruisers" || $shipType == "Heavy Interdictors" || $shipType == "Logistics Cruisers" || $shipType == "Recon Ships" || $shipType == "Strategic Cruisers" ||
                 $shipType == "Special Edition Battlecruiser" || $shipType == "Force Recon") {
                    $hourKills["C{$class}"]["cruiserKillsHour"] += 1;
                    $cruiserQuery = "UPDATE totalkills SET cruiserKillsTotal = cruiserKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($cruiserQuery);
                    $cruiserQueryDaily = "UPDATE dailykills SET cruiserKillsTotal = cruiserKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($cruiserQueryDaily);
                    $cruiserQueryWeekly = "UPDATE weeklykills SET cruiserKillsTotal = cruiserKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($cruiserQueryWeekly);
                    $cruiserQueryMonthly = "UPDATE monthlykills SET cruiserKillsTotal = cruiserKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($cruiserQueryMonthly);
                    //printf("{$cruiserQuery}\n");
                }

                if ($shipType == "Assault Frigates" || $shipType == "Covert Ops" || $shipType == "Destroyers" || $shipType == "Electronic Attack Frigates" || $shipType == "Exploration Frigate" || $shipType == "Faction Frigates" ||
                 $shipType == "Frigates" || $shipType == "Interceptors" || $shipType == "Interdictors" || $shipType == "Rookie Ships" || $shipType == "Stealth Bombers" || $shipType == "Tactical Destroyers" ||
                 $shipType == "Faction Shuttles" || $shipType == "Shuttle") {
                    $hourKills["C{$class}"]["frigateKillsHour"] += 1;
                    $frigQuery = "UPDATE totalkills SET frigateKillsTotal = frigateKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($frigQuery);
                    $frigQueryDaily = "UPDATE dailykills SET frigateKillsTotal = frigateKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($frigQueryDaily);
                    $frigQueryWeekly = "UPDATE weeklykills SET frigateKillsTotal = frigateKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($frigQueryWeekly);
                    $frigQueryMonthly = "UPDATE monthlykills SET frigateKillsTotal = frigateKillsTotal + 1 WHERE class = {$class}";
                    $conn->query($frigQueryMonthly);
                    //printf("{$frigQuery}\n");
                }
                // Add General Kill Stats
                $hourKills["C{$class}"]["hourKills"] += 1;
                $totalQuery = "UPDATE totalkills SET totalKills = totalKills + 1 WHERE class = {$class}";
                $conn->query($totalQuery);
                $totalQueryDaily = "UPDATE dailykills SET totalKills = totalKills + 1 WHERE class = {$class}";
                $conn->query($totalQueryDaily);
                $totalQueryWeekly = "UPDATE weeklykills SET totalKills = totalKills + 1 WHERE class = {$class}";
                $conn->query($totalQueryWeekly);
                $totalQueryMonthly = "UPDATE monthlykills SET totalKills = totalKills + 1 WHERE class = {$class}";
                $conn->query($totalQueryMonthly);
                //printf("{$totalQuery}\n");
                // Set lastKill and push onto killSeen
                printf("Pushing $killID onto newKills and seenKills\n");
                array_push($newKills, $killID);
                array_push($killSeen, $killID);
            }
            if (count($newKills) >= (190 * ($loopCount + 1))) {
                $continue = true;
                printf("Continuing from kill {$killID}\n");
            } else {
                $continue = false;
                printf("No more kills between {$fromHour} and {$toHour} - Last kill: {$killID}\n");
            }
        } else {
            $continue = false;
            printf("All kills retrieved to {$killID}\n");
        }
          $loopCount++;
    }

    $query = "UPDATE lastkill SET killID = {$killID}";
  //printf($query."\n");
    $conn->query($query);

    foreach ($newKills as $newKill) {
        $newKillQuery = "INSERT INTO killseen (killID) VALUES ({$newKill})";
        $conn->query($newKillQuery);
    }

    $classTypeArray = array_keys($hourKills);
  //printf("Class Types keys\n");
  //print_r($classTypeArray);
    foreach ($classTypeArray as $class) {
        $arrayKeys = array_keys($hourKills[$class]);
        //printf("\nArray Keys\n");
        //print_r($arrayKeys);
        foreach ($arrayKeys as $key) {
          //printf("\nCurrent Key - ");
          //print_r($key);
            $increment = null;
            if ($updateType == "newHour" || $updateType == "cleanRefresh") {
                $increment = "{$key} = ".$hourKills[$class][$key]."";
                $query = "UPDATE totalkills SET {$increment} WHERE class = ".substr($class, 1)."";
                $conn->query($query);
                //printf($query."\n");
            } elseif ($updateType == "refreshCurrent") {
                if ($hourKills[$class]["hourKills"] > 0) {
                    $increment = "{$key} = {$key} + ".$hourKills[$class][$key]."";
                    $query = "UPDATE totalkills SET {$increment} WHERE class = ".substr($class, 1)."";
                    $conn->query($query);
                    //printf($query."\n");
                }
            }
        }
    }
    $conn->close();
    printf("DONE\n");
    return 0;
}
