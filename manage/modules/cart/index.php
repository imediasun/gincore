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
                $result = $this->createSaleOrder($_POST, $mod_id);
                if ($result['state']) {
                    FlashMessage::set(l('Заказ успешно создан'), FlashMessage::SUCCESS);
                    Response::json(array(
                        'state' => true,
                        'redirect' => $result['location']
                    ));
                }
            }
            if (strcmp($this->all_configs['arrequest'][1], 'purchase') === 0) {
                $result = $this->createOrderToSupplier($_POST, $mod_id);
                if ($result['state']) {
                    FlashMessage::set(l('Заказ поставщику успешно создан'), FlashMessage::SUCCESS);
                    Response::json(array(
                        'state' => true,
                        'redirect' => Url::create(array(
                            'controller' => 'orders',
                            'action' => 'edit',
                            $result['parent_order_id'],
                            'hash' => '#create_supplier_order'
                        ))
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
     * @param $mod_id
     * @return array
     */
    public function createSaleOrder($post, $mod_id)
    {
        $cart = Session::getInstance()->get('cart');
        if (empty($cart)) {
            return array(
                'status' => false
            );
        }
        $goods = $this->Goods->query('SELECT * FROM {goods} WHERE id in (?li)',
            array(array_keys($cart)))->assoc('id');
        $client = $this->Clients->query('SELECT * FROM {clients} WHERE phone=?', array('000000000002'))->row();
        $products = array(
            'item_ids' => array(),
            'amount' => array(),
            'discount' => array(),
            'discount_type' => array(),
            'quantity' => array(),
            'warranty' => array(),
            'sum' => array(),
            'client_id' => empty($client) ? 1 : $client['id'],
            'clients' => 1
        );
        foreach ($cart as $id => $quantity) {
            $products['item_ids'][$id] = $id;
            $products['amount'][$id] = $goods[$id]['price'] / 100;
            $products['discount'][$id] = 0;
            $products['discount_type'][$id] = 0;
            $products['warranty'][$id] = 0;
            $products['quantity'][$id] = empty($post['quantity'][$id]) ? $quantity : $post['quantity'][$id];
            $products['sum'][$id] = $goods[$id]['price'] / 100 * $products['quantity'][$id];
        }

        $result = $this->all_configs['chains']->eshop_sold_items($products, $mod_id);
        if ($result['state']) {
            Session::getInstance()->clear('cart');
        }
        return $result;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function createOrderToSupplier($post, $mod_id)
    {
        $cart = Session::getInstance()->get('cart');
        if (empty($cart)) {
            return array(
                'status' => false
            );
        }
        $goods = $this->Goods->query('SELECT * FROM {goods} WHERE id in (?li)',
            array(array_keys($cart)))->assoc('id');
        $products = array(
            'item_ids' => array(),
            'amount' => array(),
            'quantity' => array(),
            'from_client_order' => 1
        );
        foreach ($cart as $id => $quantity) {
            $products['item_ids'][$id] = $id;
            $products['amount'][$id] = $goods[$id]['price_purchase'];
            $products['quantity'][$id] = empty($post['quantity'][$id]) ? $quantity : $post['quantity'][$id];
        }

        $result = $this->all_configs['suppliers_orders']->create_order($mod_id, $products);
        if ($result['state']) {
            Session::getInstance()->clear('cart');
        }
        return $result;
    }

    /**
     * @return string
     */
    public function gencontent()
    {
        return '';
    }
}
