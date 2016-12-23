<?php
require_once __DIR__ . '/../Core/AModel.php';

class MCategories extends AModel
{
    public $table = 'categories';

    /**
     * @param $id
     * @return array
     */
    public function isUsed($id)
    {
        try {
            $used = $this->query('SELECT count(*) FROM ?t WHERE parent_id=?i', array($this->table, $id))->el();
            if ($used) {
                throw new ExceptionWithMsg(l('В категории есть вложенные подкатегории. Сначала очистите категорию от подкатегорий, после чего повторите попытку удаления'));
            }
            $used = $this->query('SELECT count(*) FROM {orders} WHERE category_id=?i', array($id))->el();
            if ($used) {
                throw new ExceptionWithMsg(l('Используется в заказах'));
            }
            $used = $this->query('SELECT count(*) FROM {crm_requests} WHERE product_id=?i', array($id))->el();
            if ($used) {
                throw new ExceptionWithMsg(l('Используется в запросах на ремонт'));
            }
            $used = $this->query('SELECT count(*) FROM {goods} WHERE category_for_margin=?i', array($id))->el();
            if ($used) {
                throw new ExceptionWithMsg(l('Используется в свойствах товаров'));
            }
            $used = $this->query('SELECT count(*) FROM {category_goods} WHERE category_id=?i', array($id))->el();
            if ($used) {
                throw new ExceptionWithMsg(l('В категории есть вложенные товары. Сначала очистите категорию от товаров, после чего повторите попытку удаления'));
            }
            $category = $this->query('SELECT * FROM {categories} WHERE id=?i', array($id))->row();
            if (in_array($category['url'], array(
                'spisanie',
                'prodazha',
                'vozvrat-postavschiku',
                'recycle-bin',
            ))) {
                throw new ExceptionWithMsg(l('Cистемные категории не подлежат удалению'));
            }

            $result = array(
                'used' => false
            );
        } catch (ExceptionWithMsg $e) {
            $result = array(
                'used' => true,
                'message' => $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getRecycleBin()
    {
        return $this->query('SELECT * FROM ?t WHERE url=?',
            array($this->table, 'recycle-bin'))->row();
    }

    /**
     * @param $categories
     * @return array
     */
    public function getParents($categories)
    {
        if (!is_array($categories)) {
            $categories = array($categories);
        }
        $parent = $this->getChildIds($categories);
        return array_merge($categories, $parent);
    }

    /**
     * @param $categoryIds
     * @return array
     */
    public function getChildIds($categoryIds)
    {
        if (!is_array($categoryIds)) {
            $categoryIds = array($categoryIds);
        }
        $child = $this->query("
            SELECT id FROM ?t as t 
            LEFT JOIN (SELECT DISTINCT parent_id FROM ?t) AS sub ON t.id = sub.parent_id
            WHERE t.parent_id in (?li) AND t.avail=1 AND NOT (sub.parent_id IS NULL OR sub.parent_id = 0)
            ", array($this->table, $this->table, $categoryIds))->col();
        if (!empty($child)) {
            $child = array_merge($child, $this->getChildIds($child));
        }
        return empty($child) ? array() : $child;
    }

    /**
     * @param $productId
     * @return array
     */
    public function getMarginCategoryByProductId($productId)
    {
        return $this->query('
        SELECT * 
        FROM ?t
        WHERE id in (SELECT category_id FROM {category_goods} WHERE goods_id=?i ORDER by id ASC)
        AND NOT (percent_from_profit=0 AND fixed_payment=0)
        LIMIT 1
        ', array($this->table, $productId))->row();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'title',
            'parent_id',
            'avail',
            'url',
            'prio',
            'content',
            'thumbs',
            'image',
            'cat-image',
            'page_content',
            'page_title',
            'page_description',
            'page_keywords',
            'date_add',
            'warehouses_suppliers',
            'information',
            'rating',
            'votes',
            'deleted',
            'percent_from_profit',
            'fixed_payment'
        );
    }
}
