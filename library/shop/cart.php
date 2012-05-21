<?php


/**
 * Manage a cart in e-commerce project
 *
 * @category Library
 * @package  OnlineShop
 * @author   Monnot Stéphane (Shin) <monnot.stephane@gmail.com>
 * @license  Licence Shin
 * @link     http://www.shinbuntu.com/framework/documentation/
 */
class Cart
{

    /**
     *
     * @var array
     */
    private $_cart = Array();

    /**
     * Construct which init cart or get existant cart in session
     *
     * @return void
     */
    function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = Array();
        }
        $this->_cart = & $_SESSION['cart'];
    }

    /**
     * Add product to cart
     * 
     * @param string $refProduct [optional] Product's reference
     * @param int    $nb         Quantity to add
     *
     * @return void
     */
    public function addItem($refProduct="", $nb=1)
    {
        if (!isset($this->_cart[$refProduct])) {
            $this->_cart[$refProduct] = Array();
            $this->_cart[$refProduct]['quantity'] = 0;
        }
        $this->_cart[$refProduct]['quantity'] += $nb;
        if ($nb <= 0) {
            unset($this->_cart[$refProduct]);
        }
    }

    /**
     * Remove a product from cart
     *
     * @param string $refProduct [optional] Product's reference
     * @param int    $nb         Quantity to add
     *
     * @return void
     */
    public function removeItem($refProduct="", $nb=1)
    {
        $this->_cart[$refProduct]['quantity'] -= $nb;
        if ($this->_cart[$refProduct]['quantity'] <= 0) {
            unset($this->_cart[$refProduct]);
        }
    }

    /**
     * Remove a product from cart
     *
     * @param string $refProduct [optional] Product's reference
     * @param int    $toSet      Quantity to add
     *
     * @return void
     */
    public function setQuantity($refProduct="", $toSet="")
    {
        $this->_cart[$refProduct]['quantity'] = $toSet;
        if ($toSet <= 0) {
            unset($this->_cart[$refProduct]);
        }
    }

    /**
     * Get quantity of a product or cart
     *
     * @param string $refProduct [optional] Product's reference
     * 
     * @return int
     */
    public function showQuantity($refProduct="")
    {
        if ($refProduct) {
            return $this->_cart[$refProduct]['quantity'];
        } else {
            $total = 0;
            foreach ($this->_cart as $ref => $data) {
                $total += $data['quantity'];
            }
        }
        return $total;
    }

    /**
     * Get all products in the cart
     *
     * @return Array all products
     */
    public function showCart()
    {
        $list = Array();
        $i = 0;
        foreach ($this->_cart as $ref => $data) {
            $list['ref'][$i] = $ref;
            $list['qte'][$i] = $data['quantity'];
            $i++;
        }
        return $list;
    }

}

?>