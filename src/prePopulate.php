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

      for($i = 1; $i < 10; $i++) {
        $updateKills["C{$i}"] = array(
          "totalKills" => 0,
          "industrialKillsTotal" => 0,
          "t1KillsTotal" => 0,
          "t2KillsTotal" => 0,
          "t3KillsTotal" => 0,
          "capKillsTotal" => 0,
          "battleshipKillsTotal" => 0,
        	"cruiserKillsTotal" => 0,
          "frigateKillsTotal" => 0,
          "forceAuxKillsTotal" => 0,
          "dreadKillsTotal" => 0,
          "carrierKillsTotal" => 0,
          "podKillsTotal" => 0,
          "structureKillsTotal" => 0,
          "citadelKillsTotal" => 0,
          "totalISK" => 0,
          "smallKillsTotal" => 0,
          "fleetKillsTotal" => 0,
          "logiKillsTotal" => 0,
          "factionKillsTotal" => 0
        );
      }
      //print_r($updateKills);

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

      if($updateType == "newMonth") {
        // Let's clear the previous hour's seen kills
        printf("New Month, purging monthlykills table...\n");
        $purgeMonthlyQuery = "TRUNCATE TABLE monthlykills";
        $conn->query($purgeMonthlyQuery);
        for($i = 1; $i < 10; $i++) {
          $cleanMonthlyQuery = "INSERT INTO monthlykills (class) VALUES ({$i})";
          $conn->query($cleanMonthlyQuery);
          printf("Creating row in monthlykills - {$cleanMonthlyQuery}\n");
        }
      }
      if($updateType == "newWeek") {
        // Let's clear the previous hour's seen kills
        printf("New Week, purging weeklykills table...\n");
        $purgeWeeklyQuery = "TRUNCATE TABLE weeklykills";
        $conn->query($purgeWeeklyQuery);
        for($i = 1; $i < 10; $i++) {
          $cleanWeeklyQuery = "INSERT INTO weeklykills (class) VALUES ({$i})";
          $conn->query($cleanWeeklyQuery);
          printf("Creating row in weeklykills - {$cleanWeeklyQuery}\n");
        }
      }
      if($updateType == "newDay") {
        // Let's clear the previous hour's seen kills
        printf("New Day, purging dailykills table...\n");
        $purgeDailyQuery = "TRUNCATE TABLE dailykills";
        $conn->query($purgeDailyQuery);
        for($i = 1; $i < 10; $i++) {
          $cleanDailyQuery = "INSERT INTO dailykills (class) VALUES ({$i})";
          $conn->query($cleanDailyQuery);
          printf("Creating row in dailykills - {$cleanDailyQuery}\n");
        }
      }

      if($updateType == "newDay" || $updateType == "newWeek" || $updateType == "newMonth") {
        // Let's clear the previous hour's seen kills
        printf("{$updateType}, purging killseen table...\n");
        $killSeen = array();
        $killSeenQuery = "TRUNCATE TABLE killseen";
        $killSeenResult = $conn->query($killSeenQuery);
      }

      while($continue) {
        printf("Loop {$loopCount}\n");
        $json = NULL;
        $kills = NULL;
        if($loopCount >= 50) {
          break;
        }
        if($loopCount == 0) {
          $fromHour = date('YmdHi', strtotime($fromHour));
        } else {
          $fromHour = date('YmdHi', strtotime($killTimeFormatted));
        }
        $url = "https://zkillboard.com/api/kills/w-space/no-items/no-attackers/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
        //$url = "https://zkillboard.com/api/kills/no-items/no-attackers/w-space/limit/200/orderDirection/asc/afterKillID/".$lastKill."/";
        if (!$json = file_get_contents_retry($url)) {
          $error = error_get_last();
          printf("HTTP request failed. Error was: " . $error['message'] . "\n Exiting");
          break;
        }
        $kills = json_decode($json, true);
        if(empty($kills)) {
          $url = "https://zkillboard.com/api/kills/w-space/no-attackers/limit/200/orderDirection/asc/startTime/".$fromHour."/endTime/".$toHour."/";
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
            $systemID = $kill["solarSystemID"];
            $typeID = $kill["victim"]["shipTypeID"];
            $iskValue = formatValue($kill["zkb"]["totalValue"]);
            $involved = $kill["zkb"]["involved"];
            $killTime = $kill["killTime"];
            $killTimeFormatted = date_format(date_create($killTime),"YmdHi");
            $lastKill = $kill["killID"];

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

            if($involved > 4) {
              $hourTime = date_format(date_create($killTime),"YmdH00");
              $relatedURL = "https://zkillboard.com/related/{$systemID}/{$hourTime}/";

              try {
                $html = file_get_html($relatedURL);
              } catch (Exception $e) {
                printf("Error Retrieving HTML data from {$relatedURL}\n");
              }

              if($html) {
                $table = $html->find('table', 0);
                if($table) {
                  $rows = $table->find('tr');
                  $teamA = filter_var($rows[0]->children(0)->plaintext, FILTER_SANITIZE_NUMBER_INT);
                  $teamB = filter_var($rows[0]->children(1)->plaintext, FILTER_SANITIZE_NUMBER_INT);
                  //printf("Team A {$teamA} - Team B {$teamB}\n");
                  if($teamA > 4 && $teamB > 4) {
                    printf("FLEET kill with {$teamA} Team A vs {$teamB} Team B!\n");
                    $updateKills["C{$class}"]["fleetKillsTotal"] += 1;
                    //printf("{$fleetQuery}\n");
                  } else {
                    //printf("SMALL kill with {$teamA} Team A vs {$teamB} Team B!\n");
                    $updateKills["C{$class}"]["smallKillsTotal"] += 1;
                    //printf("{$smallQuery}\n");
                  }
                } else {
                  //printf("--HTML PARSE ERROR-- SMALL kill with {$involved} involved!\n");
                  $updateKills["C{$class}"]["smallKillsTotal"] += 1;
                  //printf("{$smallQuery}\n");
                }
              } else {
                //printf("--NO HTML-- DEFAULT SMALL kill with {$involved} involved!\n");
                $updateKills["C{$class}"]["smallKillsTotal"] += 1;
                //printf("{$smallQuery}\n");
              }
            } else {
              //printf("SMALL kill with < 5 involved\n");
              $updateKills["C{$class}"]["smallKillsTotal"] += 1;
              //printf("{$smallQuery}\n");
            }

            if($iskValue > 0) {
              $updateKills["C{$class}"]["totalISK"] += $iskValue;
              //printf("{$iskQuery}\n");
            }

            if($shipType == "Structure") {
              $updateKills["C{$class}"]["structureKillsTotal"] += 1;
              //printf("{$structQuery}\n");
            }

            if($shipType == "Citadel") {
              $updateKills["C{$class}"]["citadelKillsTotal"] += 1;
              //printf("{$citadelQuery}\n");
            }

            if($shipType == "Capsule" || $shipType == "Capsule - Genolution 'Auroral' 197-variant") {
              $updateKills["C{$class}"]["podKillsTotal"] += 1;
              //printf("{$podQuery}\n");
            }

            if($shipTech == "T1" && $shipType != "Capsule" && $shipType != "Capsule - Genolution 'Auroral' 197-variant" && $shipType != "Structure" &&
                $shipType != "Citadel" && $shipType != "Dreadnoughts" && $shipType != "Force Auxiliary" && $shipType != "Carriers" && $shipType != "Capital Industrial Ships" &&
                $shipType != "Fighters") {
              if($shipRace == "Faction") {
                $updateKills["C{$class}"]["factionKillsTotal"] += 1;
                //printf("{$tech1Query}\n");
              } else {
                $updateKills["C{$class}"]["t1KillsTotal"] += 1;
                //printf("{$tech1Query}\n");
              }
            }

            if($shipTech == "T2") {
              $updateKills["C{$class}"]["t2KillsTotal"] += 1;
              //printf("{$tech2Query}\n");
            }

            if($shipTech == "T3") {
              $updateKills["C{$class}"]["t3KillsTotal"] += 1;
              //printf("{$tech3Query}\n");
            }

            if($shipType == "Industrial Ships" || $shipType == "Transport Ships" || $shipType == "Mining Barges" || $shipType == "Exhumer Barges" || $shipType == "Mining Frigate") {
              $updateKills["C{$class}"]["industrialKillsTotal"] += 1;
              //printf("{$indyQuery}\n");
            }

            if($shipType == "Logistics Cruisers") {
              $updateKills["C{$class}"]["logiKillsTotal"] += 1;
              //printf("{$logiQuery}\n");
            }

            if($shipType == "Capital Industrial Ships" || $shipType == "Jump Freighters") {
              $updateKills["C{$class}"]["capKillsTotal"] += 1;
              //printf("{$capKillsTotalQuery}\n");
            }

            if($shipType == "Force Auxiliary") {
              $updateKills["C{$class}"]["forceAuxKillsTotal"] += 1;
              $updateKills["C{$class}"]["capKillsTotal"] += 1;
              //printf("{$capKillsTotalQuery}\n");
            }

            if($shipType == "Dreadnoughts") {
              $updateKills["C{$class}"]["dreadKillsTotal"] += 1;
              $updateKills["C{$class}"]["capKillsTotal"] += 1;
              //printf("{$capKillsTotalQuery2}\n");
            }

            if($shipType == "Carriers") {
              $updateKills["C{$class}"]["carrierKillsTotal"] += 1;
              $updateKills["C{$class}"]["capKillsTotal"] += 1;
              //printf("{$carrierKillsTotalQuery}\n");
              //printf("{$capKillsTotalQuery3}\n");
            }

            if($shipType == "Battleships" || $shipType == "Faction Battleships" || $shipType == "Black Ops" || $shipType == "Marauders") {
              $updateKills["C{$class}"]["battleshipKillsTotal"] += 1;
              //printf("{$battleShipsQuery}\n");
            }

            if($shipType == "Battlecruisers" || $shipType == "Battlecruisers (Attack)" || $shipType == "Command Ships" || $shipType == "Cruisers" || $shipType == "Faction Cruisers" ||
               $shipType == "Heavy Assault Cruisers" || $shipType == "Heavy Interdictors" || $shipType == "Logistics Cruisers" || $shipType == "Recon Ships" || $shipType == "Strategic Cruisers" ||
               $shipType == "Special Edition Battlecruiser" || $shipType == "Force Recon") {

              $updateKills["C{$class}"]["cruiserKillsTotal"] += 1;
              //printf("{$cruiserQuery}\n");
            }

            if($shipType == "Assault Frigates" || $shipType == "Covert Ops" || $shipType == "Destroyers" || $shipType == "Electronic Attack Frigates" || $shipType == "Exploration Frigate" || $shipType == "Faction Frigates" ||
               $shipType == "Frigates" || $shipType == "Interceptors" || $shipType == "Interdictors" || $shipType == "Rookie Ships" || $shipType == "Stealth Bombers" || $shipType == "Tactical Destroyers" ||
               $shipType == "Faction Shuttles" || $shipType == "Shuttle") {

              $updateKills["C{$class}"]["frigateKillsTotal"] += 1;
              //printf("{$frigQuery}\n");
            }
            // Add General Kill Stats
            $updateKills["C{$class}"]["totalKills"] += 1;
            //printf("{$totalQuery}\n");
            // Set lastKill and push onto killSeen
            printf("Pushing $lastKill onto newKills and seenKills\n");
            array_push($newKills, $lastKill);
            array_push($killSeen, $lastKill);
          }
          if(count($newKills) >= (190 * ($loopCount + 1))) {
            $continue = TRUE;
            printf("Continuing from kill {$lastKill}\n");
          } else {
            $continue = FALSE;
            printf("No more kills between {$fromHour} and {$toHour} - Last kill: {$lastKill}\n");
          }
        }
        else {
          $continue = FALSE;
          printf("All kills retrieved to {$lastKill}\n");
        }
        $loopCount++;
      }

      $query = "UPDATE lastkill SET killID = {$lastKill}";
      printf($query."\n");
      $conn->query($query);

      foreach($newKills as $newKill) {
        $newKillQuery = "INSERT INTO killseen (killID) VALUES ({$newKill})";
        $conn->query($newKillQuery);
      }

      $classTypeArray = array_keys($updateKills);
      //printf("Class Types keys\n");
      //print_r($classTypeArray);
      foreach($classTypeArray as $class) {
        $arrayKeys = array_keys($updateKills[$class]);
        //printf("\nArray Keys\n");
        //print_r($arrayKeys);
        foreach($arrayKeys as $key) {
          //printf("\nCurrent Key - ");
          //print_r($key);
          $increment = NULL;
          if($updateType == "newDay") {
            $increment = "{$key} = ".$updateKills[$class][$key]."";
            $query = "UPDATE dailykills SET {$increment} WHERE class = ".substr($class, 1)."";
            $conn->query($query);
            printf($query."\n");
          } elseif($updateType == "newWeek") {
            $increment = "{$key} = ".$updateKills[$class][$key]."";
            $query = "UPDATE weeklykills SET {$increment} WHERE class = ".substr($class, 1)."";
            $conn->query($query);
            printf($query."\n");
          } elseif($updateType == "newMonth") {
            $increment = "{$key} = ".$updateKills[$class][$key]."";
            $query = "UPDATE monthlykills SET {$increment} WHERE class = ".substr($class, 1)."";
            $conn->query($query);
            printf($query."\n");
          }
        }
      }
      $conn->close();
      printf("DONE\n");
      return 0;
    }
?>
