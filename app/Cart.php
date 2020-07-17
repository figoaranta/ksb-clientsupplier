<?php

namespace App;

class Cart
{
    public $productId;
    public $totalQuantity = 0;
    public $totalPrice = 0;

    public function __construct($oldCart)
    {
        if($oldCart){
            $this->productId = $oldCart->productId;
            $this->totalQuantity = $oldCart->totalQuantity;
            $this->totalPrice = $oldCart->totalPrice;
        }
        else{
            $this->productId = null;
        }
    }

    public function add($item,$id)
    {
        $storedItem = ['quantity'=>0 , 'price' => $item->price , 'item' => $item];
        if ($this->productId) {
            if (array_key_exists($id, $this->productId)) {
                $storedItem = $this->productId[$id];
            }
        }
        $storedItem['quantity']++;
        $storedItem['price'] = $item->price * $storedItem['quantity'];
        $this->totalQuantity++;
        $this->totalPrice += $item->price;
        $this->productId[$id] = $storedItem;
    }


}