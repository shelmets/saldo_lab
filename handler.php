<?php
include 'settings.php';
include 'handler_lib.php';

$mysqli = new mysqli($host, $user, $password, $data_base);
insert_saldo($mysqli);
switch ($_REQUEST['select']) {
case '1':
	firstReport($mysqli, $_REQUEST['year']);
	break;
case '2':
	secondReport($mysqli,$_REQUEST['year'], $_REQUEST['flat']);
	break;
}
$mysqli->close();
?>