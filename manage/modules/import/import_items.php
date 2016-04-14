<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';
require_once __DIR__ . '/../../Models/CategoriesTree.php';

/**
 * Class import_items
 *
 * @property ItemsInterface $provider
 */
class import_items extends abstract_import_handler
{
    /** @var  array */
    protected $categoriesTree;
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
        $this->categoriesTree = $this->getCategoriesTree();
        $this->availableItems = $this->getAvailableItems();
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
                $title = $this->provider->getTitle($row);
                $categoryId = $this->getCategoryId($this->provider->getCategories($row), $this->categoriesTree);
                if (!empty($categoryId) && $this->isValidTitle($title, $this->availableItems)) {
                    $this->createNewItem($this->userId, $row, $categoryId, $this->getManagerId());
                }
            }
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($this->items)
        );
    }

    /**
     * @param $itemCategories
     * @param $categories
     * @return int
     */
    public function getCategoryId($itemCategories, $categories)
    {
        $categoryId = null;
        try {
            foreach ($itemCategories as $category) {

            }
//            if (!isset($categories[$parent])) {
//                $categoryId = $this->createCategory($parent, '', 0);
//                if (!empty($list[1])) {
//                    $subcategory = $list[1];
//                    if (isset($categories[$parent]['subcategories'][$subcategory])) {
//                        $categoryId = $categories[$parent]['subcategories'][$subcategory]['id'];
//                    } else {
//                        $categoryId = $this->createCategory($subcategory, $parent, $categories[$parent]['id']);
//                    }
//                }
//            } else {
//                $categoryId = $categories[$parent]['id'];
//
//            }
        } catch (Exception $e) {
            // add exception message to log
        }
        return $categoryId;
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
                $result[$item['title']] = array(
                    'id' => $item['id']
                );
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCategoriesTree()
    {
        $categoriesTree = new CategoriesTree();
        return $categoriesTree->buildTreeWithTitle($categoriesTree->getCategoriesIdWithParent(), 0);
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
     * @param $userId
     * @return mixed
     * @internal param $title
     * @internal param int $price
     * @internal param int $purchase
     * @internal param int $wholesale
     */
    public function insertNewItem($row, $userId)
    {
        $title = $this->provider->getTitle($row);
        return $this->all_configs['db']
            ->query('INSERT INTO {goods} (title, secret_title, url, avail, price, article, author, price_purchase, price_wholesale, type) VALUES (?, ?, ?n, ?i, ?i, ?, ?i, ?i, ?i, ?i)',
                array(
                    $title,
                    '',
                    transliturl($title),
                    1,
                    $this->provider->getPrice($row),
                    '',
                    $userId,
                    $this->provider->getPurchase($row),
                    $this->provider->getWholesale($row),
                    0
                ), 'id');
    }

    /**
     * @param $userId
     * @param $row
     * @param $categoryId
     * @param $managerId
     * @return bool
     */
    public function createNewItem($userId, $row, $categoryId, $managerId)
    {
        $modId = $this->all_configs['configs']['products-manage-page'];

        if (!empty($userId) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            $itemId = $this->insertNewItem($row, $userId);
            if ($itemId > 0) {
                $this->items[$itemId] = $this->provider->getTitle($row);
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
     * @param $work
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

    /**
     * @param $title
     * @param $parent
     * @param $parentId
     * @return int|null
     * @throws Exception
     */
    private function createCategory($title, $parent, $parentId)
    {
        $categoryId = null;

        if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')) {
            $categoryId = $this->all_configs['db']->query('INSERT INTO {categories}
                SET title=?, url=?, content=?, parent_id=?i, avail=?i',
                array($title, transliturl($title), '', $parentId, 1), 'id');

            if (empty($categoryId)) {
                throw new Exception('Category not created');
            }
            $this->appendCategory($categoryId, $title, $parent);
            $modId = $this->all_configs['configs']['categories-manage-page'];
            $this->addToLog($this->userId, 'create-category', $modId, $categoryId);
        }
        return $categoryId;
    }

    /**
     * @param $categoriesTree
     * @param $categoryId
     * @return array
     */
    private function getBranch($categoriesTree, $categoryId)
    {
        foreach ($categoriesTree as &$category) {
            if ($category['id'] == $categoryId) {
                return $category;
            }
            $branch = $this->getBranch($category, $categoryId);
            if (!empty($branch)) {
                return $branch;
            }
        }
        return array();
    }

    /**
     * @param $categoryId
     * @param $title
     * @param $parent
     */
    private function appendCategory($categoryId, $title, $parent)
    {
        if (empty($parent)) {
            $this->categoriesTree[$title] = array(
                'id' => $categoryId,
                'subcategories' => array()
            );
        } else {
            $branch = $this->getBranch($this->categoriesTree, $parent);
            if (!empty($branch)) {

                $branch['subcategories'][$title] = array(
                    'id' => $categoryId,
                    'subcategories' => array()
                );
            }
        }
    }
}