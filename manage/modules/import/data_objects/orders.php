<?php

/**
 * Class order
 *
 * @todo не используется???
 */
class order
{
    private $messages = array();

    public function set_ccepted($accepted)
    {
        $this->date_add = $accepted;
    }

    public function set_accepter($accepter)
    {
        $this->accepter = $accepter;
    }

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_status($status)
    {
        $this->status_id = $status;
    }

    public function set_client_id($id)
    {
        $this->user_id = $id;
    }

    public function set_client_fio($fio)
    {
        $this->fio = $fio;
    }

    public function set_client_phone($phone)
    {
        $this->fio = $phone;
    }

    public function set_message($message)
    {
        $this->messages['info'] = $message;
    }

    public function set_error($message)
    {
        $this->messages['error'] = $message;
    }

    public function has_errors()
    {
        return isset($this->messages['error']);
    }
}