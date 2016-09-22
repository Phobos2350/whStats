<?php
require_once 'rethinkQueries.php';

//Routes
$app->get('/', function ($request, $response, $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    //Maint-page
    //return $this->renderer->render($response, 'maint.html', $args);
    return $this->view->render($response, 'index.html', $args);
})->setName('index');

$app->get('/entities[/]', function ($request, $response, $args) {
    $this->logger->info("Slim-Skeleton '/entities' route");
    return $this->view->render($response, 'entities.html', $args);
})->setName('entities');

$app->get('/entity/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $this->logger->info("Slim-Skeleton '/entity/{$id}' route");
    return $this->view->render($response, 'entity.html', $args);
})->setName('entities');

$app->get('/api/rethink/year/{year}/month/{month}/entity/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = intval($args['id'], 10);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/year/{$year}/month/{$month}/entity/{$id}' route");
    $returnArray = $rethinkQueries->getEntityTopKillers($year, $month, $id);
    return json_encode($returnArray);
});

$app->get('/api/rethink/year/{year}/month/{month}/entityStats/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = intval($args['id'], 10);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/year/{$year}/month/{$month}/entityStats/{$id}' route");
    $returnArray = $rethinkQueries->getEntityStatsMonthByID($id, $year, $month);
    return json_encode($returnArray);
});

$app->get('/api/rethink/year/{year}/month/{month}/char/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = (int)strval($args['id']);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/char/$id' route");
    $returnArray = $rethinkQueries->getCharShipUsage($year, $month, $id);
    return json_encode($returnArray);
});

$app->get('/api/rethink/limit/{limit}/period/{period}/page/{page}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $period = strval($args['period']);
    $page = intval($args['page'], 10)-1;
    $limit = intval($args['limit'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/$period' route");
    $returnArray = $rethinkQueries->getPeriodKills($limit, $period, $page);
    return json_encode($returnArray);
});

$app->get('/api/rethink/limit/{limit}/year/{year}/month/{month}/page/{page}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $page = intval($args['page'], 10)-1;
    $limit = intval($args['limit'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/month/$year/$month' route");
    $returnArray = $rethinkQueries->getMonthKills($limit, $year, $month, $page);
    return json_encode($returnArray);
});

$app->get('/api/rethink/ship/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = (int)strval($args['id']);
    $this->logger->info("Slim-Skeleton '/api/rethink/ship/$id' route");
    $returnArray = $rethinkQueries->getShip($id);
    return json_encode($returnArray);
});

$app->get('/api/rethink/stats/{period}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $period = strval($args['period']);
    $key = md5(strtoupper('periodStats_'.$period));
    $data = array('period' => $period);
    $cachedStats = $rethinkQueries->getCache('periodStats', $key, 5);
    if($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getStatsPeriod', 'periodStats', $key, $data);
    }
    $returnArray = $cachedStats['statsArray']['stats'];
    if($returnArray != null) {
      return json_encode($returnArray);
    } else {
      $rethinkQueries->queueTask('getStatsPeriod', 'periodStats', $key, $data);
      return $this->view->render($response, 'noMonth.html', ['year' => $period, 'month' => '']);
    }
    $this->logger->info("Slim-Skeleton '/api/rethink/stats/$period' route");
});

$app->get('/api/rethink/stats/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $key = md5(strtoupper('periodStats_'.$year.'_'.$month));
    $data = array('year' => $year, 'month' => $month);
    $cachedStats = $rethinkQueries->getCache('periodStats', $key, 30);
    //if($cachedStats['refreshDue']) {
    //  $rethinkQueries->queueTask('getStatsMonth', 'periodStats', $key, $data);
    //}
    $returnArray = $cachedStats['statsArray']['stats'];
    if($returnArray != null) {
      return json_encode($returnArray);
    } else {
      $rethinkQueries->queueTask('getStatsMonth', 'periodStats', $key, $data);
      return $this->view->render($response, 'noMonth.html', ['year' => $year, 'month' => $month]);
    }
    $this->logger->info("Slim-Skeleton '/api/rethink/stats/year/$year/month/$month' route");
});

$app->get('/api/rethink/entities/tz/{tz}/period/{period}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $conn = r\connect('localhost', 28015, 'stats');
    $tz = strval($args['tz']);
    $period = strval($args['period']);
    $key = md5(strtoupper('entityStats_'.$tz.'_'.$period));
    $data = array('tz' => $tz, 'period' => $period);
    $cachedStats = $rethinkQueries->getCache('entityStats', $key, 10);
    if($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', $key, $data);
    }
    $returnArray = $cachedStats['statsArray'];
    if($returnArray != null) {
      return json_encode($returnArray);
    } else {
      $rethinkQueries->queueTask('getEntityStatsPeriod', 'entityStats', $key, $data);
      return $this->view->render($response, 'noMonth.html', ['year' => $tz, 'month' => $period]);
    }
});

$app->get('/api/rethink/entities/tz/{tz}/period/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $tz = strval($args['tz']);
    $key = md5(strtoupper('entityStats_'.$tz.'_'.$year.'_'.$month));
    $data = array('tz' => $tz, 'year' => $year, 'month' => $month);
    $thisMonth = date("m");
    $thisYear = date("Y");
    if($year == intval($thisYear, 10) && $month == intval($thisMonth, 10)) {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 30);
    } else {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 10080);
    }
    //if($cachedStats['refreshDue']) {
    //  $rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
    //}
    $returnArray = $cachedStats['statsArray'];
    if($returnArray != null) {
      return json_encode($returnArray);
    } else {
      $rethinkQueries->queueTask('getEntityStatsMonth', 'entityStats', $key, $data);
      return $this->view->render($response, 'noMonth.html', ['year' => $year, 'month' => $month]);
    }
    return $this->view->render($response, 'noMonth.html', ['year' => $year, 'month' => $month]);
});
