<?php

require_once __DIR__ . '/../../Core/Controller.php';
$modulename[260] = 'cart';
$modulemenu[260] = l('Корзина');
$moduleactive[260] = !$ifauth['is_2'];

/**
 * Class cart
 *
 * @property MGoods        Goods
 * @property MClients      Clients
 * @property  MLockFilters LockFilters
 */
class cart extends Controller
{
    const PRICE_SALE = 1;
    const PRICE_WHOLESALE = 2;
    const PRICE_PURCHASE = 3;

    public $uses = array(
        'Goods',
        'LockFilters',
        'Clients'
    );

    /**
     * @param array $post
     * @return array
     */
    public function check_post(array $post)
    {
        $mod_id = $this->all_configs['configs']['cart-page'];
        $user_id = $this->getUserId();

        if (isset($post['cart']) && $this->all_configs['oRole']->hasPrivilege('create-goods')) {
            if (strcmp($this->all_configs['arrequest'][1], 'sale') === 0) {
                $result = $this->createOrderFromCart($_POST);
                if ($result) {
                    Response::json(array(
                        'state' => true,
                        'redirect' =>  $this->all_configs['prefix'] . 'orders?from_cart=1#create_order'
                    ));
                }
            }
            if (strcmp($this->all_configs['arrequest'][1], 'purchase') === 0) {
                $result = $this->createOrderFromCart($_POST);
                if ($result) {
                    Response::json(array(
                        'state' => true,
                        'redirect' => $this->all_configs['prefix'] . 'orders?from_cart=1#create_supplier_order'
                    ));
                }
            }
        }
        return array(
            'state' => true
        );
    }

    /**
     * @return mixed
     */
    public function can_show_module()
    {
        return $this->all_configs['oRole']->hasPrivilege('show-goods');
    }

    /**
     *
     */
    public function ajax()
    {
        $mod_id = $this->all_configs['configs']['products-manage-page'];
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        if ($act == 'add-to-cart') {
            Response::json($this->addToCart($_GET));


        }
        if ($act == 'remove-from-cart') {
            Response::json($this->removeFromCart($_GET));
        }
        if ($act == 'show-cart') {
            Response::json($this->showCart());
        }
        if ($act == 'clear-cart') {
            Response::json($this->clearCart());
        }
        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array(
                            (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                    'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                        )
                    );
                    $result = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
                    );
                } else {
                    $result = array('message' => l('Не найдено'), 'state' => false);
                }
                Response::json($result);
            }
        }

        preg_match('/changes:(.+)/', $act, $arr);
        if (count($arr) == 2 && isset($arr[1])) {
            $data = $this->getAllChanges($act, $mod_id);
        }
        Response::json($data);
    }

    /**
     * @param $goodId
     * @return bool
     */
    public function isUsedGood($goodId)
    {
        return $this->Goods->isUsed($goodId);
    }

    /**
     * @param $get
     * @return array
     */
    public function addToCart($get)
    {
        $good = $this->Goods->getByPk($get['id']);
        if (empty($good)) {
            return array(
                'state' => false,
                'message' => l('Товар не найден')
            );
        }
        if ($good['type'] == GOODS_TYPE_SERVICE) {
            return array(
                'state' => false,
                'message' => l('Нельзя добавить услугу в корзину')
            );
        }
        $cart = Session::getInstance()->get('cart');
        if (empty($cart)) {
            $cart[$get['id']] = 0;
        }
        $cart[$get['id']] += 1;
        Session::getInstance()->set('cart', $cart);
        return array(
            'state' => true,
            'quantity' => $this->getItemInCart()
        );
    }

    /**
     * @param $get
     * @return array
     */
    public function removeFromCart($get)
    {
        $cart = Session::getInstance()->get('cart');
        if (!empty($cart) && key_exists($get['id'], $cart)) {
            unset($cart[$get['id']]);
        }
        Session::getInstance()->set('cart', $cart);
        return array(
            'state' => true,
            'quantity' => $this->getItemInCart()
        );
    }

    /**
     * @return string
     */
    public function showCart()
    {
        $cart = Session::getInstance()->get('cart');
        $goods = array();
        if (!empty($cart)) {
            $goods = $this->Goods->query('SELECT * FROM {goods} WHERE id in (?li)',
                array(array_keys($cart)))->assoc('id');
        }
        return array(
            'state' => true,
            'title' => l('Корзина'),
            'content' => $this->view->renderFile('cart/show', array(
                'cart' => $cart,
                'goods' => $goods
            ))
        );
    }

    /**
     * @return mixed
     */
    public function getItemInCart()
    {
        $cart = Session::getInstance()->get('cart');
        return empty($cart) ? 0 : array_reduce($cart, function ($carry, $value) {
            return $carry + $value;
        });
    }

    /**
     * @return array
     */
    public function clearCart()
    {
        Session::getInstance()->clear('cart');
        return array(
            'state' => true,
            'quantity' => $this->getItemInCart()
        );
    }

    /**
     * @param $post
     * @return bool
     */
    public function createOrderFromCart($post)
    {
        $cart = Session::getInstance()->get('cart');
        if (empty($cart)) {
            return false;
        }
        $products = $this->createCartForOrders($post, $cart);
        Session::getInstance()->set('from_cart', $products);
        Session::getInstance()->clear('cart');
        return true;
    }

    /**
     * @param $post
     * @param $cart
     * @return array
     */
    protected function createCartForOrders($post, $cart)
    {
        $goods = $this->Goods->query('SELECT * FROM {goods} WHERE id in (?li)',
            array(array_keys($cart)))->assoc('id');
        $products = array();
        foreach ($cart as $id => $quantity) {
            switch ($post['price_type']) {
                case self::PRICE_WHOLESALE:
                    $price = $goods[$id]['price_wholesale'];
                    break;
                case self::PRICE_PURCHASE:
                    $price = $goods[$id]['price_purchase'];
                    break;
                default:
                    $price = $goods[$id]['price'];
            }
            $products[] = array(
                'id' => $id,
                'title' => $goods[$id]['title'],
                'price' => $price / 100,
                'quantity' => empty($post['quantity'][$id]) ? $quantity : $post['quantity'][$id]
            );
        }
        return $products;
    }

    /**
     * @return array
     */

    //lopushansky edit
    public function createCartProduct($id_prod)
    {
        $cart = Session::getInstance()->get('cart');

        if($cart ){
        $goods = $this->Goods->query('SELECT * FROM {goods} WHERE id in (?li)',
            array(array_keys($cart)))->assoc('id');
        $products = array();
        foreach ($cart as $id => $quantity) {
        $products[] = array(
                'id' => $id,
                'title' => $goods[$id]['title'],
                'price' => $price / 100,
                'quantity' => empty($post['quantity'][$id]) ? $quantity : $post['quantity'][$id]
            );
        }
        foreach($products as $key=>$value){
           if($value['id']==$id_prod) {

               $product = $value;
           }

        }
        
        return $product;
        }
    }
    //lopushansky edit

    /**
     * @return string
     */
    public function gencontent()
    {
        return '';
    }



  
}
