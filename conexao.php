<?php 
	$usuario = "root"; 
	$senha = "";

	// COMANDO try TENTA FAZER UMA CONEXÃO COM O Bando de Dados
	try {
		$PDO = new PDO("mysql:host=localhost;dbname=sensor_arduino;charset=utf8", $usuario, $senha);
	}catch(PDOException $e){ // COMANDO catch CASO NÃO CONSIGA UMA CONEXÃO COM O BD MOSTRA UM ERRO
		echo "Falha: ". $e->getMessage();
	}
?>
