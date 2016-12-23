<?php
require_once __DIR__ . '/../Core/AModel.php';

class MCategoriesTree extends AModel
{
    public $table = 'categories';

    /**
     * @param $categories
     * @param $parentId
     * @param $models
     * @return array
     */
    public function buildTree($categories, $parentId, $models)
    {
        $result = [];
        foreach ($categories as $id => $category) {
            if ($category['parent_id'] == $parentId) {
                $id = $category['id'];
                unset($categories[$id]);
                $result[$id] = in_array($id, $models) ? array() : $this->buildTree($categories, $id, $models);
            }
        }
        return $result;
    }

    /**
     * @param $categories
     * @param $parentId
     * @return array
     */
    public function buildTreeWithTitle($categories, $parentId)
    {
        $result = [];
        foreach ($categories as $id => $category) {
            if ($category['parent_id'] == $parentId) {
                $id = $category['id'];
                $title = isset($category['title']) ? $category['title'] : '';
                unset($categories[$id]);
                $result[$title] = array(
                    'id' => $id,
                    'subcategories' => $this->buildTreeWithTitle($categories, $id)
                );
            }
        }
        return $result;
    }

    /**
     * @param $tree
     * @return array
     */
    protected function child($tree)
    {
        $result = array();
        foreach ($tree as $id => $item) {
            if (empty($item)) {
                $result[] = $id;
            } else {
                $result += $this->child($item);
            }
        }
        return $result;
    }

    /**
     * @param $tree
     * @param $parent
     * @return array
     */
    protected function getChildBranch($tree, $parent)
    {
        $result = array();
        foreach ($tree as $item) {
            if (in_array($parent, array_keys($item))) {
                $result += $this->child($item[$parent]);
            } else {
                $result += $this->getChildBranch($item, $parent);
            }
        }
        return $result;
    }

    /**
     * @param $selectedCategories
     * @param $models
     * @return array
     */
    public function getChildren($selectedCategories, $models)
    {
        $categories = $this->getCategoriesIdWithParent();
        $tree = $this->buildTree($categories, 0, $models);

        $result = array();
        foreach ($selectedCategories as $selectedCategory) {
            $result += $this->getChildBranch($tree, $selectedCategory);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCategoriesIdWithParent()
    {
        return $this->query('SELECT id, parent_id, title FROM ?t group by parent_id, id',
            array($this->table))->assoc('id');
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
        );
    }
}