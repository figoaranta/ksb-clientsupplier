<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use App\Cart;
use App\Stock;
use Illuminate\Http\Request;
use DB;
class SupplierCartController extends Controller
{   
    public function addToCart(Request $request,$wheelId,$name)
    {
        $wheel = DB::connection('mysql2')->table('wheels')->where('id',$wheelId)->get();
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();

        $request->validate([
            'quantity' => 'required',
            'price' => 'required',
            'keterangan'=>'required',
        ]);    

        $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();
        
        if(count($stock)!=0){
        	$stock[0]->update([
        		'quantity' => $stock[0]->quantity + $request->quantity
        	]);
        }
        else{
        	Stock::create([
        		'uniqueCode'=> $wheel[0]->uniqueCode,
        		'quantity' => $request->quantity
        	]);
        }

        $productArray = ([
            "id"=>$wheel[0]->id,
            "uniqueCode"=>$wheel[0]->uniqueCode, 
            "keterangan"=>$request->keterangan,
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
    public function editCart(Request $request ,$wheelId,$name)
    {
        $wheel = DB::connection('mysql2')->table('wheels')->where('id',$wheelId)->get();
        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$name)->get();  

        $decode = json_decode($cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first()->items,true)[$wheelId];

        if($request->quantity == null){
            $request->quantity = $decode['quantity'];
        }
        if($request->price == null){
            $request->price = $decode['price'];
        }
        if($request->keterangan == null){
            $request->keterangan = $decode['keterangan'];
        }    

        $this->deleteCartItemAll($wheelId,$name);

        $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();
        
        if(count($stock)!=0){
            $stock[0]->update([
                'quantity' => $stock[0]->quantity + $request->quantity
            ]);
        }
        else{
            Stock::create([
                'uniqueCode'=> $wheel[0]->uniqueCode,
                'quantity' => $request->quantity
            ]);
        }

        $productArray = ([
            "id"=>$wheel[0]->id,
            "uniqueCode"=>$wheel[0]->uniqueCode, 
            "keterangan"=>$request->keterangan,
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


        if ($cart) {
            $oldCart = $cart;
            $oldCart->items = json_decode($oldCart->items,true);
        }
        else{
            $oldCart = null;
        }

        $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();
        $stock[0]->update([
        	'quantity' => $stock[0]->quantity-1
        ]);

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
    public function deleteCartItemAll($wheelId,$name)
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
        
        $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();
        $stock[0]->update([
        	'quantity' => $stock[0]->quantity-$oldCart->items[$wheelId]['quantity']
        ]);

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
