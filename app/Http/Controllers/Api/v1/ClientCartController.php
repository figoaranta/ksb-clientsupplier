<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers;
use App\Cart;
use App\Stock;
use Illuminate\Http\Request;
use DB;
class ClientCartController extends Controller
{   
    public function addToCart(Request $request,$wheelId,$name)
    {
        $wheel = DB::connection('mysql2')->table('wheels')->where('id',$wheelId)->get();
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();

        $request->validate([
            'quantity' => 'required',
            'price' => 'required',
            'keteranganGudang' => 'required'
        ]);

        if($request->keteranganGudang == 'Gudang'){
            $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();

            if($request->quantity > $stock[0]->quantity){
                return response()->json(["Insufficient Stock"]);
            }
            else{
                $stock[0]->update([
                    "quantity" => $stock[0]->quantity - $request->quantity
                ]);
            } 
        }
        

        $productArray = ([
            "id"=>$wheel[0]->id,
            "uniqueCode"=>$wheel[0]->uniqueCode, 
            "keteranganGudang"=>$request->keteranganGudang,
            "price"=>$request->price,
            "quantity"=>$request->quantity
        ]);
        
        $arrayObject = (object) $productArray;

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();

        if ($cart) {
            $oldCart = $cart;
            $oldCart->items = json_decode($oldCart->items,true);
        }
        else{
            $oldCart = null;
        }

        $newCart = new Cart($oldCart);

        $newCart->add($arrayObject, $wheel[0]->id);

        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);
        if ($cart == null) {
            $output->insert([
            'id' => $clientsupplier[0]->id,
            'name' => $clientsupplier[0]->name,
            'items' => json_encode($newCart->items),
            'totalPrice' => $newCart->totalPrice,
            'totalQuantity' => $newCart->totalQuantity
            ]);
        }
        else{
            $output->update([
            'id' => $clientsupplier[0]->id,
            'name' => $clientsupplier[0]->name,
            'items' => json_encode($newCart->items),
            'totalPrice' => $newCart->totalPrice,
            'totalQuantity' => $newCart->totalQuantity
            ]);
        }   
        

        return response()->json([$newCart]);

    }

    public function viewCart($name)
    {
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
        
        if($cart == null){
            return response()->json(["Cart is currently empty"]);
        }
        else{
            $cart->items = json_decode($cart->items);
            return response()->json([$cart]);
        }
        
    }
    public function deleteCartItem(Request $request ,$wheelId,$name)
    {

        $wheel = DB::connection('mysql2')->table('wheels')->where('id',$wheelId)->get();
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();


        
        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);

        if(json_decode($cart->items,true)[$wheelId]['keteranganGudang'] != "Pinjam"){
            $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();

            $stock[0]->update([
                'quantity' => $stock[0]->quantity + 1
            ]);
        }
        
        if ($cart) {
            $oldCart = $cart;
            $oldCart->items = json_decode($oldCart->items,true);
        }
        else{
            $oldCart = null;
        }

        if($oldCart->totalQuantity == 1){
            $output->delete();
            return response()->json([]);            
        }

        if($oldCart->items[$wheelId]['quantity'] == 1){
            $oldCart->totalQuantity = $oldCart->totalQuantity-1;
            $oldCart->totalPrice = $oldCart->totalPrice - $oldCart->items[$wheelId]['price'];

            unset($oldCart->items[$wheelId]);

            $output->update([
                'id' => $clientsupplier[0]->id,
                'name' => $clientsupplier[0]->name,
                'items' => json_encode($oldCart->items),
                'totalPrice' => $oldCart->totalPrice,
                'totalQuantity' => $oldCart->totalQuantity
                ]);
            $cartArray = [];
            return response()->json([]);
        }

        $basePrice = $oldCart->items[$wheelId]['price']/$oldCart->items[$wheelId]['quantity'];

        $oldCart->totalQuantity = $oldCart->totalQuantity-1;
        $oldCart->items[$wheelId]['quantity'] = $oldCart->items[$wheelId]['quantity']-1;
        $oldCart->items[$wheelId]['price'] = $oldCart->items[$wheelId]['price'] - $basePrice;
        $oldCart->totalPrice = $oldCart->totalPrice - $basePrice;
        

        $output->update([
        'id' => $clientsupplier[0]->id,
        'name' => $clientsupplier[0]->name,
        'items' => json_encode($oldCart->items),
        'totalPrice' => $oldCart->totalPrice,
        'totalQuantity' => $oldCart->totalQuantity
        ]);
        // $request->session()->put('cart',$oldCart);
        return response()->json(['Data has been deleted.']);
    }
    public function deleteCartItemAll(Request $request, $wheelId,$name)
    {
        $wheel = DB::connection('mysql2')->table('wheels')->where('id',$wheelId)->get();
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);

        if ($cart) {
            $oldCart = $cart;
            $oldCart->items = json_decode($oldCart->items,true);
        }
        else{
            $oldCart = null;
        }

        if($oldCart->items[$wheelId]['keteranganGudang'] != "Pinjam"){
            $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();

            $stock[0]->update([
                'quantity' => $stock[0]->quantity + $oldCart->items[$wheelId]['quantity']
            ]);
        }
        
        $oldCart->totalPrice = $oldCart->totalPrice - $oldCart->items[$wheelId]['price'];
        $oldCart->totalQuantity = $oldCart->totalQuantity - $oldCart->items[$wheelId]['quantity'];
        unset($oldCart->items[$wheelId]);

        $output->update([
            'id' => $clientsupplier[0]->id,
            'name' => $clientsupplier[0]->name,
            'items' => json_encode($oldCart->items),
            'totalPrice' => $oldCart->totalPrice,
            'totalQuantity' => $oldCart->totalQuantity
        ]);
        
        if($oldCart->items == null){
            $output->delete();
            // Session::flush();
        }
        return response()->json(['Data has been deleted.']);
    }
}
