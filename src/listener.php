<?php
require_once 'scheduler.php';
$queueListener = new QueueListener();
$queueListener->listenToQueue();
