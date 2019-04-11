<?php
include 'settings.php';
$mysqli = new mysqli($host, $user, $password, $data_base);
$res_old_data = $mysqli->query(sprintf("select number_flat, cash, date from charges"));
if ($res_old_data){
	echo $res_old_data->num_rows;
}
?>