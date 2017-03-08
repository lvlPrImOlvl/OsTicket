#!/usr/bin/php -q
<?php
#########################################################################
###########################CONFIGURACIONES###############################
#########################################################################
#topicId -> Tema al cual ingresará el ticket, por el momento es estatico,
#pero creo que lopodemos ir variando con respecto al tipo de incidencia detectada

$settings = array
(
   'topicId' => 12,
   'subjectPrefix' => '[',
   'subjectSuffix' => ']',
   'reporterEmail' => 'test@domain.com',
   'reporterName' => 'bot',
   'apiURL' => 'http://127.0.0.1/osticket/upload/api/http.php/tickets.json',
   'apiKey' => '1712240E6A66B68B228DB30191869DE5'
);
#Si no se han ingresado las configuraciónes el programa no avanzará
if (!isset($settings))
{
  die ('$settings is not set. Aborting.');
}

#########################################################################
###########################QUITAR AL FINAL###############################
#########################################################################
if (count($argv) === 1)
{
  echo "\nUsage $argv[0] subject [messasge]\n\n";
  exit(1);
}
#########################################################################
###########################IMPLEMENTAR CON BD###############################
#########################################################################
$subject = $argv[1];
$message = isset($argv[2]) ? $argv[2] : null;
#########################################################################

#Se manda a llamar a la función crear ticket con el asunto y mensaje
createTicket($subject, $message);

function createTicket($subject, $message = null)
{
   #Dentro de esta función se usa la variable global settings, donde se guardo toda la configuración previa
   global $settings;
   $topicId = $settings['topicId']; 
   $reporterEmail = $settings['reporterEmail'];
   $reporterName = $settings['reporterName'];
   $reporterIP = gethostbyname(gethostname());

   #Aseguramos que el asunto NO esta vacio
   if (empty($subject))
   {
      echo ("No Subject provided. Not creating ticket.\n");
      return false;
   };

   #Si el mensaje esta vacio, asignamos como mensaje el asunto
   if (empty($message))
   {
      $message = $subject;
   }

   #Crreamos el asunto con la forma -> [asunto]
   $subject = $settings['subjectPrefix'] . $subject . $settings['subjectSuffix'];
   #Arreglo de datos que serán los que se enviarán a osticket
   $data = array
   (
      'name'      =>      $reporterName, 
      'email'     =>      $reporterEmail, 
      'phone' 	=>      '',  
      'subject'   =>      $subject,  
      'message'   =>      $message,  
      'ip'        =>      $reporterIP,
      'topicId'   =>      $topicId
  );

   #Comprobamos que el sistema tengo las herramientas necesarias
   function_exists('curl_version') or die('CURL support required');
   function_exists('json_encode') or die('JSON support required');
   set_time_limit(30);

   $ch = curl_init();
   #Configuramos la ulr a donde se enviaran los datos
   curl_setopt($ch, CURLOPT_URL, $settings['apiURL']);
   #Configuramos como 1 (True) para indicar que se hara una peticion post
   curl_setopt($ch, CURLOPT_POST, 1);
   #Configuracion de todos los campos que son enviados por post (nuestro arreglo data)
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
   #Configuramos el user agent
   curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.10');
   #Con FALSE no introduciremos en la salida el header 
   curl_setopt($ch, CURLOPT_HEADER, FALSE);
   #Array de campos a configurar para el header
   curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-API-Key: '.$settings['apiKey']));
   #False para no seguir ninguna redirección (Location) que envie el server como parte del header HTTP
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
   #True para devolcver el resultadoen formato de string 
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

   #Establecemos la sesion cURL
   $result=curl_exec($ch);
   #Para saber si la peticion fue exitosa, obtenemos informacion de la transferencia, en este caso, nos interesa
   #El http code que regresó
   $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   #Cerramos la 
   curl_close($ch);

   #Si el codigodevuelto por CURLINFO_HTTP_CODE es diferente a 201, fue por que hubo algun error
   if ($code != 201)
   {
      echo "Unable to create ticket with subject $subject: " .$result . "\n";
      return false;
   }

   #En caso contrario, todo salio bien! :D, por lo que al obtener el resultado de la transferencia, obtenremos el 
   #numero del ticket creado
   $ticketId = (int)$result;
   echo "Ticket '$subject' created with id $ticketId\n";
   return $ticketId;
} // End of createTicket()