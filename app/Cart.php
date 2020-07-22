<?php

namespace App;

class Cart
{
    public $items;
    public $totalQuantity = 0;
    public $totalPrice = 0;

    public function __construct($oldCart)
    {
        if($oldCart){
            $this->items = $oldCart->items;
            $this->totalQuantity = $oldCart->totalQuantity;
            $this->totalPrice = $oldCart->totalPrice;
        }
        else{
            $this->items = null;
        }
    }

    public function add($item,$id)
    {
        $storedItem = ['quantity'=>0 , 'price' => $item->price , 'uniqueCode' => $item->uniqueCode, 'keterangan'=>$item->keterangan];
        // $storedItem = ['quantity'=>0 , 'price' => $item->price , 'item' => $item];
        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            }
        }
        $storedItem['quantity'] = $storedItem['quantity']+ $item->quantity;
        $storedItem['price'] = $item->price * $storedItem['quantity'];
        // $storedItem['price'] = $item->price;
        $this->totalQuantity +=$item->quantity;
        $this->totalPrice += $item->price*$item->quantity;
        // $this->totalPrice += $item->price;
        $this->items[$id] = $storedItem;
    }


}