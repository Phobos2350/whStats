<?php
require_once 'rethinkQueries.php';
$queries = new RethinkQueries();
$queries->getEntityStatsMonth('all', 2016, 4);
//$queries->getEntityStatsMonthByID(99005198, 2016, 9);
