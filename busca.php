 <?php
	// https://www.geekality.net/2010/06/27/php-how-to-easily-provide-json-and-jsonp/
	//header('content-type: application/json; charset=utf-8');

 	//Parte que monta a tabela com os dados dos sensores 
	include "conexao.php";
 	
	if(isset($_POST["select_date"])){   
          
		$sql = " SELECT * FROM sensores WHERE data = :data ";
        
        $stmt = $PDO->prepare($sql);
        $stmt->bindParam(':data', $_POST["select_date"]);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as $val_sensor){
            
            echo "

                <tr> 
                    <td >".$val_sensor['amper']."</td>
                    <td >".$val_sensor['watts']."</td>
					<td >".$val_sensor['kwh']."</td>
                </tr>
                
            ";

        }
    
	}
    
	//Parte que busca o ultimo registro no banco de dados
	/*
		Primeiro registro de uma tabela no MySQL

		SELECT * FROM table ORDER BY id LIMIT 1;
		
		Último registro de uma tabela MySQL

		SELECT * FROM table ORDER BY id DESC LIMIT 1;
	*/ 
	
	if(isset($_POST["mostra_valor"])){ 

		$sql = " SELECT * FROM sensores ORDER BY id DESC LIMIT 1 ";  
		
		$stmt = $PDO->prepare($sql);
		$stmt->execute();
		$data_sensor = $stmt->fetch(PDO::FETCH_ASSOC);
		
		echo json_encode(
			array(  
				
				'amper'=> $data_sensor['amper'] ?? 0,
				'watts'=> $data_sensor['watts'] ?? 0,
				'kwh' => $data_sensor['kwh'], 
			
			)
		);
	}
	/*
	$stmt = $PDO->prepare($sql);
  	$stmt->execute();

  	$resultado = array();

  	$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

	$json = json_encode($resultado);

	echo isset($_GET['callback']) ? "{$_GET['callback']}($json)" : $json;
	*/
?>