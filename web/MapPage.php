<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
        <?php
        if(!isset($_REQUEST['ServerName'])) {
            echo $_REQUEST['ServerName'] = 'Unknown DynMap';
        } else {
	        echo($_REQUEST['ServerName'] . '\'s DynMap');
        }
        ?>
    </title>
    <script type="text/javascript" src="assets/js/dragscroll.js"></script>
    <script type="text/javascript" src="assets/js/menu-manager.js"></script>
    <link rel="stylesheet" href="assets/css/map-page.css"/>
</head>
<body class="dragscroll">
    <div class="wrapper">
        <div class="menu menu-activated" id="menu">
            <div id="menu-box" class="cursor-hand box box-deactivated" onclick="toggleMenuBox()"></div>
            <h2>Menu</h2>
            <hr>
            <p>This is some text that will be displayed.</p>
        </div>
        <img src="<?php echo requestInitialRegion(); ?>">
    </div>
</body>
</html>


<?php

function requestInitialRegion(): string {
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($socket, '127.0.0.1', 80);
    socket_write($socket, "REQUEST_INITIAL_REGION", strlen("REQUEST_INITIAL_REGION"));

    $readData = socket_read($socket, 1);
    while($readData === false || $readData === "") {
        sleep(1);
    }
    $result = "";
    $buffer = socket_read($socket, 14);
    if(($readData . $buffer) !== "REGION_RESPONSE") {
        echo "Error: No response from the DynMapPMMP Plugin. Maybe the server isn't running?";
        return "";
    }
    while($buffer = socket_read($socket, 5024)) {
        $result .= $buffer;
    }
    return "data:image/jpg;base64," . base64_encode($result);
}

?>