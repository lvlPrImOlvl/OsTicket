<?php
#Incluimos el modulo para enviar tickets
include 'sendTicket.php';
#Estructura del array de datos
/*
lineas[0] -> ASN
lineas[1] -> IP_ORIGEN
lineas[2] -> FECHA
lineas[3] -> NUMERO_EVENTO
lineas[4] -> DISPOSITIVO
lineas[5] -> IDENTIFICADOR
lineas[6] -> ALERTA
lineas[7] -> NOMBRE_ASN
*/

#shell_exec('sh ./getData.sh');
sleep(2);

#Guardamos en el log de osticket que se ejecuto el script
if (($log = fopen("osticket.log", "a")) !== FALSE)
{
	fwrite($log, 'Script ejecutado -> '.date("d-m-Y (H:i:s)",time()) . PHP_EOL);
}

#Si podemos leer el archivo, procedemos a analziarlo
if (($fichero = fopen("prueba.csv", "r")) !== FALSE)
{
	#Abrimos una conexión a la base de datos
	include_once "conexion.php";
	#Mientras que no encuentre lineas para leer, lo haremos...
    while (($lineas = fgetcsv($fichero, 1000)) !== FALSE)
    {
    	#Separamos la ip para poderla analizar mejor
    	$ipSeparada= explode(".",$lineas[1]);
    	$ip = $ipSeparada[0] . "." . $ipSeparada[1] . "." . $ipSeparada[2];
    	$host = $ipSeparada[3];
		
		#realizamos la consulta a la base de datos para obtener a los adminUNAM
		$result = mysqli_query($cnx, "SELECT * FROM administrador_ where segmento_red='$ip' and (rango_inicial<='$host' or rango_final>='$host')")
		or die ('No se pudo realizar la consulta : ' . mysql_error());

		#Nos posicionamos en el inicio de nuestro array de resultado
		mysqli_data_seek ($result, 0);

		#Lo pasamos a la variable extraido para poder utilizarlo como array
		$extraido= mysqli_fetch_array($result);

		#Liberamos la variable result
		mysqli_free_result($result);

		#El asunto se formara de acuerdo a la dependencia afectada y el ataque registrado
		$subject = "Incidencia en ". $extraido['dependencia'] . " -> $lineas[6]";
		#En el mensaje ira todo lo recabado del .CSV
		$message = "Problema encontrado en " . $lineas[1] . "\n\nDetalles:\n".
		"ASN = $lineas[0]\n" .
		"IP = $lineas[1]\n" .
		"FECHA = $lineas[2]\n" .
		"EVENTO = $lineas[3]\n" .
		"DISPOSITIVO = $lineas[4]\n" .
		"IDENTIFICADOR = $lineas[5]\n" .
		"ALERTA = $lineas[6]\n" .
		"NOMBRE ASN = $lineas[7]\n"	;

		#De acuerdo a la dependencia afectada se mandara el ticket a un agente
		if ($extraido['dependencia'] == 'PROTECO')
		{
			$topicId = 13;
		}
		else
		{
			$topicId = 14;
		}

		#Imprimimos un mensaje para saber que se esta procesando
		echo "Incidencia en $lineas[1]            ->            ";
		#Tomamos el correo del adminUNAM
		$correo = $extraido['correo'];
		#Tomamos el nombre del adminUNAM
		$nombre = $extraido['nombre'];
		#Si el nombre esta vacio, es por que la ip no se encontro, por lo que es desconocida
		#Si no lo esta, creamos el ticket.
		if ($extraido['nombre'] != "")
			createTicket($subject, $message,$topicId,$correo,$nombre);
		else if (($unknownIP = fopen("unknownIPS.log", "a")) !== FALSE)
		{
			fwrite($unknownIP, "Ip desconocida -> $lineas[1]" . PHP_EOL);
			echo "IP DESCONOCIDA, AGREGANDO AL LOG...";
		}
    }
}
#Guardamos en el log de osticket que se termino de ejecutar el script
if (($log = fopen("osticket.log", "a")) !== FALSE)
{
	fwrite($log, 'Script terminado -> '.date("d-m-Y (H:i:s)",time()) . PHP_EOL);
}
#Cerramos los ficheros que estamos manejando
fclose($fichero);
fclose($log);
fclose($unknownIP);
?>
