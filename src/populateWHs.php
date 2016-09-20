<?php
include 'simple_html_dom.php';
require_once("/home/stats/vendor/danielmewes/php-rql/rdb/rdb.php");

$conn = r\connect('localhost', 28015, 'stats');

$systemArray = r\table('whSystems')->run($conn);
foreach($systemArray as $system) {
  $url = "https://zkillboard.com/system/".$system['systemID']."/";
  try {
      $html = file_get_html($url);
  } catch (Exception $e) {
      printf("Error Retrieving HTML data from {$url}\n");
  }

  if ($html) {
    $table = $html->find('table', 1);
    if ($table) {
        $row = $table->find('tr', 0);
        $cell = $row->find('td', 0);
        $systemName = $cell->children(0)->plaintext;
        $system['systemName'] = $systemName;
        $result = r\table('whSystems')->get($system['systemID'])->replace($system)->run($conn);
        print_r($system);
    }
  }
}
