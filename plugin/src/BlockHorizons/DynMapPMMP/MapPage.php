<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php
        if(!isset($_REQUEST['ServerName'])) {
            $_REQUEST['ServerName'] = "Unknown DynMap";
        }
        echo($_REQUEST['ServerName'] . '\'s DynMap');
        ?></title>
	<link rel="stylesheet" href="../../../../web/assets/css/main.css"/>
</head>
<body class="map-background">
    <div class="body">
        <img src="<?php echo requestInitialRegion();?>">
    </div>
</body>
</html>

<?php

function requestInitialRegion(): string {
    var_dump("Hi");
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_connect($socket, '127.0.0.1', 80);
	socket_write($socket, "REQUEST_INITIAL_REGION", strlen("REQUEST_INITIAL_REGION"));

	while(socket_read($socket, 1) === false || socket_read($socket, 1) === "") {
	    sleep(1);
    }
    $result = "";
	$buffer = socket_read($socket, 15);
	if($buffer !== "REGION_RESPONSE") {
		echo "Error: No response from the DynMapPMMP Plugin. Maybe the server isn't running?";
		return "";
	}
	while($buffer = socket_read($socket, 5024)) {
	    $result .= $buffer;
    }
	echo "data:image/png;base64," . base64_encode($result);

	return $this->socketListen($socket);
}

/**
 * @param resource $socket
 *
 * @return string
 */
function socketListen($socket): string {
	if(!is_resource($socket)) {
		return "";
	}
	if(($socket = socket_accept($socket)) === false) {
		echo "Error: No response from the DynMapPMMP Plugin. Maybe the server isn't running?";
		return "";
	}
	$buffer = substr($result = socket_read($socket, 66000), 0, 15);
	if($buffer !== "REGION_RESPONSE") {
		echo "Error: No response from the DynMapPMMP Plugin. Maybe the server isn't running?";
		return "";
	}
	return "data:image/png;base64," . base64_encode(str_replace("REGION_RESPONSE", "", $result));
}

?>