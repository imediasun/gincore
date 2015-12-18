<?php


$modulename[70] = 'categories';
$modulemenu[70] = l('Категории');
$moduleactive[70] = !$ifauth['is_2'];

class categories
{

    public $cat_id;
    public $cat_img;
    protected $all_configs;

    public $count_on_page;

    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();
        $this->cat_img = $this->all_configs['configs']['cat-img'];
        $this->cat_id = isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0 ?
            $this->all_configs['arrequest'][2] : null;

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас нет прав для просмотра категорий') . '</p></div>';
        }

        // если отправлена форма изменения продукта
        if ( !empty($_POST) ) {
            $this->check_post($_POST);
        }

        //if ($ifauth['is_2']) return false;

        if (!array_key_exists(1, $this->all_configs['arrequest'])) {// выбрана ли категория
            $input_html['mcontent'] = $this->show_categories();
        } else {
            $input_html['mmenu'] = '<div class="span3">' . $this->genmenu() . '</div>';
            $input_html['mcontent'] = '<div class="span9">' . $this->gencontent() . '</div>';
        }
    }

    private function check_post($post, $redirect = true)
    {
        $mod_id = $this->all_configs['configs']['categories-manage-page'];

        $title = (isset($post['title']) && !is_array($post['title'])) ? trim($post['title']) : '';
        $content = isset($post['content']) ? $post['content'] : '';
        //$parent_id = isset($post['parent_id']) ? intval($post['parent_id']) : 0;
        $avail = isset($post['avail']) ? 1 : null;
        $url = isset($post['url']) ? $this->transliturl($post['url']) : $this->transliturl($title);
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        // создание категории
        if ( isset($post['create-category']) && $this->all_configs['oRole']->hasPrivilege('create-filters-categories') ) {

            $category_url = $this->all_configs['db']->query('SELECT id FROM {categories} WHERE url=?', array($url))->el();

            if ( $category_url ) {
                if($redirect){
                    header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                        . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/?error=url');
                    exit;
                }else{
                    return array('error' => l('Категория с таким названием уже существует.'));
                }
            }

            $id = $this->all_configs['db']->query('INSERT INTO {categories}
                SET title=?,
                    url=?,
                    content=?,
                    parent_id=?i,
                    avail=?i',
                array(
                    $title,
                    $url,
                    $content,
                    intval($post['categories']),
                    $avail
                ), 'id');

            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'create-category', $mod_id, $id));

            //$new_url = $this->create_cat_url($id, '');генерация вложенного юрл

            if($redirect){
                header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                    . $this->all_configs['arrequest'][1] . '/' . $id);
                exit;
            }else{
                return $id;
            }
        } elseif ( isset($post['edit-seo']) && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ) {
            // редактирование seo
            $ar = $this->all_configs['db']->query('UPDATE {categories}
                SET page_title=?,
                    page_description=?,
                    page_keywords=?,
                    page_content=?
                WHERE id=?i',
                array(
                    trim($post['page_title']),
                    trim($post['page_description']),
                    trim($post['page_keywords']),
                    trim($post['page_content']),
                    intval($post['category_id'])
                ))->ar();
            if ( intval($ar) > 0 ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'edit-seo-category', $mod_id, intval($post['category_id'])));
            }

            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2]);
            exit;
        } elseif(isset($post['edit-context'])) {
            // редактирование контекстной рекламы
            if (isset($post['campaign_id'])) {
                foreach ($post['campaign_id'] as $system_id=>$campaign_id) {
                    $this->all_configs['db']->query('INSERT INTO {context_categories} (category_id, system_id, campaign_id)
                            VALUES (?i, ?i, ?) ON DUPLICATE KEY UPDATE campaign_id=VALUES(campaign_id)',
                        array($this->cat_id, $system_id, $campaign_id));
                }
            }
        } elseif ( isset($post['edit-category']) && $this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ) {
            // редактирование категории
            if( intval($post['id']) < 1 )
                return false;

            if ( isset($_FILES['thumbs']) && $_FILES['thumbs']['error'] < 1 && $_FILES["thumbs"]["size"] > 0 && $_FILES["thumbs"]["size"] < 1024*1024*1 &&
                ($_FILES["thumbs"]["type"] == "image/gif" || $_FILES["thumbs"]["type"] == "image/jpeg"
                    || $_FILES["thumbs"]["type"] == "image/jpg" || $_FILES["thumbs"]["type"] == "image/png")) {
                list($width, $height, $type, $attr) = getimagesize($_FILES["thumbs"]["tmp_name"]);
                $path_parts = full_pathinfo($_FILES["thumbs"]["name"]);
                if ($width == 30 && $height == 30) {
                    if ( move_uploaded_file($_FILES["thumbs"]["tmp_name"],
                        $this->all_configs['sitepath'] . $this->cat_img . $url . '.' . $path_parts['extension']) ) {

                        $ar = $this->all_configs['db']->query('UPDATE {categories}
                            SET thumbs=?
                            WHERE id=?i',
                            array($url . '.' . $path_parts['extension'], intval($post['id'])
                            ))->ar();
                        if( intval($ar) > 0 ) {
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'edit-category-thumbs', $mod_id, intval($post['id'])));
                        }
                    }
                }
            }
            if ( isset($_FILES['image']) && $_FILES['image']['error'] < 1 && $_FILES["image"]["size"] > 0 && $_FILES["image"]["size"] < 1024*1024*10 &&
                $_FILES["image"]["type"] == "image/png") {
                if ( move_uploaded_file($_FILES["image"]["tmp_name"],
                    $this->all_configs['sitepath'] . $this->cat_img . $url . '_image.png')) {

                    $ar = $this->all_configs['db']->query('UPDATE {categories}
                        SET image=?
                        WHERE id=?i',
                        array( $url . '_image.png', intval($post['id'])
                        ))->ar();
                    if( intval($ar) > 0 ) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'edit-category-image', $mod_id, intval($post['id'])));
                    }
                }
            }
            if ( isset($_FILES['cat-image']) && $_FILES['cat-image']['error'] < 1 && $_FILES["cat-image"]["size"] > 0 && $_FILES["cat-image"]["size"] < 1024*1024*1 &&
                ($_FILES["cat-image"]["type"] == "image/gif" || $_FILES["cat-image"]["type"] == "image/jpeg"
                    || $_FILES["cat-image"]["type"] == "image/jpg" || $_FILES["cat-image"]["type"] == "image/png")) {

                $path_parts = full_pathinfo($_FILES["cat-image"]["name"]);
                if ( move_uploaded_file($_FILES["cat-image"]["tmp_name"],
                    $this->all_configs['sitepath'] . $this->cat_img . $url . '_cat.' . $path_parts['extension'])) {

                    $ar = $this->all_configs['db']->query('UPDATE {categories} SET `cat-image`=? WHERE id=?i',
                        array($url . '_cat.' . $path_parts['extension'], intval($post['id'])))->ar();
                    if( intval($ar) > 0 ) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'edit-category-cat-image', $mod_id, intval($post['id'])));
                    }
                }
            }

            $category_url = $this->all_configs['db']->query('SELECT id FROM {categories} WHERE url=? AND id<>?', array($url, intval($post['id'])))->el();

            if ( $category_url ) {
                header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                    . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/?error=url');
                exit;
            }

            $ar = $this->all_configs['db']->query('UPDATE {categories}
                        SET title=?,
                            url=?,
                            content=?,
                            parent_id=?i,
                            prio=?i,
                            warehouses_suppliers=?,
                            information=?,
                            avail=?i,
                            rating=?,
                            votes=?
                        WHERE id=?i',
                array(
                    $title,
                    $url,
                    $content,
                    intval($post['categories']),
                    $post['prio'],
                    isset($post['warehouses_suppliers']) ? trim($post['warehouses_suppliers']) : '',
                    isset($post['information']) ? trim($post['information']) : '',
                    $avail,
                    isset($post['rating']) ? trim($post['rating']) : 0,
                    isset($post['votes']) ? trim($post['votes']) : 0,
                    intval($post['id'])
                ))->ar();
            if ( intval($ar) > 0 ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'edit-category', $mod_id, intval($post['id'])));
            }
            /*// если изменена вложенность категории
            if( key($post['parent_id']) != $post['parent_id'][key($post['parent_id'])] )
            {
                $this->all_configs['db']->query('DELETE FROM {filter_category} WHERE category_id=?i', array(key($post['parent_id'])));
                return false;
            }*/
            //$new_url = $this->create_cat_url(intval($post['id']), '');генерация вложенного юрл
            
            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/'
                . $this->all_configs['arrequest'][1] . '/' . intval($post['id']));
            exit;
        }
    }
    /* генерация вложенного юрл при редактировании категорий
     * account function create_cat_url($id)
    {
        global $this->all_configs['arrequest'], $this->all_configs['db'], $this->all_configs['path'], $this->all_configs['prefix'],$this->all_configs['sitepath'];
        static $url;
        $r = $this->all_configs['db']->query('SELECT url, parent_id FROM {categories} WHERE id=?i', array($id), 'row');
        $url = $r['url'].'-'.$url;

        if( $r['parent_id'] > 0 )
        {
            $this->create_cat_url($r['parent_id'], $url);
        }
        return substr($url, 0, strlen($url)-1);
    }*/
    private function gencreate($name = '', $ajax = false)
    {
        if ( !$this->all_configs['oRole']->hasPrivilege('create-filters-categories') )
            return '<p  class="text-error">' . l('У Вас нет прав для создания новой категории') . '</p>';

        $attr = 'form method="post"';
        $form_close = 'form';
        if($ajax){
            $attr = 'div class="emulate_form ajax_form" data-callback="select_typeahead_device" data-method="post" '
                    .'data-action="'.$this->all_configs['prefix'].'categories/ajax/?act=create_new"';
            $form_close = 'div';
        }
        
        // строим форму добавления категории
        $category_html = '<'.$attr.'><fieldset><legend>' . l('Добавление новой категории') . ' (' . l('название устройства') . ')</legend>';
        if ( isset($_GET['error']) && $_GET['error'] == 'url')
            $category_html .= '<p class="text-error">' . l('Категория с таким названием уже существует') . '</p>';
        $category_html .= '<div class="form-group"><label>' . l('Название') . ':</label>
            <input autocomplete="off" placeholder="' . l('Укажите название устройства или категории. Пример: IPhone 5') . '" class="form-control global-typeahead" data-anyway="1" data-table="categories" name="title" value="'.($name ? htmlspecialchars($name) : '').'" /></div>';
        $category_html .= '<div class="form-group"><div class="checkbox"><label>
            <input name="avail" type="checkbox">' . l('Активность') . '</label></div></div>';
        $category_html .= '<div class="form-group"><label>' . l('Высшая (родительская) категория') . ':</label>
            ' . typeahead($this->all_configs['db'], 'categories', false, 0, 1, 'input-large','', '', 
                          false, false, '', false, l('Укажите название высшей категории или оставьте пустым. Пример: Iphone')) . '</div>';
        $category_html .= '<div class="form-group"><label>' . l('Описание') . ':</label>
            <textarea placeholder="' . l('краткое описание') . '" name="content" class="form-control" rows="3"></textarea></div>';

        $category_html .= '
            <div class="form-group">
                <input class="btn btn-primary" type="submit" value="' . l('Создать') . '" name="create-category" />
                '.($ajax ? '<button type="button" class="btn btn-default hide_typeahead_add_form">' . l('Отмена') . '</button>' : '').'
            </div>';

        $category_html .= '</fieldset></'.$form_close.'>';

        return $category_html;
    }

    private function show_categories()
    {
        $categories = $this->get_categories();
        $categories_html = '';

        if ( $this->all_configs['oRole']->hasPrivilege('create-filters-categories') ) {
            $categories_html .= '<p><a href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/create" class="btn btn-success">' . l('Создать категорию') . '</a>
                </p>';//<a href="" class="btn btn-danger">Удалить</a>
        }
        if ( count($categories) > 0 ) {
            $categories_html .= '<p><input class="form-control" id="tree_search" type="text" name="tree_search" placeholder="' . l('поиск по дереву') . '"></p>';
            $categories_html .= '<div class="well four-column" id="search_results" style="display: none;"><ul></ul></div>';
            $categories = $this->get_categories();
            $categories_html .= '<div class="four-column dd backgroud-white" id="categories-tree">' . 
                                    build_array_tree($categories, array(), 2) . 
                                '</div>';
        } else {
            $categories_html .= '<p  class="text-error">' . l('Не существует ниодной категории') . '</p>';
        }

        return $categories_html;
    }

    private function get_categories()
    {
        $categories = $this->all_configs['db']->query("SELECT * FROM {categories} ORDER BY prio")->assoc();

        return $categories;
    }

    private function genmenu()
    {
        /* для сложного вложенного юрл
        $category = explode("-", $this->all_configs['arrequest'][1]);
        foreach ($category AS $el){
            if (isset($this_sql)){
                $this_sql=$this->all_configs['db']->makeQuery('(SELECT id FROM {categories} WHERE  parent_id=?query AND url=? )', array($this_sql, $el));
            } else {
                $this_sql=$this->all_configs['db']->makeQuery('(SELECT id FROM {categories} WHERE parent_id=0 AND url=?)', array($el));
            }
        }
        $sql=$this->all_configs['db']->plainQuery($this_sql);
        $this->cat_id = $sql->el();*/

        $categories = $this->get_categories();
        $categories_html = '';
        if ( $this->all_configs['oRole']->hasPrivilege('create-filters-categories') ) {
            $categories_html .= '<p><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0].'/create" class="btn btn-success">' . l('Создать категорию') . '</a>
                </p>';//<a href="" class="btn btn-danger">Удалить</a>
        }
        $categories_html .= '<div class="dd" id="categories-tree">' . build_array_tree($categories, array($this->cat_id), 2) . '</div>';

        return $categories_html;
    }

    function categories_tree_menu($cats, $count = 0, $pid = null, $c = 0)
    {
        static $i = 1, $table = 1;
        $tree = '';

        foreach($cats as $cat){
            $disabledpage = '';
            if ( $cat['avail'] != 1 )
                $disabledpage = 'class="disabledpage"';
            if ( $count > 0 ) { // если нужно в несколько колонок
                if ( $i == 1) {
                    $tree .= '<td>';
                }
            }
            if ( $cat['parent_id'] == 0)
                $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' connectedSortable nav nav-list first-in-menu">';

            $h = 1;
            for ($iter=0; $iter<$c; $iter++){
                if ($h == 1 && $c > 1)
                    $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' hide-ul connectedSortable nav nav-list">';
                else
                    $tree .= '<ul class="' . ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories') ? 'sortable' . $table : '') . ' connectedSortable nav nav-list">';
                $h++;
            }
            $i++;

            if( intval($cat['id']) == intval($this->cat_id)) {
                if ( $c == 1 && isset($cat['child']) )
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><span class="show-child-cat">+</span><a '
                        . $disabledpage . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] .'/">' . $cat['title'] . '</a>';
                else
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><a ' . $disabledpage . ' href="'
                        . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] . '/">' . $cat['title'] . '</a>';
            } else {
                if ( $c == 1 && isset($cat['child']) )
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><span class="show-child-cat">+</span><a '
                        . $disabledpage . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] .'/">' . $cat['title'] . '</a>';
                else
                    $tree .= '<li id="recordsArray_' . $cat['id'] . '" class="ui-state-default"><a ' . $disabledpage . ' href="'
                        . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $cat['id'] .'/">' . $cat['title'] . '</a>';
            }
            $c++;
            if( isset($cat['child']) ) {
                $tree .=  $this->categories_tree_menu($cat['child'], $count,  $cat['parent_id'], $c );
            } else {
                $tree .= '<ul class="sortable' . $table . ' connectedSortable nav nav-list"><li style="height:2px"></li></ul>';
            }

            $c--;
            $tree .= '</li>';
            for ($iter=0; $iter<$c; $iter++){
                $tree .= '</ul>';
            }
            if ( $cat['parent_id'] == 0 ){
                $tree .= '</ul>';
            }
            if ( $count > 0 ) { // если нужно в несколько колонок
                if ( intval($count/4) <= $i && $cat['parent_id'] == 0 ) {
                    $i = 1;
                    $tree .= '</td>';
                    $table++;
                }
            }
        }

        return $tree;
    }

    function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k=>$l){
            if(isset($list[$l['id']])){
                $l['child'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

    function get_cur_category()
    {
        return $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
            array($this->cat_id))->row();
    }

    function get_filters($filters_values = false)
    {
        // добываем все группы фильтров по текущей категории
        $filters = $this->all_configs['db']->query('SELECT fn.id, fn.title, fn.type, fn.prio
                FROM {filter_name} as fn, {category_filter_name} as cfn
                WHERE cfn.category_id=? AND cfn.fname_id=fn.id ',
            array($this->cat_id))->assoc();

        if ($filters && $filters_values == true) {
            $filters_value = $this->all_configs['db']->query('SELECT fv.value, fv.id, nv.fname_id, fc.filter_id
                    FROM {filter_value} as fv, {category_filter} as fc, {filter_name_value} as nv
                    WHERE fc.category_id=?i AND fc.filter_id=nv.id AND nv.fvalue_id=fv.id',
                array($this->cat_id))->assoc();

            if ($filters_value) {
                foreach ($filters as $k=>$filter) {
                    //$filters[$k]['values'] = array();
                    foreach ($filters_value as $filter_value) {
                        if ($filter_value['fname_id'] == $filter['id']) {
                            $filters[$k]['values'][$filter_value['id']] = array(
                                'value' => $filter_value['value'],
                                'filter_id' => $filter_value['filter_id'],
                            );
                        }
                    }
                }
            }
        }

        return $filters;
    }

    function categories_edit_tab_category()
    {
        $category_html = '';

        if ( $this->all_configs['oRole']->hasPrivilege('show-categories-filters') ) {
            $cur_category = $this->get_cur_category();
            // строим форму редактирования категорий для текущей категории
            $thumbs = '';
            if ( !empty($cur_category['thumbs']) )
                $thumbs = '<img src="' . $this->all_configs['siteprefix'] . $this->cat_img . $cur_category['thumbs'] . '" />';

            $category_html .= '<form method="post" enctype="multipart/form-data"><fieldset>
                <legend>' . $thumbs . ' ' . l('Редактирование категории') . ' ID: ' . $cur_category['id'] . '. ' . $cur_category['title'] . '</legend>';
            if ( isset($_GET['error']) && $_GET['error'] == 'url')
                $category_html .= '<p  class="text-error">' . l('Категория с таким названием уже существует') . '</p>';
            $category_html .= '<div class="form-group"><label>' . l('Название') . ':</label>
                <input class="form-control" name="title" value="' . $cur_category['title'] . '" /></div>';
            $category_html .= '<input type="hidden" class="span5" name="id" value="' . $cur_category['id'] . '" />';
//            $category_html .= '<div class="form-group"><label class="control-label">url:</label>
//                <input class="form-control" name="url" value="' . $cur_category['url'] . '" /></div>';
            /*if ( $cur_category['parent_id'] == 0 ) {
                $category_html .= '<div class="control-group"><label class="control-label">Выберите превью:</label>
                    <div class="controls"><input class="span5" name="thumbs" type="file" /></div>
                    <div class="controls">' . $cur_category['thumbs'] . '</div></div>';
                $category_html .= '<div class="control-group"><label class="control-label">Выберите картинку для меню:</label>
                    <div class="controls"><input class="span5" name="image" type="file" /></div>
                    <div class="controls">' . $cur_category['image'] . '</div></div>';
            }
            if ( $cur_category['parent_id'] > 0 ) {
                $category_html .= '<div class="control-group"><label class="control-label">Выберите картинку:</label>
                    <div class="controls"><input class="span5" name="cat-image" type="file" /></div>
                    <div class="controls">' . $cur_category['cat-image'] . '</div></div>';
            }*/

            $checked = '';
            if($cur_category['avail'] == 1)
                $checked = 'checked';
            $category_html .= '<div class="form-group"><div class="checkbox">
                <label><input name="avail" '.$checked.' type="checkbox">' . l('Активность') . '</label></div></div>';
            $category_html .= '<div class="form-group"><label class="control-label">' . l('Родитель') . ':</label>
                <div class="controls">' . typeahead($this->all_configs['db'], 'categories', false, $cur_category['parent_id'], 2, 'input-large') . '</div>';
            $category_html .= '<div class="form-group"><label>' . l('Описание') . ': </label>
                <div class="controls"><textarea name="content" class="form-control" rows="3">' . htmlspecialchars($cur_category['content']) . '</textarea></div>';
            $category_html .= '<div class="form-group"><label>' . l('Приоритет') . ': </label>
                    <input class="form-control" type="text" value="' . $cur_category['prio'] . '" name="prio"  /></div>';
//            $category_html .= '<div class="form-group"><label>Склады поставщиков США/Китай: </label>
//                <textarea name="warehouses_suppliers" class="form-control" rows="3">' . $cur_category['warehouses_suppliers'] . '</textarea></div>';
            $category_html .= '<div class="form-group"><label>' . l('Важная информация') . ': </label>
                <textarea name="information" class="form-control" rows="3">' . $cur_category['information'] . '</textarea></div>';

            $category_html .= '<div class="form-group"><label>' . l('Рейтинг') . ': </label>
                        <input class="form-control" type=text" onkeydown="return isNumberKey(event, this)" placeholder="' . l('рейтинг') . '" value="' . $cur_category['rating'] . '" name="rating" /></div>';
            $category_html .= '<div class="form-group"><label>' . l('Количество голосов') . ': </label>
                        <input class="form-control" onkeydown="return isNumberKey(event)" type=text" placeholder="' . l('голоса') . '" value="' . $cur_category['votes'] . '" name="votes" /></div>';

            if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
                $category_html .= '<div class="form-group"><div class="controls">
                    <input class="btn btn-primary " type="submit" value="'.l('Сохранить').'" name="edit-category" /></div></div>';
            } else {
                $category_html .= '<script>$(":input:not(:disabled)").prop("disabled",true)</script>';
            }
            $category_html .= '</fieldset></form>';
        } else {
            $category_html .= '<p  class="text-error">' . l('У Вас нет прав для просмотра категорий') .'</p>';
        }

        return array(
            'html' => $category_html,
            'functions' => array(),
        );
    }

    function categories_edit_tab_goods()
    {
        $category_html = '';

        if ( $this->all_configs['oRole']->hasPrivilege('show-goods') ) {
            // проверяем сортировку
            $sorting = ' ORDER BY {goods}.prio';

            $sort = '';
            $sort_id = '<a href="?sort=rid">ID';
            $sort_title = '<a href="?sort=title">' . l('Название продукта');
            $sort_price = '<a href="?sort=price">' . l('Цена');
            $sort_date = '<a href="?sort=date">'.l('Дата').'';
            $sort_avail = '<a href="?sort=avail">' . l('Вкл');
            if ( isset($_GET['sort']) ) {
                $sort = '&sort='.$_GET['sort'];
                switch ( $_GET['sort'] ) {
                    case 'id':
                        $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
                        break;
                    case 'rid':
                        $sort_id = '<a href="?sort=id">ID<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.id DESC';
                        break;
                    case 'title':
                        $sort_title = '<a href="?sort=rtitle">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.title';
                        break;
                    case 'rtitle':
                        $sort_title = '<a href="?sort=title">' . l('Название продукта') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.title DESC';
                        break;
                    case 'price':
                        $sort_price = '<a href="?sort=rprice">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.price';
                        break;
                    case 'rprice':
                        $sort_price = '<a href="?sort=price">' . l('Цена') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.id DESC';
                        break;
                    case 'date':
                        $sort_date = '<a href="?sort=rdate">'.l('Дата').'<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.date_add';
                        break;
                    case 'rdate':
                        $sort_date = '<a href="?sort=date">'.l('Дата').'<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.date_add DESC';
                        break;
                    case 'avail':
                        $sort_avail = '<a href="?sort=ravail">' . l('Вкл') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                        $sorting = ' ORDER BY {goods}.avail';
                        break;
                    case 'ravail':
                        $sort_avail = '<a href="?sort=avail">' . l('Вкл') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                        $sorting = ' ORDER BY {goods}.avail DESC';
                        break;
                }
            } else {
                $sort_id = '<a href="?sort=rid">ID<i class="glyphicon glyphicon-chevron-down"></i>';
            }


            // добываем все товары для текущей категории
            $goods = $this->all_configs['db']->query('SELECT {goods}.*
                FROM {goods}, {category_goods}
                WHERE {category_goods}.category_id=? AND {category_goods}.goods_id={goods}.id' . $sorting,
                array($this->cat_id))->assoc();

            // строим таблицу товаров


            //$category_html .= '<h4>' . l('Товары') . '</h4>';

            if ( $this->all_configs['oRole']->hasPrivilege('create-goods') ) {
                $category_html .= '<a class="btn btn-primary" href="' . $this->all_configs['prefix'] . 'products/create?cat_id=';
                $category_html .= $this->cat_id . '">' . l('Добавить товар') . '</a><br /><br />';
            }

            if (count($goods) > 0) {
                $category_html .= '<table class="table table-striped"><thead><tr>
                        <td>' . $sort_id . '</a></td>
                        <td>' . $sort_title . '</a></td>
                        <td>' . $sort_avail . '</td>
                        <td>'. $sort_price . '</a></td>
                        <td>' . $sort_date . '</a></td>
                        <td title="' . l('Общий остаток') . '">' . l('Общий') . '</td>
                        <td title="' . l('Свободный остаток') . '">' . l('Свободный') . '</td>
                    </td></tr></thead><tbody>';
                $count_on_page = $this->count_on_page;//20; // количество товаров на страничке

                if ( isset($_GET['p']) )
                    $current_page = $_GET['p']-1;
                else
                    $current_page = 0;

                $count_page = $count_on_page > 0 ? ceil(count($goods) / $count_on_page) : 0;
                $show_goods = array_slice($goods, $count_on_page * $current_page, $count_on_page );
                foreach($show_goods as $good)
                {
                    $category_html .= '<tr>
                        <td>'.$good['id'].'</td>
                        <td><a href="' . $this->all_configs['prefix'] . 'products/create/' . $good['id'] . '/">'.htmlspecialchars($good['title']).'<i class="icon-pencil"></i></a>
                            <span style="float:right">
                                <a href="' . $this->all_configs['siteprefix'] . $good['url'] . '/p/' . $good['id'] . '/"><i class="glyphicon glyphicon-eye-open"></i></a>
                            </span></td>
                        <td>'.$good['avail'].'</td><td>' . show_price($good['price'], 2, ' ').'</td>' .
                        '<td><span title="' . do_nice_date($good['date_add'], false) . '">' . do_nice_date($good['date_add']) . '</span></td>
                        <td>' . intval($good['qty_wh']) . '</td><td>' . intval($good['qty_store']) . '</td>
                    </tr>';
                }
                $category_html .= '</tbody></table>';


                // строим блок страничек товаров
                $category_html .= page_block($count_page, '#edit_tab_goods');

            } else {
                $category_html .= '<p  class="text-error">' . l('В выбранной Вами категории нет ни одного товара') . '</p>';
            }
        } else {
            $category_html .= '<p  class="text-error">' . l('У Вас нет прав для просмотра товаров') . '</p>';
        }

        return array(
            'html' => $category_html,
            'functions' => array(),
        );
    }

    function categories_edit_tab_seo()
    {
        $category_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            $cur_category = $this->get_cur_category();
            $category_html .= '<form method="post"><input type="hidden" value="'.$this->cat_id.'" name="category_id" />';
            $category_html .= '<div class="form-group"><label>' . l('Заголовок страницы') . ': </label>
                        <input class="form-control" data-symbol_counter="70" type="text" value="' . $cur_category['page_title'] . '" name="page_title"  /></div>';
            $category_html .= '<div class="form-group"><label>' . l('Описание страницы') . ': </label>
                        <input class="form-control seo_description" data-symbol_counter="150" type="text" value="' . $cur_category['page_description'] . '" name="page_description"  /></div>';
            $category_html .= '<div class="form-group"><label>' . l('Ключевые слова') . ': </label>
                        <input class="form-control seo_keywords" data-symbol_counter="150" type="text" value="' . $cur_category['page_keywords'] . '" name="page_keywords"  /></div>';
            /*$category_html .= '<div class="control-group"><label class="control-label">Описание страницы: </label>
                <div class="controls"><textarea name="page_content" class="span5" rows="3">' . $cur_category['page_content'] . '</textarea></div></div>';*/
            $category_html .= '<div class="form-group">
                <label style="float: left; margin: 4px 10px 0 0">' . l('Редактор') . ':</label>
                <input type="checkbox" id="toggle_mce"'.((isset($_COOKIE['mce_on']) && $_COOKIE['mce_on']) || !isset($_COOKIE['mce_on']) ? 'checked="checked"' : '').'>
                <textarea id="page_content" name="page_content" class="mcefull" rows="18" cols="80" style="width:650px;height:320px;">' . $cur_category['page_content'] . '</textarea></div>';
            $category_html .= '<div class="form-group">
                <input class="btn btn-primary" type="submit" value="'.l('Сохранить').'" name="edit-seo" /></div>';
            $category_html .= '</form></div>';
        }

        return array(
            'html' => $category_html,
            'functions' => array('tiny_mce()'),
        );
    }

    private function gencontent()
    {
        // добываем текущюю категорию
        if ($this->cat_id > 0) {
            $cur_category = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                array($this->cat_id))->row();

            // проверяем на наличие категории
            if( empty($cur_category) )
                return '<p  class="text-error">' . l('Не существует такой категории') . '</p>';
        } else {
            return $this->gencreate();
        }

        $category_html = '';
        $category_html .= '<div class="tabbable"><ul class="nav nav-tabs">';
        $category_html .= '<li><a class="click_tab default" data-open_tab="categories_edit_tab_category" onclick="click_tab(this, event)" data-toggle="tab" href="#edit_tab_category">' . l('Категория') . '</a></li>';
        $category_html .= '<li><a class="click_tab" data-open_tab="categories_edit_tab_goods" onclick="click_tab(this, event)" data-toggle="tab" href="#edit_tab_goods">' . l('Товары') . '</a></li>';
