<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once __DIR__ . '/../utils/translate.php';
require_once $this->all_configs['sitepath'] . 'mail.php';

class import_items extends abstract_import_handler
{
    /** @var  array */
    protected $categories;
    /** @var  array */
    protected $availableItems;
    protected $sendNotice = false;
    protected $items = array();
    public $userAsManager = true;
    protected $userId;

    /**
     * @inheritdoc
     */
    public function __construct($all_configs, $provider, $import_settings)
    {
        parent::__construct($all_configs, $provider, $import_settings);
        $this->categories = array_flip($this->getCategories());
        $this->availableItems = array_flip($this->getAvailableItems());
        $this->userId = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $this->userAsManager = isset($this->import_settings['accepter_as_manager']) && $this->import_settings['accepter_as_manager'];
    }

    /**
     * @param $rows
     * @return array
     */
    public function run($rows)
    {
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $title = $this->provider->get_title($row);
                $categoryId = $this->getCategoryId($this->provider->get_category($row), $this->categories);
                if (!empty($categoryId) && $this->isValidTitle($title, $this->availableItems)) {
                    $this->createNewItem($this->userId, $title, $categoryId, $this->getManagerId());
                }
            }
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($this->items)
        );
    }

    /**
     * @param $category
     * @param $categories
     * @return int
     */
    public function getCategoryId($category, $categories)
    {
        return isset($categories[$category]) ? $categories[$category] : null;
    }

    /**
     * @param $title
     * @param $items
     * @return bool
     */
    public function isValidTitle($title, $items)
    {
        return !isset($items[$title]);
    }

    /**
     * @todo Implement get_result_row() method.
     *
     * @param $row
     * @return string
     */
    protected function get_result_row($row)
    {
        return "<td>" . $row . "</td>";
    }

    /**
     * @param $array
     * @return array
     */
    protected function toFlatArray($array)
    {
        $result = array();
        if (!empty($array)) {
            foreach ($array as $item) {
                $result[$item['id']] = $item['title'];
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $categories = $this->all_configs['db']->query('SELECT {categories}.id, {categories}.title FROM {categories}')->assoc();
        return $this->toFlatArray($categories);
    }

    /**
     * @return array
     */
    public function getAvailableItems()
    {
        $items = $this->all_configs['db']->query('SELECT {goods}.id, {goods}.title FROM {goods}')->assoc();
        return $this->toFlatArray($items);
    }

    /**
     * @return string
     */
    public function getDefaultManager()
    {
        $managers = $this->getManagers();
        return empty($managers) ? '' : $managers[0]['manager'];
    }

    /**
     * @return array
     */
    public function getManagers()
    {
        return $this->all_configs['db']->query('
                SELECT u.id, u.login, m.user_id as manager FROM {users} as u
                LEFT JOIN {users_roles} as r ON u.role=r.id
                LEFT JOIN {users_role_permission} as rp ON rp.role_id=r.id
                RIGHT JOIN (SELECT id FROM {users_permissions} WHERE link="external-marketing")p ON p.id=rp.permission_id
                LEFT JOIN {users_goods_manager} as m ON m.user_id=u.id
                WHERE u.avail=1 GROUP BY u.id')->assoc();
    }

    /**
     * @param $title
     * @param $userId
     * @return mixed
     */
    public function insertNewItem($title, $userId)
    {
        return $this->all_configs['db']
            ->query('INSERT INTO {goods} (title, secret_title, url, avail, price, article, author, type) VALUES (?, ?, ?n, ?i, ?i, ?, ?i, ?i)',
                array($title, '', translate::toURL(trim($title)), 1, 100, '', $userId, 1), 'id');
    }

    /**
     * @param $userId
     * @param $title
     * @param $categoryId
     * @param $managerId
     * @return bool
     */
    public function createNewItem($userId, $title, $categoryId, $managerId)
    {
        $modId = $this->all_configs['configs']['products-manage-page'];

        if (!empty($userId) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            $itemId = $this->insertNewItem($title, $userId);
            if ($itemId > 0) {
                $this->items[$itemId] = $title;
                $this->setCategory($itemId, $categoryId);
                $this->addToLog('create-goods', $userId, $modId, $itemId);

                if (!empty($managerId) && $this->setManager($managerId, $itemId)) {
                    $this->addToLog('add-manager', $userId, $modId, $managerId);
                }
                $this->sendNotice($this->getContent($this->items));
                return true;
            }
        }

        return false;
    }

    /**
     * @param $managerId
     * @param $itemId
     */
    private function setManager($managerId, $itemId)
    {
        return $this->all_configs['db']->query('INSERT IGNORE INTO {users_goods_manager} SET user_id=?i, goods_id=?i',
            array(intval($managerId), $itemId))->ar();
    }

    /**
     * @param $items
     * @return mixed|string
     */
    public function getContent($items)
    {
        $content = l('Создан новый товар');
        foreach ($items as $id => $title) {
            $content .= ' <a href="' . $this->all_configs['prefix'] . 'products/create/' . $id . '">';
            $content .= htmlspecialchars(trim($title)) . '</a></br>';
        }
        return $content;
    }

    /**
     * @param $content
     */
    private function sendNotice($content)
    {
        $mailer = new Mailer($this->all_configs);
        if ($this->sendNotice) {
            $mailer->send_message($content, l('Требуется обработка товарной позиции'), 'mess-create-product', 1);
        }
    }

    /**
     * @param $itemId
     * @param $categoryId
     */
    private function setCategory($itemId, $categoryId)
    {
        $this->all_configs['db']->query('INSERT IGNORE INTO {category_goods} (category_id, goods_id) VALUES (?i, ?i)',
            array($categoryId, $itemId));
    }

    /**
     * @param $userId
     * @param $modId
     * @param $itemId
     */
    private function addToLog($userId, $work, $modId, $itemId)
    {
        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($userId, $work, $modId, $itemId));
    }

    /**
     * @return null|string
     */
    private function getManagerId()
    {
        return $this->userAsManager ? $this->userId : null;
    }
}