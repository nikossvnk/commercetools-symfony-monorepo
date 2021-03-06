<?php
/**
 *
 */

namespace Commercetools\Symfony\CartBundle\Event;

use Commercetools\Core\Model\Cart\Cart;
use Symfony\Component\EventDispatcher\Event;

class CartPostCreateEvent extends Event
{
    private $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }
}
