<?php
include 'settings.php';
function insert_saldo($mysql_conn)
{
	$max_date = $mysql_conn->query("select max(t.mx) as result from ((select max(date) as mx from payments) union (select max(date) as mx from charges)) as t")->fetch_assoc()['result']; //получаем месяц до которого есть информация по платежам-долгам
	$min_date = $mysql_conn->query("select min(t.mn) as result from ((select min(date) as mn from payments) union (select min(date) as mn from charges)) as t")->fetch_assoc()['result']; // получаем месяц с которого есть инорфмация по платежам-долгам

	$res_flats = $mysql_conn->query("(select number_flat from payments) union (select number_flat from charges)");

	if (!is_null($max_date)){
		echo sprintf("Max date: %s, Min date: %s <br>",$max_date, $min_date);
		while($row = $res_flats->fetch_assoc()){
			$value = $row['number_flat'];
			echo "Flat: ".$value. "<br>";
			$saldo = 0;
			for ($cur_date = strtotime($min_date), $max = strtotime($max_date); $cur_date<=$max;) 
			{
				$saldo_row = $mysql_conn->query(sprintf("select payment, charge from saldo where month='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc();
				$charge = $mysql_conn->query(sprintf("select cash from charges where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$payment = $mysql_conn->query(sprintf("select cash from payments where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$charge = (!is_null($charge))? $charge:0; 
				$payment = (!is_null($payment))? $payment:0;
				
				if (!is_null($saldo_row))
				{
					if ($saldo_row['payment']!=$payment || $saldo_row['charge']!=$charge)
						if ($mysql_conn->query(sprintf("update saldo set payment = %d, charge = %d where month='%s' number_flat = %d",$payment, $charge, date('Y-m-d',$cur_date), $value)))
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d Successful!<br>",$value, date('Y-m-d', $cur_date), $payment, $charge);
						else
						{
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d - Failure, errno: %d, error: %s<br> ",$value, date('Y-m-d', $cur_date),$payment, $charge, $mysqli->errno, $mysqli->error);
							break;
						}
				}
				else
				{
					if ($mysql_conn->query(sprintf("insert saldo(number_flat, month, payment, charge) value(%d, '%s', %d, %d)",$value, date('Y-m-d',$cur_date),$payment, $charge))){
						echo sprintf("insert number_flat: %d, month: '%s', payment: %d, charge: %d - Successful!<br>",$value, date('Y-m-d', $cur_date), $payment, $charge);
					}
					else{
						echo sprintf("insert number_flat: %d, month: '%s', payment: %d, charge: %d - Failure, errno: %d, error: %s<br> ",$value, date('Y-m-d', $cur_date),$payment, $charge, $mysqli->errno, $mysqli->error);
						break;
					}
				}
				$cur_date = mktime(0, 0, 0, date("m", $cur_date)+1 , date("d", $cur_date), date("Y", $cur_date));
			};
		};
	}
	else
	{
		echo "Max, Min date: NULL";
	}
};

$mysqli = new mysqli($host, $user, $password, $data_base);
insert_saldo($mysqli);
?>