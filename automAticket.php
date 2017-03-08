<?php
echo "Leyendo el archivo:";
?><br><br><?php
$dataCSV = array();
$dataAdminUNAM = array();
$i = 0;
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
if (($fichero = fopen("prueba.csv", "r")) !== FALSE)
{
	include_once "conexion.php";
    while (($lineas = fgetcsv($fichero, 1000)) !== FALSE)
    {
    	$dataCSV[$i] = $lineas;
    	$i++;

    	$ipSeparada= explode(".",$lineas[1]);
    	$ip = $ipSeparada[0] . "." . $ipSeparada[1] . "." . $ipSeparada[2];
    	$host = $ipSeparada[3];
    	echo "DATOS:"
    	?><br><br><?php
    	echo "Para la IP $lineas[1]";
    	?><br><?php

		
		$result = mysqli_query($cnx, "SELECT * FROM administrador where segmento_red='$ip' and (rango_inicial<='$host' or rango_final>='$host')") or die ('No se pudo realizar la consulta : ' . mysql_error());

		mysqli_data_seek ($result, 0);

		$extraido= mysqli_fetch_array($result);

		echo "- Id: ".$extraido['administrador_id']."<br/>";
		echo "- Nombre: ".$extraido['nombre']."<br/>";
		echo "-----------------------------------";
		?><br><?php
		mysqli_free_result($result);
		mysqli_close($link);
    	
    }
}

echo "FINAL";
/*?> <br><br><br><?php
foreach ($dataCSV as $key)
{
	echo $key[0];
	echo $key[1];
	echo $key[2];
}
*/
fclose($linea);
?>
