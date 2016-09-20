<?php
require_once("/home/stats/vendor/danielmewes/php-rql/rdb/rdb.php");
$conn = r\connect('localhost', 28015, 'stats');
//
// $entitiesArray = r\table('entities')->run($conn);
// foreach($entitiesArray as $entity) {
//   printf("EntityID - {$entity['corporationID']}");
//   $killsList = r\table('whKills')->getAll($entity['corporationID'], array('index' => 'attacker_corporationID'))
//                 ->pluck('killID')
//                 ->run($conn);
//   $killsArray = array();
//   foreach ($killsList as $kill) {
//     array_push($killsArray, $kill['killID']);
//   }
//   //print_r($killsArray);
//   $updateResult = r\table('entities')->get($entity['corporationID'])->update(array(
//     'kills' => $killsArray
//   ))->run($conn);
//   printf("...DONE\n");
// }

$charArray = r\table('characters')->run($conn);
foreach($charArray as $char) {
  printf("CharID - {$char['characterID']}");
  $killsList = r\table('whKills')->getAll($char['characterID'], array('index' => 'attacker_characterID'))
                ->pluck('killID')
                ->run($conn);
  $killsArray = array();
  foreach ($killsList as $kill) {
    array_push($killsArray, $kill['killID']);
  }
  //print_r($killsArray);
  $updateResult = r\table('characters')->get($char['characterID'])->update(array(
    'kills' => $killsArray
  ))->run($conn);
  printf("...DONE\n");
}