//        if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories'))
//            $category_html .= '<li><a class="click_tab" data-open_tab="categories_edit_tab_seo" onclick="click_tab(this, event)" data-toggle="tab" href="#edit_tab_seo">SEO</a></li>';
        $category_html .= '</ul>';

        $category_html .= '<div class="tab-content"><div id="edit_tab_category" class="tab-pane">';
        $category_html .= '</div>';

        if ( $this->all_configs['oRole']->hasPrivilege('show-goods') ) {
            $category_html .= '<div id="edit_tab_goods" class="tab-pane">';
            $category_html .= '</div>';
        }

        if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')) {
            $category_html .= '<div id="edit_tab_seo" class="tab-pane">';
            $category_html .= '</div>';
        }

        $category_html .= '</div>';

        return $category_html;
    }

    private function can_show_module()
    {
        if ($this->all_configs['oRole']->hasPrivilege('show-goods')) {
            return true;
        } else {
            return false;
        }
    }

    private function ajax()
    {
        $data = array(
            'state' => false
        );

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['products-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав', 'state' => false));
            exit;
        }
        
        if($act == 'create_form'){
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            echo json_encode(array('html' => $this->gencreate($name, true), 'state' => true));
            exit;
        }

        if($act == 'create_new'){
            $_POST['create-category'] = true;
            $create = $this->check_post($_POST, false);
            if(isset($create['error'])){
                echo json_encode(array('state' => false, 'msg' => $create['error']));
            }else{
//            $create = 1;
//            $_POST['title'] = 'test';
                echo json_encode(array('state' => true, 'id' => $create, 'name' => $_POST['title']));
            }
            exit;
        }
        
        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    echo json_encode(array('html' =>  $function['html'], 'state' => true, 'functions' => $function['functions']));
                } else {
                    echo json_encode(array('message' => l('Не найдено'), 'state' => false));
                }
                exit;
            }
        }

        // редактирование названия группы фильтров
        if ($act == 'rename-filter-name') {
            if ($this->cat_id > 0 && isset($_POST['pk']) && $_POST['pk'] > 0 && isset($_POST['value'])) {
                $this->all_configs['db']->query('UPDATE {filter_name} SET title=? WHERE id=?i',
                    array(trim($_POST['value']), $_POST['pk']));
                $data['state'] = true;
            }
        }

        // редактирование названия фильтра
        if ($act == 'rename-filter-value') {
            if ($this->cat_id > 0 && isset($_POST['pk']) && $_POST['pk'] > 0 && isset($_POST['value'])) {
                $this->all_configs['db']->query('UPDATE {filter_value} SET value=? WHERE id=?i',
                    array(trim($_POST['value']), $_POST['pk']));
                $data['state'] = true;
            }
        }

        // drag-and-drop категорий товаров
        if ($act == 'update-categories') {
            if (isset($_POST['cur_id']) && $_POST['cur_id'] > 0) {
                $position = isset($_POST['position']) ? intval($_POST['position']) + 1 : 1;
                $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;

                // обновляем парент ид
                $this->all_configs['db']->query('UPDATE {categories} SET parent_id=?i, prio=?i WHERE id=?i',
                    array($parent_id, $position, $_POST['cur_id']));

                // достаем всех соседей категории
                $categories = $this->all_configs['db']->query('SELECT id, prio FROM {categories}
                      WHERE parent_id IN (SELECT parent_id FROM {categories} WHERE id=?i)
                        AND id<>?i ORDER BY prio',
                    array($_POST['cur_id'], $_POST['cur_id']))->vars();

                if ($categories) {
                    $i = 1;
                    foreach ($categories as $category => $prio) {
                        $i = $i == $position ? $i + 1 : $i;
                        $this->all_configs['db']->query('UPDATE {categories} SET prio=?i WHERE id=?i',
                            array($i, $category));
                        $i++;
                    }
                }

                $data['state'] = true;
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    function transliturl($str) {
        $tr = array(
            "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
            "Д"=>"d","Е"=>"e","Ж"=>"zh","З"=>"z","И"=>"i",
            "Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
            "О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
            "У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch",
            "Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"yi","Ь"=>"",
            "Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            " "=> "-", "."=> "", "/"=> "-", "(" => "", ")"=> ""
        );
        $str = trim($str);
        $str = strtolower($str);
        $str = strtr($str,$tr);
        $str = preg_replace ("/[^a-z0-9-+_\s]/", "", $str);
        $str = preg_replace('/-{2,}/', '-', $str);
        $str = preg_replace('/_{2,}/', '_', $str);

        return $str;
        //$url = preg_replace('/[^0-9a-z-A-Z-_?]/', '', transliturl($title));
    }
}

?>
