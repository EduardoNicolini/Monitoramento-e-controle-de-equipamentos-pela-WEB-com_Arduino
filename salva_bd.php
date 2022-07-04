<?php

	include "conexao.php";

	$amper     = $_GET['irms'];
	$potencia  = $_GET['potencia'];
	$kwh       = $_GET['kwh'];
		
	
	$sql = " INSERT INTO sensores (amper, watts, kwh, data) VALUES (:amper, :watts, :kwh, :data) ";
	//$sql = " INSERT INTO  sensores SET amper = '$amper', quantidade = '$potencia', kwh = $kwh, data = current_date()"; 

	$stmt = $PDO->prepare($sql);

	$stmt->bindParam(':amper', $amper); 
	$stmt->bindParam(':watts', $potencia);
	$stmt->bindParam(':kwh', $kwh);
	$stmt->bindParam(':data', date("y/m/d"));

	if($stmt->execute()){

		echo "'Dados inseridos com sucesso!";

	}  
	else{

		echo "Erro ao inserir os dados";

	}    
?>