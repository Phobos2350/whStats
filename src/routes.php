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
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'daily.html', $args);
});

$app->get('/weekly', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'weekly.html', $args);
});

$app->get('/monthly', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'monthly.html', $args);
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
    return $this->renderer->render($response, 'top'.$tzQuery.'.html', $args);
});
