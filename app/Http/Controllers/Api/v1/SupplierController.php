<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use App\Supplier;
use App\Stock;
use Illuminate\Http\Request;
use DB;

class SupplierController extends Controller
{
    public function index()
    {
    	return Supplier::all();
    }
    public function show(Supplier $supplier)
    {
        // $productArray = ([
        //     "id"=>$wheel[0]->id,
        //     "uniqueCode"=>$wheel[0]->uniqueCode, 
        //     "keteranganGudang"=>null,
        //     "price"=>$request->price,
        //     "quantity"=>$request->quantity
        // ]);
        $newArray = [];
        $supplier->order = json_decode($supplier->order,true);
        foreach ($supplier->order as $key => $value) {
            $array = ([
                "wheelId"=>$key,
                "uniqueCode"=>$value['uniqueCode'],
                "quantity"=>$value['quantity'],
                "price"=>$value['price']
            ]);
            $newObject = (object) $array;
            array_push($newArray, $newObject);
        }
        $supplier->order = $newArray;
    	return $supplier;
    }
    public function store(Request $request)
    {
    	$request->validate([
    		'nomorBon' => 'required',
	    	'penjual' => 'required',
	    	'pembeli'=> 'required',
	    	'alamatPenerima'=> 'required',
            'keteranganBon' => 'required',
	    	// 'tanggalBayar'=> 'required',
	    	// 'tanggalPengiriman'=> 'required',
	    	// 'terbayar'=> 'required',
	    	// 'order'=> 'required',
	    	// 'lunas'=> 'required',
    	]);

        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$request->penjual)->get();

        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
        
        $hutang = DB::connection('mysql3')->table('hutangs')->where('penjual',$clientsupplier[0]->name)->get();

        if($hutang->count() != 0){
            DB::connection('mysql3')->table('hutangs')->where('penjual',$clientsupplier[0]->name)->update([
                'total' => $hutang[0]->total + $cart->totalPrice
            ]);
        }
        else if ($cart->totalPrice!=0){
            $hutang = DB::connection('mysql3')->table('hutangs')->insert([
                'penjual' => $request->penjual,
                "alamat" => $request->alamatPenerima,
                "total" => $cart->totalPrice,
            ]);
        }

        $supplier = Supplier::create([
            'nomorBon' => $request->nomorBon,
            'penjual' => $request->penjual,
            'pembeli' => $request->pembeli,
            'alamatPenerima' => $request->alamatPenerima,
            'keteranganBon' =>$request->keteranganBon,
            'order' => $cart->items,
            'barangTotal'=> $cart->totalQuantity,
            'hargaTotal' => $cart->totalPrice
        ]);

        $output->delete();
    	return $supplier;
    }
    public function update(Request $request, Supplier $supplier)
    {
    	$supplier->update($request->all());
    	return $supplier;
    }
    public function destroy(Supplier $supplier)
    {
        $items = json_decode($supplier->order,true);
        foreach ($items as $key => $value) {
            $wheel = DB::connection('mysql2')->table('wheels')->where('id',$key)->get();

            $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();

            Stock::where('uniqueCode',$wheel[0]->uniqueCode)->update([
                'quantity' => $stock[0]->quantity-$value['quantity']
            ]);
        }

        $hutang  = DB::connection('mysql3')->table('hutangs')->where('penjual',$supplier->penjual)->get();
        if($hutang->count()!=0){
            if($hutang[0]->total - $supplier->hargaTotal == 0){
                DB::connection('mysql3')->table('hutangs')->where('penjual',$supplier->penjual)->delete();
            }
            else{
                DB::connection('mysql3')->table('hutangs')->where('penjual',$supplier->penjual)->update([
                    'total' => $hutang[0]->total - $supplier->hargaTotal
                ]);
            }
        }

    	$supplier->delete();
    	return response()->json(["Data has been deleted."]);
    }
}
