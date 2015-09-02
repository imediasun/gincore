<?php

class turbosms
{

    /**
     * клиент
     */
    private $client;

    /**
     * URI of the WSDL file
     */
    private $wsdl = 'http://turbosms.in.ua/api/wsdl.html';

    public function __construct($login, $password)
    {
        // Подключаемся к серверу
        $this->client = new SoapClient($this->wsdl);

        // Авторизируемся на сервере
        return $this->auth($login, $password);
    }

    /**
     * Авторизация
     */
    private function auth($login, $password)
    {
        // Данные авторизации
        $auth = array(
            'login' => $login,
            'password' => $password,
        );
        // Авторизируемся на сервере
        $result = $this->client->Auth($auth);

        // Результат авторизации
        return $result->AuthResult;
    }

    /**
     * Просмотреть список доступных функций сервера
     */
    function functions()
    {
        return (array)$this->client->__getFunctions();
    }

    /**
     * Получаем количество доступных кредитов
     */
    function get_balance()
    {
        $result = $this->client->GetCreditBalance();

        return $result->GetCreditBalanceResult;
    }

    /**
     * Текст сообщения ОБЯЗАТЕЛЬНО отправлять в кодировке UTF-8
     */
    private function text($text)
    {
        $encoding = mb_detect_encoding($text);

        if ($encoding != 'UTF-8') {
            $text = iconv($encoding, 'UTF-8', $text);
        }

        return trim($text);
    }

    private function phone($destination)
    {
        return implode(',', (array)preg_replace('/[^+0-9,]+/', '', $destination));
    }
    /**
     * Отправляем сообщение
     */
    function send($sender, $destination, $text)
    {
        // Данные для отправки
        $sms = Array(
            // Подпись отправителя может содержать английские буквы и цифры. Максимальная длина - 11 символов.
            'sender' => $sender,
            // Номер указывается в полном формате, включая плюс и код страны
            'destination' => $this->phone($destination),
            // Текст сообщения ОБЯЗАТЕЛЬНО отправлять в кодировке UTF-8
            'text' => $this->text($text),
        );

        $result = $this->client->SendSMS($sms);

        // результат отправки.
        return (array)$result->SendSMSResult;
    }

    /**
     * Отправляем сообщение с WAPPush ссылкой
     **/
    function send_wappush($sender, $destination, $text, $wappush)
    {
        // Данные для отправки
        $sms = Array(
            // Подпись отправителя может содержать английские буквы и цифры. Максимальная длина - 11 символов.
            'sender' => $sender,
            // Номер указывается в полном формате, включая плюс и код страны
            'destination' => $this->phone($destination),
            // Текст сообщения ОБЯЗАТЕЛЬНО отправлять в кодировке UTF-8
            'text' => $this->text($text),
            // Ссылка должна включать http://
            'wappush' => $wappush,
        );

        $result = $this->client->SendSMS($sms);

        // результат отправки.
        return (array)$result->SendSMSResult;
    }

    /**
     * Запрашиваем статус конкретного сообщения по ID
     */
    function get_status($mess_id)
    {
        $sms = array('MessageId' => $mess_id);
        $status = $this->client->GetMessageStatus($sms);

        return $status->GetMessageStatusResult;
    }

    /**
     * Запрашиваем массив ID сообщений, у которых неизвестен статус отправки
     */
    function get_messages()
    {
        $result = $this->client->GetNewMessages();

        return (array)$result->GetNewMessagesResult;
    }
}