<?php
require_once __DIR__ . '/../Core/AModel.php';

class MCategories extends AModel
{
    public $table = 'categories';

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
