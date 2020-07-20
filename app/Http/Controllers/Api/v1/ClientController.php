<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers;
use App\Client;
use App\Stock;
use Illuminate\Http\Request;
use DB;

class ClientController extends Controller
{
    public function index()
    {
    	// $test = DB::connection('mysql2')->table('stocks')->get();
    	// return $test;
    	return Client::all();
    }
    public function show(Client $client)
    {
        $client->order = json_decode($client->order,true);
    	return $client;
    }
    public function store(Request $request)
    {
    	$request->validate([
    		'nomorBon' => 'required',
	    	'pembeli' => 'required',
	    	'penerima'=> 'required',
	    	'alamatPenerima'=> 'required',
	    	// 'tanggalBayar'=> 'required',
	    	// 'tanggalPengiriman'=> 'required',
	    	// 'terbayar'=> 'required',
	    	// 'lunas'=> 'required',
    	]);

        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$request->pembeli)->get();

        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
       
        $hutang = DB::connection('mysql3')->table('piutangs')->where('pembeli',$clientsupplier[0]->name)->get();

        if($hutang->count() != 0){
            DB::connection('mysql3')->table('piutangs')->where('pembeli',$clientsupplier[0]->name)->update([
                'total' => $hutang[0]->total + $cart->totalPrice
            ]);
        }
        else{
            $hutang = DB::connection('mysql3')->table('piutangs')->insert([
                'pembeli' => $request->pembeli,
                "alamat" => $request->alamatPenerima,
                "total" => $cart->totalPrice,
            ]);
        }

    	$client = Client::create([
            'nomorBon' => $request->nomorBon,
            'pembeli' => $request->pembeli,
            'penerima' => $request->penerima,
            'alamatPenerima' => $request->alamatPenerima,
            'order' => $cart->items,
            'barangTotal'=> $cart->totalQuantity,
            'hargaTotal' => $cart->totalPrice
        ]);

        $output->delete();
    	return $client;
    }
    public function update(Request $request, Client $client)
    {
    	$client->update($request->all());
    	return $client;
    }
    public function destroy(Client $client)
    {
        $items = json_decode($client->order,true);
        foreach ($items as $key => $value) {
            $wheel = DB::connection('mysql2')->table('wheels')->where('id',$key)->get();

            $stock = Stock::where('uniqueCode',$wheel[0]->uniqueCode)->get();

            Stock::where('uniqueCode',$wheel[0]->uniqueCode)->update([
                'quantity' => $stock[0]->quantity+$value['quantity']
            ]);

        }
        $hutang  = DB::connection('mysql3')->table('piutangs')->where('pembeli',$client->pembeli)->get();
        if($hutang->count()!=0){
            DB::connection('mysql3')->table('piutangs')->where('pembeli',$client->pembeli)->update([
                    'total' => $hutang[0]->total - $client->hargaTotal
                ]);
        }

    	$client->delete();
    	return response()->json(["Data has been deleted."]);
    }
}
