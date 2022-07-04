<!DOCTYPE HTML>
<html lang="pt-br">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

		<link rel="stylesheet" href="css/style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

		<title>Tabela do BD com Ajax</title>

	</head>
	<body onload="status();" >

		<div class="container">

			<div class="row">	

				<div class="page-header">

					<h1>
						Sistema de monitoramento e controle de cargas elétricas<br>
						<small>Desenvolvido por Eduardo Nicolini</small>
					</h1>

				</div>
			</div>

			<div class="row">

				<div class="col-md-3 well">

					<p class="margem_top">
						Leitura recebida do sensor de carga do Arduino.	
					</p>

					<p class="margem_top">
						<P>Status da conexão: <span id="status" class="label"></span><P>
					</p>

					<p class="margem_top">
						<h4>Corrente</h4>
						<P><strong><span class="fonte_dados" id="valor1" ></span></strong><P>
					</p>

					<p class="margem_top">
						<h4>Potencia (Watts) </h4>
						<P><strong><span class="fonte_dados" id="valor2"></span></strong><P>
					</p>

					<p class="margem_top">
						<h4>kilowatt hora (KWh)</h4>
						<P><strong><span class="fonte_dados" id="kwh"></span></strong><P>
					</p>

					<p class="margem_top">	

						<div class="btn-group">

						  	<h4>Status da rede

								<strong class="btn3">
									<button type="button" class="btn btn-default botaoEnvia" id="btn3">Atualizar</button>
								</strong>

							</h4>

						</div>

					</p>

					<p class="margem_top">

						<h4>Controle de carga</h4>

						<div class="btn-group btn_on_off">
						  	<button type="button" class="btn btn-default botaoEnvia" id="002">Liga</button>
						  	<button type="button" class="btn btn-default botaoEnvia" id="001">Desliga</button>
						</div>

						<span class="status_rele" id="resultRELE"></span><br/>

					</p>				
				</div>

				<div class="col-md-9">

					<h2 class="text-info">Registros de dados do sensor</h2>

					<h3>
						Selecione o mes desejado
						<input type="date" class="btn btn-default botaoEnvia" name="search_data" id="search_data" value='<?php echo date("Y-m-d"); ?>'>
					</h3>

					<div class="table-responsive">   

					  	<table class="table table-hover table-striped">

							<thead>
								<tr class="fonte_table">
									<th class="">Amper</th>
									<th class="">Watts</th>
									<th class="">Kwh</th>
								</tr>
							</thead>

							<tbody class="fonte_table" id="tabela">
								
							</tbody>

					  	</table>
				  	</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

		<script type="text/javascript" defer="defer" >

			// Parte que se comunica com o banco de dados
			var busca = 'busca.php';

			$(document).ready(function(){

				setInterval(Requisicao, 3000);
				
				function Requisicao(){

					$.ajax({    

						url: busca,     
						method: 'POST',
						data: {
                            				select_date : $('#search_data').val(), 
         					},
                        			cache : false,

						success: function(data){
							$('#tabela').html(data);
						}

					})

					$.ajax({

						url: busca,
						method: 'POST',     
						dataType: 'json',
						data: "mostra_valor", 

						success: function(data) {
							//Le e retorna o valor de leiura do sensor no arduino e envia em um ID para a TAG span acima
							$('#valor1').text(data.amper);
							$('#valor2').text(data.watts);
							$('#kwh').text(data.kwh);		
						}
					});
					return false;
				}
		  	});

		  	//Parte que se comunica com o Arduino
		  	// Para acessar pela rede local utilise o IP do Arduino na rede local
			var IP_Arduino = 'http://192.168.0.250:80';

			$(document).ready(function(){

				$('.botaoEnvia').click(function(){

					var valor = $(this).attr('ID');
			    		enviaDados(valor);
			  	});
			
				function enviaDados(dado_botao){	

					$.ajax({

						url: IP_Arduino,
						data: { 'acao': dado_botao},
						dataType: 'jsonp',
						crossDomain: true,
						jsonp: false,
						jsonpCallback: 'dados', // Retorna para mim os valores passos em Json no arduino

						success: function(data,status,xhr) {

							// posso ler dados e retoranar na pagina para avisar se a luz ta ligada ou desligada.
							console.log(data.rele);

							$('#resultRELE').text(statusReturn(data.rele)); 
						}
					});
					return false;
			  	}

			  	function statusReturn (valor) {

					if(valor == 0) {
						return "Desligada";
					}
					else if(valor == 1) {
						return "Ligada";	
					}
					else { return "Desconhecido";}
			  	}

				$('#btn3').click(function(){
			    		status_do_rele();
			  	});

				setInterval(status_do_rele, 60000);

				function status_do_rele(){

					$('#status').removeClass('label-success').addClass('label-warning');
					$('#status').text("Enviando Requisição...");

			    		$.ajax({

						url: IP_Arduino, 
						dataType: 'jsonp', 
						crossDomain: true, 
						jsonp: false,
						jsonpCallback: 'dados', // Retorna para mim os valores passos em Json no arduino

						success: function(data,status,xhr) {

							$('#status').removeClass('label-warning').addClass('label-success')

							$('#status').text("Requisição Recebida!");

							$('#resultRELE').text(statusReturn(data.rele));

						}
					});
					return false;
				} 
			});

		</script>
	</body>
</html>
