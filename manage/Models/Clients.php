<?php

require_once __DIR__ . '/../Core/AModel.php';

class MClients extends AModel
{
    public $table = 'clients';

    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        return $this->query("SELECT * FROM ?t WHERE id=?i", array($this->table, $id))->row();
    }

    /**
     * @param $clientId
     * @return string
     */
    public function getClientCode($clientId)
    {
        $clientCode = $this->query('SELECT client_code FROM ?t WHERE id=?i', array($this->table, $clientId))->el();
        if (empty($clientCode)) {
            do {
                /** @todo алогоритм предложен Vitaly */
                if ($clientId <= 9) {
                    $clientCode = $clientId + 1;
                } else {
                    $clientCodeAsArray = str_split((string)($clientId + 1));
                    $clientCode = implode('', array_merge((array)array_pop($clientCodeAsArray), $clientCodeAsArray));
                }

                $has = $this->query('SELECT count(*) FROM ?t WHERE client_code=?',
                    array($this->table, $clientCode))->el();
            } while ($has != 0);
            $this->query('UPDATE ?q SET client_code=?i WHERE id=?i', array($this->table, $clientCode, $clientId));
        }
        return $clientCode;
    }
    
    /**
     * @param $post
     * @return array
     * @throws Exception
     */
    public function getClient($post)
    {
        require_once($this->all_configs['sitepath'] . '/shop/access.class.php');
        $access = new \access($this->all_configs, false);
        $client_phone_filtered = $access->is_phone($post['client_phone']);

        if (!$this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            throw new ExceptionWithMsg('У Вас нет прав');
        }
        $clientId = isset($post['client_id']) ? intval($post['client_id']) :
            (isset($post['clients']) ? intval($post['clients']) : 0);
        
        if (isset($post['clients']) && $clientId != 0) {
            return $this->getById($clientId);
        }
        if (empty($post['client_fio']) && empty($_POST['client_fio'])) {
            throw new ExceptionWithMsg(l('Укажите ФИО клиента'));
        }
        if (empty($post['client_phone']) && empty($_POST['client_phone'])) {
            throw new ExceptionWithMsg(l('Укажите телефон клиента'));
        }
        // создать клиента
        if (!$client_phone_filtered) {
            throw new ExceptionWithMsg(l('Введите номер телефона в формате вашей страны'));
        }
        $info = array(
            'phone' => $client_phone_filtered[0],
            'fio' => $_POST['client_fio']
        );
        if (!empty($_POST['address'])) {
            $info['legal_address'] = $_POST['address'];
        }
        if (!empty($_POST['email'])) {
            $info['email'] = $_POST['email'];
        }
        $u = $access->registration($info);
        if ($u['id'] <= 0) {
            throw new ExceptionWithMsg(isset($u['msg']) ? $u['msg'] : l('Ошибка создания клиента'));
        }
        return array(
            'id' => $u['id'],
            'phone' => $client_phone_filtered[0],
            'fio' => $_POST['client_fio']
        );
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'company_name',
            'inn',
            'kpp',
            'fax',
            'id',
            'phone',
            'ind',
            'email',
            'fio',
            'date_add',
            'pass',
            'person',
            'confirm',
            'legal_address',
            'institution',
            'birthday',
            'job',
            'works_phone',
            'position',
            'relationship',
            'childrens',
            'childrens_age',
            'counts_people_apartment',
            'education',
            'mobile',
            'identification_code',
            'passport',
            'issued_passport',
            'when_passport_issued',
            'registered_address',
            'payment',
            'shipping',
            'region',
            'city',
            'works_address',
            'residential_address',
            'office_id',
            'np_office_id',
            'reminder_password',
            'reminder_date',
            'credits_package',
            'contractor_id',
            'total_as_sum',
            'tags_id',
            'tag_id',
            'sms_code',
            'client_code'
        );
    }
}