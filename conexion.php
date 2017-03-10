<?php 
$cnx = mysqli_connect("localhost" , "root" , "hola123.,") or die('No se pudo conectar : ' . mysql_error());
mysqli_select_db($cnx, "adminUNAM");
?>