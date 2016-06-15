<?php

  $tzQuery = $_GET['tz'];
  $entityQuery = $_GET['id'];
  $sortQuery = $_GET['sort'];
  //printf("Entity Query = {$entityQuery}\n");

  $servername = "localhost";
  $username = "whdata";
  $password = "whdata";
  $db = "killstats";
  $toEncode = array();

  $conn = new mysqli($servername, $username, $password, $db);

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $query = "SELECT * FROM lastkill";
  $result = $conn->query($query)->fetch_assoc();
  $fromDate = $result["fromDate"];
  $lastCheck = $result["lastCheck"];

  $from = date_format(date_create($lastCheck), 'Ymd0000');
  $lastCheck = date_format(date_create($lastCheck), 'Ymd2359');

  if($tzQuery) {
    $getTz = strtolower($tzQuery);
  } else {
    printf("Please enter a valid Timezone Query e.g. <br><a href='http://stats.limited-power.co.uk/getEntity.php?tz=eu'>http://stats.limited-power.co.uk/getEntity.php?tz=eu</a><br>
                                                       <a href='http://stats.limited-power.co.uk/getEntity.php?tz=ru'>http://stats.limited-power.co.uk/getEntity.php?tz=ru</a><br>
                                                       <a href='http://stats.limited-power.co.uk/getEntity.php?tz=us'>http://stats.limited-power.co.uk/getEntity.php?tz=us</a><br>
                                                       <a href='http://stats.limited-power.co.uk/getEntity.php?tz=au'>http://stats.limited-power.co.uk/getEntity.php?tz=au</a><br>");
    exit;
  }

  if($entityQuery) {
    $entityID = $entityQuery;
    $getEntity = " WHERE entityID = {$entityID}";
  } else {
    $getEntity = "";
  }



  $query = "SELECT * FROM entitystats{$getTz}{$getEntity}";
  $out = "Kills from ".$from." to ".$lastCheck." <br />\n";
  $result = $conn->query($query);
  if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $toEncode[] = $row;
    }
  }
  //printf($out);

  if($sortQuery == "true") {
    usort($toEncode, function ($item1, $item2) {
        return $item2['whKills'] <=> $item1['whKills'];
    });
  }

  $output = json_encode($toEncode);
  echo $output;
  return $output;
?>
