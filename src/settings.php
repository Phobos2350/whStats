<?php
return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
        "db" => [
            "host" => "localhost",
            "dbname" => "killstats",
            "user" => "statsapp",
            "pass" => "W9Wex8KbXhnV6qD"
        ],
        'view' => [
            'template_path' => __DIR__ . '/../templates/twig',
            'twig' => [
               'cache' => __DIR__ . '/../cache/twig',
               'debug' => true,
              'auto_reload' => true,
            ],
        ],
    ],
];
