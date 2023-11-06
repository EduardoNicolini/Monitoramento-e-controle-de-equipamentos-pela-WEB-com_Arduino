 <?php

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
?>
