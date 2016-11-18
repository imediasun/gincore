<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';
require_once __DIR__ . '/../../Models/CategoriesTree.php';

/**
 * Class import_items
 *
 * @property ItemsInterface $provider
 * @property MGoods Goods
 * @property MCategories Categories
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
    public $uses = array(
        'Goods',
        'Categories'
    );

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
        $results = array();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $title = $this->provider->getTitle($row);
                $category = $this->getCategoryId($this->provider->getCategories($row), $this->categoriesTree);
                if (empty($category['id'])) {
                    $results[] = array(
                        'state' => false,
                        'id' => null,
                        'title' => $this->provider->getTitle($row),
                        'message' => l('У товара отсутствует категория'),
                    );
                    continue;
                }

                if ($id = $this->isValidTitle($title, $this->availableItems, true)) {
                    $this->items[$id] = $title;
                    $results[] = array(
                        'state' => false,
                        'id' => $id,
                        'title' => $this->provider->getTitle($row),
                        'message' => l('Товар с таким именем уже существует'),
                    );
                    continue;
                }
                if (!empty($category['warning'])) {
                    $results[] = array(
                        'state' => false,
                        'id' => $id,
                        'title' => $this->provider->getTitle($row),
                        'message' => $category['warning'],
                    );
                }

                $results[] = $this->createNewItem($this->userId, $row, $category['id'], $this->getManagerId());
            }
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
        );
    }

    /**
     * @param $itemCategories
     * @param $categories
     * @return array
     */
    public function getCategoryId($itemCategories, $categories)
    {
        $getId = function ($branch, $category, $parentId) {
            if (array_key_exists($category, $branch)) {
                $categoryId = $branch[$category]['id'];
            } else {
                $categoryId = $this->createCategory($category, $parentId);
            }
            return $categoryId;
        };
        $warning = '';
        $categoryId = null;
        $branch = $categories;
        try {
            foreach ($itemCategories as $id => $category) {
                if (!empty($category)) {
                    if ($id != 0) {
                        $branch = $this->getBranch($branch, $categoryId);
                        if (isset($branch['subcategories'])) {
                            $branch = $branch['subcategories'];
                        }
                    }
                    $categoryId = $getId($branch, $category, $categoryId);
                }
            }
        } catch (Exception $e) {
            Log::dump($e->getMessage());
            // add exception message to log
        }
        return array(
            'id' => $categoryId,
            'warning' => $warning
        );
    }

    /**
     * @param $title
     * @param $items
     * @param bool $return_id
     * @return bool
     */
    public function isValidTitle($title, $items, $return_id = false)
    {
        if ($return_id) {
            return isset($items[$title]) ? $items[$title]['id'] : false;
        } else {
            return !isset($items[$title]);
        }
    }

    /**
     * @param $row
     * @return string
     */
    public function get_result_row($row)
    {
        return '<td>' . $row['id'] . '</td>' .
        '<td>' . $row['title'] . '</td>' .
        '<td>' . $row['message'] . '</td>';
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
        $categoriesTree = new MCategoriesTree();
        return $categoriesTree->buildTreeWithTitle($categoriesTree->getCategoriesIdWithParent(), 0);
    }

    /**
     * @return array
     */
    public function getAvailableItems()
    {
        $items = $this->all_configs['db']->query('SELECT {goods}.id, {goods}.title FROM {goods} WHERE `deleted`=0')->assoc();
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
     * @param $row
     * @param $userId
     * @return mixed
     */
    public function insertNewItem($row, $userId)
    {
        $title = $this->provider->getTitle($row);
        return $this->Goods->insert(array(
            'title' => $title,
            'secret_title' => '',
            'url' => transliturl($title),
            'avail' => 1,
            'price' => $this->provider->getPrice($row),
            'article' => '',
            'author' => $userId,
            'price_purchase' => $this->provider->getPurchase($row),
            'price_wholesale' => $this->provider->getWholesale($row),
            '`type`' => $this->provider->getType($row),
            'vendor_code' => $this->provider->getVendorCode($row)
        ));
    }

    /**
     * @param $userId
     * @param $row
     * @param $categoryId
     * @param $managerId
     * @return array
     */
    public function createNewItem($userId, $row, $categoryId, $managerId)
    {
        $modId = $this->all_configs['configs']['products-manage-page'];

        if (!empty($userId) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            $itemId = $this->insertNewItem($row, $userId);
            if ($itemId > 0) {
                $this->items[$itemId] = $this->provider->getTitle($row);
                $this->setCategory($itemId, $categoryId);
                $this->addToLog($userId, 'create-goods', $modId, $itemId);

                if (!empty($managerId) && $this->setManager($managerId, $itemId)) {
                    $this->addToLog($userId, 'add-manager', $modId, $managerId);
                }
                $this->sendNotice($this->getContent($this->items));
                return array(
                    'state' => true,
                    'id' => $itemId,
                    'title' => $this->provider->getTitle($row),
                    'message' => l('Товар добавлен в базу')
                );
            }
        }

        return array(
            'state' => false,
            'id' => null,
            'title' => $this->provider->getTitle($row),
            'message' => l('Ошибка, Вам не хватает прав.')
        );
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
     * @return null|string
     */
    private function getManagerId()
    {
        return $this->userAsManager ? $this->userId : null;
    }

    /**
     * @param $title
     * @param $parentId
     * @return int
     * @throws Exception
     */
    private function createCategory($title, $parentId)
    {
        $url = transliturl($title);
        if ($this->Categories->query('SELECT count(*) FROM {categories} WHERE url=?', array($url))->el()) {
            $url = $url . '-' . mt_rand(1, 10);
        }
        $categoryId = $this->Categories->insert(array(
            'title' => $title,
            'url' => $url,
            'content' => '',
            'parent_id' => (int)$parentId,
            'avail' => 1
        ));

        if (empty($categoryId)) {
            throw new Exception('Category not created');
        }
        $this->categoriesTree = $this->getCategoriesTree();
        $modId = $this->all_configs['configs']['categories-manage-page'];
        $this->addToLog($this->userId, 'create-category', $modId, $categoryId);
        return $categoryId;
    }

    /**
     * @param $categoriesTree
     * @param $categoryId
     * @return array
     */
    private function getBranch($categoriesTree, $categoryId)
    {
        if (empty($categoryId)) {
            return $categoriesTree;
        }
        if (!empty($categoriesTree)) {
            foreach ($categoriesTree as &$category) {
                if ($category['id'] == $categoryId) {
                    return $category;
                }
                $branch = $this->getBranch($category['subcategories'], $categoryId);
                if (!empty($branch)) {
                    return $branch;
                }
            }
        }
        return array();
    }

    /**
     * @return string
     */
    public function getImportForm()
    {
        return $this->view->renderFile('import/forms/items');
    }

    /**
     *
     */
    public function example()
    {
        $data = db()->query('
            SELECT 
                g.title as title, c.title as c_title, "" as subcat1, "" as subcat2, "" as subcat3, "" as subcat4, 
                g.price/100, g.price_purchase/100, g.vendor_code, g.type
            FROM {goods} as g 
            JOIN {category_goods} as cg ON cg.goods_id=g.id
            JOIN {categories} as c ON c.id=cg.category_id
            LIMIT 2
        ')->assoc();
        foreach ($data as &$item) {
            $item['type'] = $item['type'] === GOODS_TYPE_SERVICE ? lq('Да') : lq('Нет');
        }
        return $this->provider->example($data);
    }
}