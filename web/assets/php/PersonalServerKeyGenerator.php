<?php

declare(strict_types = 1);

$serverAddress = $_POST['serverAddress'];
$serverPort = $_POST['serverPort'];

echo base64_encode($serverAddress . ":" . $serverPort);

?>