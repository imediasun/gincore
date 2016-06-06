<?php
require_once __DIR__ . '/../Core/AModel.php';

class MCategories extends AModel
{
    public $table = 'categories';

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
            'type'
        );
    }
}
