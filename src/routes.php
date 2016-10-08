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
    $id = intval($args['id'], 10);
    $this->logger->info("Slim-Skeleton '/entity/{$id}' route");
    return $this->view->render($response, 'entity.html', $args);
})->setName('entities');

$app->get('/pilots[/]', function ($request, $response, $args) {
    $this->logger->info("Slim-Skeleton '/pilots' route");
    return $this->view->render($response, 'pilots.html', $args);
})->setName('pilots');

$app->get('/pilot/{id}[/]', function ($request, $response, $args) {
    $id = intval($args['id'], 10);
    $this->logger->info("Slim-Skeleton '/pilot/{$id}' route");
    return $this->view->render($response, 'pilot.html', $args);
})->setName('pilots');

$app->get('/entity/noData/{id}[/]', function ($request, $response, $args) {
    $id = intval($args['id'], 10);
    $this->logger->info("Slim-Skeleton '/entity/noData/{$id}' route");
    return $this->view->render($response, 'noEntity.html', ['id' => $id]);
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

$app->get('/api/rethink/year/{year}/month/{month}/char/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = (int)strval($args['id']);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/year/{$year}/month/{$month}/char/{$id}' route");
    $returnArray = $rethinkQueries->getCharShipUsage($year, $month, $id);
    return json_encode($returnArray);
});

$app->get('/api/rethink/limit/{limit}/period/{period}/page/{page}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $period = strval($args['period']);
    $page = intval($args['page'], 10)-1;
    $limit = intval($args['limit'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/limit/{$limit}/period/{$period}/page/{$page}' route");
    $returnArray = $rethinkQueries->getPeriodKills($limit, $period, $page);
    return json_encode($returnArray);
});

$app->get('/api/rethink/limit/{limit}/year/{year}/month/{month}/page/{page}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $page = intval($args['page'], 10)-1;
    $limit = intval($args['limit'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/limit/{$limit}/year/{$year}/month/{$month}/page/{$page}' route");
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
    $this->logger->info("Slim-Skeleton '/api/rethink/stats/$period' route");
    $key = md5(strtoupper('periodStats_'.$period.'_0_0'));
    $data = array('period' => $period, 'year' => 0, 'month' => 0);
    if($period == 'hour') {
      $cachedStats = $rethinkQueries->getCache('periodStats', $key, 2);
    } elseif($period == 'day') {
      $cachedStats = $rethinkQueries->getCache('periodStats', $key, 10);
    } elseif($period == 'week') {
      $cachedStats = $rethinkQueries->getCache('periodStats', $key, 30);
    }
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('getStats', 'periodStats', $key, $data);
      return $this->view->render($response, 'noStats.html', ['period' => $period, 'year' => 0, 'month' => 0]);
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getStats', 'periodStats', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/stats/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/stats/year/$year/month/$month' route");
    $key = md5(strtoupper('periodStats_month_'.$year.'_'.$month));
    $data = array('period' => 'month', 'year' => $year, 'month' => $month);
    $cachedStats = $rethinkQueries->getCache('periodStats', $key, 45);
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('getStats', 'periodStats', $key, $data);
      return $this->view->render($response, 'noStats.html', ['period' => 'month', 'year' => $year, 'month' => $month]);
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getStats', 'periodStats', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/entities/period/{period}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $conn = r\connect('localhost', 28015, 'stats');
    $period = strval($args['period']);
    $this->logger->info("Slim-Skeleton '/api/rethink/entities/period/{$period}' route");
    $key = md5(strtoupper('entityStats_'.$period.'_0_0'));
    $data = array('period' => $period, 'year' => 0, 'month' => 0);
    if($period == 'hour') {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 5);
    } elseif($period == 'day') {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 10);
    } elseif($period == 'week') {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 30);
    }
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('genEntityStats', '', '', $data);
      $rethinkQueries->queueTask('getEntityStats', 'entityStats', $key, $data);
      return $this->view->render($response, 'noStats.html', ['period' => $period, 'year' => 0, 'month' => 0]);
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('genEntityStats', '', '', $data);
      $rethinkQueries->queueTask('getEntityStats', 'entityStats', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/entities/period/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/entities/period/year/{$year}/month/{$month}' route");
    $key = md5(strtoupper('entityStats_month_'.$year.'_'.$month));
    $data = array('period' => 'month', 'year' => $year, 'month' => $month);
    $thisMonth = date("m");
    $thisYear = date("Y");
    if($year == intval($thisYear, 10) && $month == intval($thisMonth, 10)) {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 45);
    } else {
      $cachedStats = $rethinkQueries->getCache('entityStats', $key, 10080);
    }
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('genEntityStats', '', '', $data);
      $rethinkQueries->queueTask('getEntityStats', 'entityStats', $key, $data);
      return $this->view->render($response, 'noStats.html', ['period' => 'month', 'year' => $year, 'month' => $month]);
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('genEntityStats', '', '', $data);
      $rethinkQueries->queueTask('getEntityStats', 'entityStats', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/entityStats/{id}/period/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = intval($args['id'], 10);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/entityStats/{$id}/period/year/{$year}/month/{$month}' route");
    $key = md5(strtoupper('entityStatsID_'.$id.'_'.$year.'_'.$month));
    $data = array('id' => $id, 'year' => $year, 'month' => $month);
    $cachedStats = $rethinkQueries->getCache('entityStatsID', $key, 30);
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('getEntityStatsMonthByID', 'entityStatsID', $key, $data);
      return null;
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getEntityStatsMonthByID', 'entityStatsID', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/pilotStats/{id}/period/year/{year}/month/{month}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = intval($args['id'], 10);
    $year = intval($args['year'], 10);
    $month = intval($args['month'], 10);
    $this->logger->info("Slim-Skeleton '/api/rethink/pilotStats/{$id}/period/year/{$year}/month/{$month}' route");
    $key = md5(strtoupper('pilotStatsID_'.$id.'_'.$year.'_'.$month));
    $data = array('id' => $id, 'year' => $year, 'month' => $month);
    $cachedStats = $rethinkQueries->getCache('pilotStatsID', $key, 30);
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('getPilotStatsMonthByID', 'pilotStatsID', $key, $data);
      return null;
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getPilotStatsMonthByID', 'pilotStatsID', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});

$app->get('/api/rethink/systemStats/{id}[/]', function ($request, $response, $args) {
    $rethinkQueries = new RethinkQueries();
    $id = strval($args['id']);
    $this->logger->info("Slim-Skeleton '/api/rethink/systemStats/{$id}' route");
    $key = md5(strtoupper('systemStatsID_'.$id));
    $data = array('id' => $id);
    $cachedStats = $rethinkQueries->getCache('systemStatsID', $key, 1);
    $returnArray['statsArray'] = $cachedStats['statsArray'];
    $returnArray['lastCached'] = $cachedStats['lastCached'];
    if($returnArray['statsArray'] == null) {
      $rethinkQueries->queueTask('getSystemStatsID', 'systemStatsID', $key, $data);
      return null;
    } elseif($cachedStats['refreshDue']) {
      $rethinkQueries->queueTask('getSystemStatsID', 'systemStatsID', $key, $data);
      return json_encode($returnArray);
    } else {
      return json_encode($returnArray);
    }
});
