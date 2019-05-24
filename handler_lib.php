<?php

function sum_saldo($mysql_conn, $flat, $year){
	$saldo = 0;
	$res_saldo = $mysql_conn->query(sprintf("select payment-charge as sal from saldo where number_flat = %d and year(month) < %d",$flat,$year));
	if ($res_saldo){
		while($row = $res_saldo->fetch_assoc())
			$saldo+=$row['sal'];
	}
	else{
		return null;
	}
	return $saldo;
}
function insert_saldo($mysql_conn)
{
	$max_date = $mysql_conn->query("select max(t.mx) as result from ((select max(date) as mx from payments) union (select max(date) as mx from charges)) as t")->fetch_assoc()['result']; //получаем месяц до которого есть информация по платежам-долгам
	$min_date = $mysql_conn->query("select min(t.mn) as result from ((select min(date) as mn from payments) union (select min(date) as mn from charges)) as t")->fetch_assoc()['result']; // получаем месяц с которого есть инорфмация по платежам-долгам

	$res_flats = $mysql_conn->query("(select number_flat from payments) union (select number_flat from charges)");

	if (!is_null($max_date)){
		echo sprintf("Max date: %s, Min date: %s <br>",$max_date, $min_date);
		while($row = $res_flats->fetch_assoc()){
			$value = $row['number_flat'];
			for ($cur_date = strtotime($min_date), $max = strtotime($max_date); $cur_date<=$max;){

				$saldo_row = $mysql_conn->query(sprintf("select payment, charge from saldo where month='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc();
				$charge = $mysql_conn->query(sprintf("select cash from charges where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$payment = $mysql_conn->query(sprintf("select cash from payments where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$charge = (!is_null($charge))? $charge:0; 
				$payment = (!is_null($payment))? $payment:0;
				
				if (!is_null($saldo_row)){
					if ($saldo_row['payment']!=$payment || $saldo_row['charge']!=$charge)
						if ($mysql_conn->query(sprintf("update saldo set payment = %d, charge = %d where month='%s' number_flat = %d",$payment, $charge, date('Y-m-d',$cur_date), $value)))
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d Successful!<br>",$value, date('Y-m-d', $cur_date), $payment, $charge);
						else{
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d - Failure, errno: %d, error: %s<br> ",$value, date('Y-m-d', $cur_date),$payment, $charge, $mysqli->errno, $mysqli->error);
							break;
						}
				}
				else{
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

function firstReport($mysql_conn, $year){
	echo "<table border='1' width='100%''>";
	echo sprintf('<caption>First Report in %d</caption>', $year);
	echo "<thread>";
	echo "<tr>";
	echo "<th>Flat</th>";
	echo "<th>Saldo in begin</th>";
	for ($i=1;$i<=12;$i++){
		echo "<th>".$i."</th>";
	};
	echo "<th>Saldo in end</th>";
	echo "</tr>";
	echo "</thread>";
	echo "<tbody>";
	
	$res_flats = $mysql_conn->query("select distinct number_flat from saldo");
	while($r = $res_flats->fetch_assoc()){
		$value = $r['number_flat'];
		echo "<tr>";
		echo "<th>".$value."</th>";
		$curr_saldo = sum_saldo($mysql_conn, $value, $year);
		if (is_null($curr_saldo)){
			break;
			echo "SOME ERROR";
		};
		echo "<th>". $curr_saldo."</th>";
		for ($i=1;$i<=12;$i++){
			$row = $mysql_conn->query((sprintf("select payment, charge from saldo where year(month)=%d and number_flat=%d and month(month)=%d", $year, $value, $i)))->fetch_assoc();
			echo "<th>";
			$charge =  $row['charge'];
			echo (($charge!=NULL)? $charge:0 )."<br>";
			$payment = $row['payment'];
			echo (($payment!=NULL)? $payment:0)."<br>";
			$curr_saldo+=$payment - $charge;
			echo ($curr_saldo!=NULL)? $curr_saldo:0;
			echo "</th>";
		};
		echo "<th>".$curr_saldo."</th>";
		echo "</tr>";
	};
	echo "</tbody>";
	echo "</table>";
};

function secondReport($mysql_conn, $year, $value){
	$total_charges = 0;
	$total_payments = 0;
	echo "<table border='1' width='100%''>";
	echo sprintf('<caption>Second Report for %d in %d</caption>',$year, $value);
	echo "<thread>";
	echo "<tr>";
	echo "<th>Saldo in begin</th>";
	echo "<th></th>";
	for ($i=1;$i<=12;$i++)
	{
		echo "<th>".$i."</th>";
	};
	echo "<th>Total</th>";
	echo "<th>Saldo in end</th>";
	echo "</tr>";
	echo "</thread>";
	echo "<tbody>";

	echo "<tr>";
	$curr_saldo = sum_saldo($mysql_conn, $value, $year);
		if (is_null($curr_saldo)){
			$curr_saldo = "NULL";
		};
	echo "<th rowspan='2'>".$curr_saldo."</th>";
	echo "<th>payments</th>";
	for ($i = 1; $i<=12; $i++)
	{
		$row = $mysql_conn->query((sprintf("select charge from saldo where year(month)=%d and number_flat=%d and month(month)=%d", $year, $value, $i)))->fetch_assoc();
		$charge = ($row['charge']!=NULL)? $row['charge']:0;
		$total_charges+=$charge;
		echo "<th>".$charge."</th>";
	};
	echo "<th>".$total_charges."</th>";
	$end_saldo = sum_saldo($mysql_conn, $value, $year+1);
	if (is_null($curr_saldo)){
		$end_saldo =  "NULL";
	};
	echo "<th rowspan='2'>".$end_saldo."</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<th>charges</th>";
	for ($i = 1; $i<=12; $i++)
	{
		$row = $mysql_conn->query((sprintf("select payment from saldo where year(month)=%d and number_flat=%d and month(month)=%d", $year, $value, $i)))->fetch_assoc();
		$payment = ($row['payment']!=NULL)? $row['payment']:0;
		$total_payments+=$payment;
		echo "<th>".$payment."</th>";
	};
	echo "<th>".$total_payments."</th>";
	echo "</tr>";
	echo "</tbody>";
	echo "</table>";
};
?>