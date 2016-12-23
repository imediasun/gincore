<?php

require_once __DIR__ . '/abstract_gincore_import_provider.php';

/**
 * Class gincore_categories_with_subs
 *
 * @property  MCategories Categories
 */
class gincore_categories_with_subs extends abstract_gincore_import_provider
{
    public $cols = array();
    public $categories = array();
    public $uses = array(
        'Categories'
    );

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'parent_id' => lq('Родитель'),
            'title' => lq('Категория'),
        );
        $this->categories = db()->query('select id, title from {categories}', array())->assoc('title');
    }

    /**
     * @return array
     */
    public function get_cols()
    {
        return $this->cols;
    }

    /**
     * @return array
     */
    public function get_translated_cols()
    {
        return array(
            lq('Родитель'),
            lq('Подкатегория 1'),
            lq('Подкатегория 2'),
            lq('Подкатегория 3'),
            lq('Категория'),
        );
    }

    /**
     * @inheritdoc
     */
    public function check_format($header_row)
    {
        $this->header_row = array_flip($header_row);
        return true;
    }

    /**
     * @param $row
     * @return string
     */
    public function get_title($row)
    {
        $id = 0;
        $title = '';
        while (!empty($row[$id])) {
            $title = $row[$id];
            $id++;
        }
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $title : iconv('cp1251', 'utf8',
            trim($title));
    }


    /**
     * @param $row
     * @return int
     * @throws Exception
     */
    public function get_parent_id($row)
    {
        $id = 0;
        $parent = '';
        do {
            $previous = $parent;
            if (empty($row[$id + 1]) && $id === 0) {
                break;
            }
            $parent = (empty($this->codepage) || $this->codepage == 'utf-8') ? $row[$id] : iconv('cp1251', 'utf8',
                trim($row[$id]));
            if (empty($parent)) {
                throw new Exception(l('Пустая родительская категория'));
            }
            if (!key_exists($parent, $this->categories)) {
                $previous_id =isset($this->categories[$previous]) ? $this->categories[$previous]['id'] : 0;
                $this->categories[$parent] = array(
                    'id' => $this->createParentCategory($parent, $previous_id),
                    'title' => $parent
                );
            }
            $id++;
        } while (!empty($row[$id + 1]));
        return $this->categories[$parent]['id'];
    }

    /**
     * @param $title
     * @param $parent_id
     * @return bool|int
     */
    private function createParentCategory($title, $parent_id)
    {
        $this->categories[$title] = array(
            'id' => $this->Categories->insert(array(
                'title' => $title,
                'url' => transliturl($title),
                'content' => '',
                'parent_id' => $parent_id,
                'avail' => 1

            )),
            'title' => $title
        );
        return $this->categories[$title]['id'];
    }
}
