<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>NuevoTicket</title>
</head>
<body>
	<section>
		<form method="post" enctype="multipart/form-data" >
			<div id="info">
				<h2>Información de Contacto</h2>
				<input type="email" name="correo" id="correo" maxlength="60" placeholder="correo" />
				<input type="text" name="nombre" id="nombre" maxlength="60" placeholder="nombre" />
				<input type="number" name="telefono" id="telefono" maxlength="20" placeholder="telefono" />
				<input type="text" name="extencion" id="extencion" maxlength="5" placeholder="extención" />
			</div>
			<div id="problem">
				<h2>Datos del ticket</h2>
				<input type="text" name="resumen" id="resumen" maxlength="60" placeholder="resumen del problema" />
				<textarea name="problema" id="problema" cols="40" rows="10" placeholder="Detalles del problema"></textarea>
				<label for="archivo" id="upluadoF"> subir archivo
					<input type="file" name="archivo" id="archivo" multiple>
				</label>
				<input type="hidden" name="sitio" id="sitio" value="elSitioDesdeDondeSeEnvia" />
				<input type="submit" value="Generar ticket" name="new_ticket"/>
			</div>
		</form>
	</section>
</body>
	<style>
		*{ box-sizing: border-box; text-align: center;}

		section{
			position: relative;
			margin:0 auto;
			max-width: 450px;
			width: 100%;
			text-align: center;
			border:2px solid rgba(13, 71, 161, 1);
			border-radius: 5px;
			padding: 10px;
		}

		section h2{
			font-size: 23px;
			margin: 15px 0 0 0;
			color: #424242;
		}

		#info input[type="email"], #info input[type="text"], #info input[type="number"], #problem input[type="text"], #problem textarea{
			display: block;
			max-width: 350px;
			width: 100%;
			height: 30px;
			margin: 0 auto;
			outline: none;
			padding: 10px;
			margin-top: 15px;
		}
		#problem textarea{
			height: 70px;
			margin-bottom: 15px;
		}
		#problem input[type="file"]{
			display: none;
		}
		#problem #upluadoF{
			border: 1px solid #BDBDBD;
			display: inline-block;
			margin-bottom: 30px;
			padding: 6px 35px;
			/*border-radius: 5px;*/
			cursor: pointer;
			/*border: 1px solid rgba(13, 71, 161, 1);*/
			font-weight: bold;
			color: color: #BDBDBD;
		}
		

		#problem input[type="submit"]{
			display: block;
			margin: 0 auto;
			margin-bottom: 15px;

			border:2px solid red;
			
			margin-bottom: 30px;
			padding: 6px 35px;
			border-radius: 5px;
			cursor: pointer;
			border: 1px solid rgba(13, 71, 161, 1);
			font-weight: bold;
		}

		#problem input[type="submit"]:hover{
			background: rgba(21, 101, 192, 1);
			color:white;
			transition: all 1s ease;
		}
	</style>
</html>



<?php

	if(isset($_POST["new_ticket"]))
	{
		try {
			// incluimos la clase Ticket
			require_once "class.ticket.php";
			// creamos un objeto de tipo Ticket
			$o_ticket = new Ticket();
			// array asociativo que contiene url y llave de la api para generar reportes
			$config = array(
				'url'=>'http://localhost/osTicket/upload/api/http.php/tickets.json',
				'key'=>'F91C35B7BD82D71AA4B2E2F6191518EC'
			);
			
			/* Obtenemos los valores del formulario,
			   Validamos cada unos de los valores obtenidos con ayuda del metodo validate_string,
			   guardamso los datos en un array asociativo
			*/
			$data = array(
				'name' => $o_ticket->validate_string($_POST["nombre"], null, true), //NOMBRE
				'email' => $o_ticket->validate_string($_POST["correo"], null, false), //NOMBRE
				'phone' => $o_ticket->validate_string($_POST["telefono"], null, true).", ext ".$o_ticket->validate_string($_POST["extencion"], null, false), //TELEFONO
				'subject' => $o_ticket->validate_string($_POST["resumen"], null, false), //TITULO
				'message' => $o_ticket->validate_string($_POST["problema"], null, false), //MENSAJE
				'ip' => $_SERVER['REMOTE_ADDR'], //IP CLIENTE
				'topicId' => '1', //TOPIC
				'Site' => $o_ticket->validate_string($_POST["sitio"], null, false), //EJEMPLO DE CAMPO PERSONALIZADO
				'attachments' => array() //ARRELGO PARA ARCHIVOS
			);
			
			// obtenemos los archivos (si es que existe alguno)
			foreach ($_FILES as $file => $f){
				if (isset($f) && is_uploaded_file($f['tmp_name'])) {
					$nombre = $f["name"];
					$tipo = $f["type"];
					$ruta = $f['tmp_name'];
					$data['attachments'][] = array("$nombre" => 'data: '.$tipo.';base64,'.base64_encode(file_get_contents($ruta)));
				}
			}
			
			// validamos que existan las bibliotecas de CURL y JSON
			function_exists('curl_version') or die('CURL support required');
			function_exists('json_encode') or die('JSON support required');
			// agrrega 30 segundos al tiempo máximo de ejecución del script. No es necesari
			//set_time_limit(30);

			$code = $o_ticket->set_curl("http://localhost/osTicket/upload/", $config, $data);
			if ($code != 201){ die('Error al generar el ticket: '.$result); }
			else{ echo "<span>Ticket abierto con n&uacute;mero: ".$o_ticket->num_ticket."</span>"; }

		} catch (Exception $e) {
	    	echo "clase ticket no diponible";
		}
		// fin
	}

	 
?>






