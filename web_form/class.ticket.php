<?php

	final class Ticket
  	{
      public $num_ticket;

      	public function validate_string($data, $length=null, $only_num_letter=false)
      	{
          
      		// convertimos algunos caracteres en entidades html
      		//$data = str_replace("<", "&#60", $data);
        	//$data = str_replace(">", "&#62", $data);
        	//$data = str_replace("#", "&#35", $data);
        	//$data = str_replace("\"", "&#34", $data);
        	//$data = str_replace("'", "&#39", $data);
        	//$data = str_replace("@", "&#64", $data);
        	//$data = str_replace("*", "&#42", $data);
        	//$data = str_replace(" ", "&#32", $data);
        	//$data = str_replace("!", "&#33", $data);
        	//$data = str_replace("$", "&#36", $data);
        	//$data = str_replace("-", "&#45", $data);
        	//$data = str_replace("&", "&#38", $data);

        	if($length != null){
        		if(strlen($data) > $length)
          			$data = substr($data, 0, $length); // recortara la cadena
        	}
        	if($only_num_letter === true){
        		// eliminamos lo que no es un numero o letra
        		$data = ereg_replace("[^A-Za-z0-9]", "", $data);
        	}
        	return $data;
      	}

        public function set_curl($url, $config, $data) {
          /*$config = array(
            'url'=>'http://localhost/osTicket/upload/api/http.php/tickets.json',
            'key'=>'F91C35B7BD82D71AA4B2E2F6191518EC'
          );*/
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_URL, $config['url']);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.8');
          curl_setopt($ch, CURLOPT_HEADER, FALSE);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-API-Key:'.$config['key']));
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($ch, CURLOPT_TIMEOUT, 180);
          $result=curl_exec($ch);
          $this->num_ticket = substr($result, -6);
          $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);

          return $code;
        }
  }

?>