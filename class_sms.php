<?php


class Sms {
    /**
      * Отправка СМ, используя турбо смс
      *
      * @param <type> $msg
      * @param <type> $phone
      * @return <type>
      */
	  
	public function SendSms($msg, $phone, $spell_count = 1){
		global $settings,$db;
		$client = new SoapClient ('http://turbosms.in.ua/api/wsdl.html');
		// Авторизируемся на сервере
		$auth = Array (
			'login' => $settings['sms_gw_login'],
			'password' => $settings['sms_gw_pass']
		);
		$result = $client->Auth($auth);

		// Результат авторизации
		// echo $result->AuthResult . '<br />';

		if ($settings['sms_balance']<20)
			send_mail($settings['content_email'], 'Запас смс очень мал', 'Остаток: '.$settings['sms_balance']);

		$sms = Array (
			'sender' => $settings['sms_gw_sender'],
			'destination' => '+'.$phone,
			'text' => $msg
		);

		$result = $client->SendSMS ($sms);

		$this->send_notify($phone, '<pre>'.print_r($sms, true).'</pre> <hr>'.'<pre>'.print_r($result, true).'</pre>');
		$this->count_plus_one_sms($spell_count);

		
		// output to text file				
		$filename = 'sms_log.txt';
		$log = "смс на номер ".$phone."\n"."Результат: ".$result->SendSMSResult->ResultArray['0']."\n".$msg."\n Баланс: ".$settings['sms_balance']."\n-----------------------\r\n";
		file_put_contents($filename, $log, FILE_APPEND);
		// $handle = fopen($filename, 'a');
		// fwrite($handle, $log);
		// fclose($handle);
		
		return true;

	}

	private function count_plus_one_sms($cnt = 1){
		global $db;
		$db->query('UPDATE {settings} SET value=value-?i WHERE name="sms_balance"', array($cnt));
//		$db->query("UPDATE {settings} SET `value`=`value`+1 WHERE `name`='sms_sent'");

	}
	private function send_notify($phone, $msg){
//		send_mail('ar@fon.in.ua', 'смс отправлена на номер '.$phone, $msg);
	
	}

	 
}

?>