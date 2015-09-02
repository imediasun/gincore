<?php
///// module information
$modulename[]='subdomains';
$modulemenu[]='Домены';
$moduleactive[]=true;
///////////
class subdomains {

    protected $all_configs;

    function __construct(&$all_configs)
    {
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас нет прав</p></div>';
        }

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }

        if($ifauth['is_2']) return false;

        $input_html['mmenu'] = $this->genmenu();
        $input_html['mcontent'] = $this->gencontent();
    }

    function genmenu(){

        $out='<h4>Секции сайта</h4>';

        $sql=$this->all_configs['db']->query("SELECT * FROM {section} ORDER BY prio")->assoc();
        $out.='<ul>';
        foreach ($sql AS $pp){
            $out.='<li><a href="'.$this->all_configs['prefix'].'subdomains/'.$pp['id'].'">'.$pp['name'].'</a> 
                        <small>-&gt; '.$pp['redirect'].'</small></li>';

        }
        $out.='</ul>';

        $out.='<br><a href="'.$this->all_configs['prefix'].'subdomains/add">Добавить</a><br>';
        

        $out.='<br>';
        

        return $out;
    }

    function gencontent(){

//        $id=$_POST['id'];
       if (isset($_POST['name'])){
            $prio=$_POST['prio'];
            $name=$_POST['name'];
            $url=$_POST['url'];
            $redirect=$_POST['redirect'];
            $contacts=$_POST['contacts'];
            $contacts2=$_POST['contacts2'];
            $contacts3=$_POST['contacts3'];
       }
//        
//        $ok=$_POST['ok'];
//        $cat=$_POST['cat'];

//        $ut=date_parse($uxt);
//        $uxt=mktime($ut['hour'], $ut['minute'], $ut['second'], $ut['month'], $ut['day'], $ut['year']);

        if (!isset($this->all_configs['arrequest'][1])){
            $out='<h3>Модуль Секций сайта</h3><br>';
            $out.='';
            
        }

###############################################################################
        if (isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])){
            $pp=$this->all_configs['db']->query("SELECT * FROM {section} WHERE id=?i", array($this->all_configs['arrequest'][1]))->row();
            $out='<ul>';
            $out='<h3>Выбраная секция «'.$pp['name'].'»</h3><br>';

            if (!isset($this->all_configs['arrequest'][2])){
                $out.='<form action="'.$this->all_configs['prefix'].'subdomains/'.$pp['id'].'/update" method="POST">
                Название<br>
                    <input type="text" name="name" value="'.$pp['name'].'"   class=":required"><br><br>
                Путь УРЛ (англ. символы)<br>
                    <input type="text" name="url" value="'.$pp['url'].'"   class=""><br><br>
                Приоритет (1, 2 и т. д. Меньше — выше)<br>
                    <input type="text" name="prio" value="'.$pp['prio'].'"   class=":required"><br><br>
                Редирект. УРЛ первой страницы<br>
                    <input type="text" name="redirect" value="'.$pp['redirect'].'"   class=""><br><br>
                Строка 1<br>
                    <input type="text" name="contacts" value="'.$pp['contacts'].'"   class="" size="55"><br>
                Строка 2<br>
                    <input type="text" name="contacts2" value="'.$pp['contacts2'].'"   class="" size="55"><br>
                Строка 3<br>
                    <input type="text" name="contacts3" value="'.$pp['contacts3'].'"   class="" size="55"><br>
                    <br>
                    <input type="submit" value="Сохранить">
                </form>';

            }
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2]=='update') {
                $this->all_configs['db']->query("UPDATE {section} SET name=?, url=?, prio=?, redirect=?, contacts=?, contacts2=?, contacts3=? WHERE id=?i",
                    array($name, $url, $prio, $redirect, $contacts, $contacts2, $contacts3, $this->all_configs['arrequest'][1]), 'ar');
               
                redirect($this->all_configs['prefix'] . 'subdomains/' . $this->all_configs['arrequest'][1], false);
            }//update

        }
###############################################################################
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1]=='add'){
            $out='<h3>Добавление секции</h3><br>';

            $out.='<form action="'.$this->all_configs['prefix'].'subdomains/addnow" method="POST">
                    Название<br>
                        <input type="text" name="name" value=""  class=":required" id="pagename"><br><br>
                    Путь УРЛ (англ. символы)<br>
                        <input type="text" name="url" value=""  class=":required" id="pageurl"><br><br>
                    
                    <input type="submit" value="Создать">
                    </form>';
        }
        ///////
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1]=='addnow'){
                $id = $this->all_configs['db']->query("INSERT INTO  {section} (name, url) VALUES (?, ?)",  array($name, $url), 'id');

                $out.='Создано. <a href="'.$this->all_configs['prefix'].'subdomains/'.$id.'">Продолжить</a>';
        }
###############################################################################

    
        return $out;
    }

}


