<?php
function insert_saldo($mysql_conn)
{
	$max_date = $mysql_conn->query("select max(t.mx) as result from ((select max(date) as mx from payments) union (select max(date) as mx from charges)) as t")->fetch_assoc()['result'];//получаем месяц до которого есть информация по платежам-долгам
	$min_date = $mysql_conn->query("select min(t.mn) as result from ((select min(date) as mn from payments) union (select min(date) as mn from charges)) as t")->fetch_assoc()['result'];
	$mass_flats = array();
	$res_flats = $mysql_conn->query("(select number_flat from payments) union (select number_flat from charges)")
	if (!is_null($max_date)){
		$res_flats->data_seek(0);
		while($row = $res->fetch_assoc()){
			array_push($mass_flats, $row["number_flat"]);
		};
		echo sprintf("max date: %s, min date: %s",$max_date, $min_date);
		foreach ($mass_flats as $key => $value) {
			$saldo = 0;
			for ($y_cur=explode('-',$min_date)[0], $m_cur = explode('-',$min_date)[1]; ($y_cur*12 + $m_cur)<=(explode('-',$max_date)[0]*12 + explode('-',$max_date)[1]); $m_cur++) 
			{
				$charge = $mysqli->query(sprintf("select cash from charges where data='%s-%s-01'"$y_cur,$m_cur))->fetch_assoc()['cash'];
				$payment = $mysqli->query(sprintf("select cash from payments where data='%s-%s-01'"$y_cur,$m_cur))->fetch_assoc()['cash'];
				$charge = ($charge!=NULL)? $charge:0; 
				$payment = ($payment!=NULL)?$payment:0;
				$saldo+=$charge-$payment;
				if ($mysqli->query(sprintf("insert saldo(cash, number_flat, date) value(%d, %d, '%d-%d-01')",$saldo,$value,$y_cur,$m_cur))
				{
					echo sprintf("insert %d, %d, '%d-%d-01' successful!<br>",$value, $saldo, $y_cur,$m_cur);
				}
				else
				{
					echo sprintf("insert %d, %d, '%d-%d-01' failure!<br>",$value, $saldo, $y_cur,$m_cur);
				}
				if ($m_cur==12){
					$m_cur=0;
					$y_cur+=1;
				};
			};
		};
	}
	else
	{
		echo "max,min date: NULL";
	}
};
$mysqli = new mysqli("localhost", "root", "fishing", "saldo_lab");
insert_saldo($mysqli);
$mysqli->close();
?>