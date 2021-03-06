<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use App\Supplier;
use App\Stock;
use Illuminate\Http\Request;
use DB;
use App\LogStok;

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
                "price"=>$value['price'],
                "keterangan"=>$value['keterangan']
            ]);
            $newObject = (object) $array;
            array_push($newArray, $newObject);
        }
        $supplier->order = $newArray;
    	return $supplier;
    }
    public function store(Request $request)
    {
        $today = "";
    	$request->validate([
	    	'penjual' => 'required',
	    	'pembeli'=> 'required',
	    	'alamatPenerima'=> 'required',
            'keteranganBon' => 'required',
	    	'tanggalBayar'=> 'required',
	    	'tanggalPengiriman'=> 'required',
            'keteranganGudang'=>'required',
	    	// 'terbayar'=> 'required',
	    	// 'order'=> 'required',
	    	// 'lunas'=> 'required',
    	]);
        $date = (getdate());
        if(Supplier::all()->count() != 0){
            $number = '';
            $lastNomorBon = Supplier::all()->last()->nomorBon;
            if(strlen($date['mon'])==1){
                $date['mon'] = "0".$date['mon'];
            }
            if(strlen($date['mday'])==1){
                $date['mday'] = "0".$date['mday'];
            }
            if($lastNomorBon[10].$lastNomorBon[11] != $date['mday']){
                $newNomorBon = 1;
            }
            else{
                for ($i=strlen($lastNomorBon)-1; $i > 0 ; $i--) { 
                    if($lastNomorBon[$i] == " "){
                        break;
                    }
                $number = $number.$lastNomorBon[$i];
                }
                $newNomorBon = strrev($number)+1;
            }
            
            $newBon = 'S-'.$date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$newNomorBon;
            $today = $date['year'].'-'.$date['mon'].'-'.$date['mday'];
        }
        else{
            if (strlen($date['mon'])==1){
                $date['mon'] = 0 . $date['mon'];
            }
            if (strlen($date['mday'])==1){
                $date['mday'] = 0 . $date['mday'];
            }
            
            $newBon = 'S-'.$date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.'1';
            $today = $date['year'].'-'.$date['mon'].'-'.$date['mday'];
        }

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

        foreach (json_decode($cart->items,true) as $key => $value) {
             $LogStok = LogStok::create([
                'wheelId'=>$key,
                'uniqueCode'=>$value['uniqueCode'],
                'name'=>$request->penjual,
                'quantity'=>$value['quantity'],
                'price'=>$value['price'],
                'date'=>$today,
                'keterangan'=>"In",
             ]);
         };
         
        $supplier = Supplier::create([
            'nomorBon' => $newBon,
            'penjual' => $request->penjual,
            'pembeli' => $request->pembeli,
            'alamatPenerima' => $request->alamatPenerima,
            'tanggalBayar'=>$request->tanggalBayar,
            'tanggalPengiriman'=>$request->tanggalPengiriman,
            'keteranganGudang'=>$request->keteranganGudang,
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
        if($request->lunas){
            if ($request->lunas == true) {
                $hutang = DB::connection('mysql3')->table('hutangs')->where('penjual',$supplier->penjual)->get();
                if($hutang){
                    DB::connection('mysql3')->table('hutangs')->where('penjual',$supplier->penjual)->update([
                        "total" => $hutang[0]->total - $supplier->hargaTotal
                    ]);
                }
                
            }
        }
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
        public function showFromClientName($supplier)
    {
        return Supplier::where('penjual',$supplier)->where('lunas',0)->get();
    }
}
