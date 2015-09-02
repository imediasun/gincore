<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_Pagination
 *
 * @author alex
 */
class Catalog {

    public $quantity = 10;
    private $db;
    private $prefix;
    
    function __construct(){
        // initialize
        global $prefix, $db;
        $this->db = $db;
        $this->prefix = $prefix;
    }
    
    function setElementsPerPage($num){
        $this->quantity = intval($num);
    }
    
####### Page generator 
    
    /**
     * Get total pages for bus list
     * 
     * @return number of pages
     */
    function getPages(){
        $total = $this->db->query('SELECT COUNT(*) FROM {reviews}',
                            array(),
                            'el');
        $pages = ceil($total / $this->quantity);
        return $pages;
    }
    
    /**
     * Get bus list, limited by quantity
     * 
     * @param $page - number of start page
     * @return array with bus params 
     */
    function getElements($page = 1){
        $arr = $this->db->query('SELECT user, comment, mark, uxt
                                FROM {reviews} ORDER BY id DESC LIMIT ?i, ?i',
                            array(($page-1) * $this->quantity, $this->quantity ),
                            'assoc');
        return $arr;
    }
    
    
    /**
     * make get request for current pagination
     * 
     * @param array $arr - with get request
     * @return string - param=value&...
     */
    private function getRequest($arr){
        $str='';
        foreach ($arr as $key=>$val){
            if ($key == 'page')
                continue;
            $str .= $key.'='.$val.'&amp;';
        }
        return $str;
    }
    
    /**
     * Creates naigation string
     * for current page & get request
     * 
     * @global type $prefix
     * @param number $page - current page
     * @param array $get - $_GET request
     * @return string with nawigation
     */
    function getNav($page, $get=''){
        global $prefix;
        $pages = $this->getPages();
        if($get) $get = $this->getRequest($get);
        
        $pages_txt ='<div class="navigation"><div class="nav_inner">';
        $page_show_count = 10;
        if ($page > 0 && $page <= ($page_show_count / 2 + 1)) {
                for ($i = 1; $i <= ($pages > $page_show_count ? $page_show_count : $pages); $i++) {
                        $pages_txt .= '<a href="?'.$get.'page='.$i.'" class="page_link'.($page == $i ? ' current' : '').'">'.$i.'</a>';
                }
                if ($pages > $page_show_count) {
                        if ($pages > ($page_show_count + 1)) {
                                $pages_txt .= '<div class="t3">...</div>
                                        <a href="?'.$get.'page='.$pages.'" class="page_link">'.$pages.'</a>';
                        } else {
                                $pages_txt .= '<a href="?'.$get.'page='.$pages.'" class="page_link">'.$pages.'</a>';
                        }
                }
        } else if ($page > ($page_show_count / 2 + 1) && $page <= $pages - ($page_show_count / 2 - 1)) {
                if ($page - ($page_show_count / 2) > 1) {
                        if ($page - ($page_show_count / 2) > 2) {
                                $pages_txt .= '<a href="?'.$get.'page=1" class="page_link">1</a>
                                        <div class="t3">...</div>';
                        } else {
                                $pages_txt .= '<a href="?'.$get.'page=1" class="page_link">1</a>';
                        }
                }
                for ($i = $page - ($page_show_count / 2); $i <= $page + ($page_show_count / 2 - 1); $i++) {
                        $pages_txt .= '<a href="?'.$get.'page='.$i.'" class="page_link'.($page == $i ? ' current' : '').'">'.$i.'</a>';
                }
                if ($page + ($page_show_count / 2 - 1) < $pages) {
                        if ($page + ($page_show_count / 2 - 1) < $pages - 1) {
                                $pages_txt .= '<div class="t3">...</div>
                                        <a href="?'.$get.'page='.$pages.'" class="page_link">'.$pages.'</a>';
                        } else {
                                $pages_txt .= '<a href="?'.$get.'page='.$pages.'" class="page_link">'.$pages.'</a>';
                        }
                }
        } else if ($page > $pages - ($page_show_count / 2 - 1)) {
                if ($pages - ($page_show_count - 1) > 1) {
                        if ($pages - ($page_show_count - 1) > 2) {
                                $pages_txt .= '<a href="?'.$get.'page=1" class="page_link">1</a>
                                        <div class="t3">...</div>';
                        } else {
                                $pages_txt .= '<a href="?'.$get.'page=1" class="page_link">1</a>';
                        }
                }
                for ($i = $pages - ($page_show_count - 1); $i <= $pages; $i++) {
                        $pages_txt .= '<a href="?'.$get.'page='.$i.'" class="page_link'.($page == $i ? ' current' : '').'">'.$i.'</a>';
                }
        }
        $pages_txt .= "</div></div>";
        return $pages_txt;
    }
    
    
    /**
     * Generate user friendly URL to Bus page from bus params
     * 
     * @param type $brand
     * @param type $model
     * @param type $type
     * @param type $id
     * @return string
     */
    function generateItemLink($brand, $model, $type, $id)
    {
        $prefix = $this->prefix;
        $str = strtolower($brand.'-'.$model.'-'.$type).'-id'.$id;
        $str = $this->replaceForUFU($str);
        $str = htmlspecialchars($str);
        $str = $this->translitIt($str);
        $link = $prefix. $str;
//        $link = $prefix. urlencode($link);
        return $link;
    }
    
    /**
     * replace symbols to make user friendly URL
     * 
     * @param type $str
     * @return type
     */
    function replaceForUFU($str)
    {
        // replace to make readable
        $str = str_replace(array(' ', '/', '&'), '-', $str);
        // delete unnecessary
        $str = str_replace(array('(', ')', '+', '*', '`', '"', "'", '?',
                                '{', '}', '[', ']', '|', '\\', '^', '~',
                                '<', '>'
                                )
                            , '', $str);
        // replaces up to 9 symbols by 1
        $str = str_replace(array('----', '---', '--'), '-', $str);
        return $str;
    }
    
    /**
     * Simplified translit function
     * convert str from ru to en
     * 
     * @param type $str
     * @return type
     */
    function translitIt($str) 
    {
        $tr = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        );
        return strtr($str,$tr);
    }
    
}

?>
