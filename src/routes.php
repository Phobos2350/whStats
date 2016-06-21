<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.html', $args);
});

$app->get('/daily', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/daily' route");

    // Render index view
    return $this->renderer->render($response, 'daily.html', $args);
});

$app->get('/weekly', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/weekly' route");

    // Render index view
    return $this->renderer->render($response, 'weekly.html', $args);
});

$app->get('/monthly', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/monthly' route");

    // Render index view
    return $this->renderer->render($response, 'monthly.html', $args);
});

$app->get('/api/{period}[/]', function ($request, $response, $args) {
    $period = strval($args['period']);
    $this->logger->info("Slim-Skeleton '/api/$period' route");

    if($period == "hourly") {
      $stats = $this->db->prepare("SELECT * FROM totalkills");
      $stats->execute();
      $result = $stats->fetchAll();
      return json_encode($result);
    }
    if($period == "daily") {
      $stats = $this->db->prepare("SELECT * FROM dailykills");
      $stats->execute();
      $result = $stats->fetchAll();
      return json_encode($result);
    }
    if($period == "weekly") {
      $stats = $this->db->prepare("SELECT * FROM weeklykills");
      $stats->execute();
      $result = $stats->fetchAll();
      return json_encode($result);
    }
    if($period == "monthly") {
      $stats = $this->db->prepare("SELECT * FROM monthlykills");
      $stats->execute();
      $result = $stats->fetchAll();
      return json_encode($result);
    }
    if($period == "total") {
      $stats = $this->db->prepare("SELECT * FROM totalkills");
      $stats->execute();
      $result = $stats->fetchAll();
      return json_encode($result);
    }
});

$app->get('/api/entity/{sort}/{tz}/[{id}/]', function ($request, $response, $args) {
  $sort = strval($args['sort']);
  $tz = strval($args['tz']);
  $id = strval($args['id']);
  $sort != "" ? $sortQuery = " ORDER BY {$sort}" : $sortQuery = "";
  $id != "" ? $idQuery = " WHERE entityID = {$id}" : $idQuery = "";
  $this->logger->info("Slim-Skeleton '/entity/:tz' route where tz = $tz");
  $entity = $this->db->prepare("SELECT * FROM entitystats{$tz}{$idQuery}{$sortQuery}");
  $entity->execute();
  $result = $entity->fetchAll();
  return json_encode(array_reverse($result));
});

$app->get('/top/{tz}[/]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/top' route");
    $tzQuery = strtoupper(strval($args['tz']));
    // Render index view
    return $this->renderer->render($response, 'top.html', $args);
});

$app->get('/entity/{id}[/]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/entity' route");
    // $id = strval($args['id']);
    // // Render index view
    // $entity = $this->db->prepare("SELECT * FROM entitystatseu WHERE entityID = {$id}");
    // $entity->execute();
    // $result = $entity->fetchAll();
    // $statsEU = json_encode($result);
    return $this->renderer->render($response, 'entityStats.html', $args);
});
