<?php

	include "conexao.php";

	$amper     = $_GET['irms'];
	$potencia  = $_GET['potencia'];
	$kwh       = $_GET['kwh'];
	
	$sql = " INSERT INTO sensores (amper, watts, kwh, data) VALUES (:amper, :watts, :kwh, :data) ";
	
	$stmt = $PDO->prepare($sql);

	$stmt->bindParam(':amper', $amper); 
	$stmt->bindParam(':watts', $potencia);
	$stmt->bindParam(':kwh', $kwh);
	$stmt->bindParam(':data', date("y/m/d"));

?>
