<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
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
	    	'pembeli' => 'required',
	    	'penerima'=> 'required',
	    	'alamatPenerima'=> 'required',
            'keteranganBon' => 'required',
	    	'tanggalBayar'=> 'required',
	    	'tanggalPengiriman'=> 'required',
	    	// 'terbayar'=> 'required',
	    	// 'lunas'=> 'required',
    	]);
        $date = (getdate());
        if(Client::all()->count() != 0){
            $number = '';
            $lastNomorBon = Client::all()->last()->nomorBon;
            if(strlen($date['mon'])==1){
                $date['mon'] = "0".$date['mon'];
            }
            if(strlen($date['mday'])==1){
                $date['mday'] = "0".$date['mday'];
            }
            if($lastNomorBon[8].$lastNomorBon[9] != $date['mday']){
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
            
            
            $newBon = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.$newNomorBon;
        }
        else{
            if (strlen($date['mon'])==1){
                $date['mon'] = 0 . $date['mon'];
            }
            if (strlen($date['mday'])==1){
                $date['mday'] = 0 . $date['mday'];
            }
            $newBon = $date['year'].'-'.$date['mon'].'-'.$date['mday'].' '.'1';
        }

        $clientsupplier = DB::connection('mysql2')->table('clients_suppliers')->where('name',$request->pembeli)->get();

        $output = DB::table('carts')->where('id', $clientsupplier[0]->id);

        $cart = DB::table('carts')->where('id', $clientsupplier[0]->id)->first();
       
        $hutang = DB::connection('mysql3')->table('piutangs')->where('pembeli',$clientsupplier[0]->name)->get();

        if($hutang->count() != 0){
            DB::connection('mysql3')->table('piutangs')->where('pembeli',$clientsupplier[0]->name)->update([
                'total' => $hutang[0]->total + $cart->totalPrice
            ]);
        }
        else if ($cart->totalPrice!=0){
            $hutang = DB::connection('mysql3')->table('piutangs')->insert([
                'pembeli' => $request->pembeli,
                "alamat" => $request->alamatPenerima,
                "total" => $cart->totalPrice,
            ]);
        }

    	$client = Client::create([
            'nomorBon' => $newBon,
            'pembeli' => $request->pembeli,
            'penerima' => $request->penerima,
            'alamatPenerima' => $request->alamatPenerima,
            'tanggalBayar'=>$request->tanggalBayar,
            'tanggalPengiriman'=>$request->tanggalPengiriman,
            'order' => $cart->items,
            'barangTotal'=> $cart->totalQuantity,
            'hargaTotal' => $cart->totalPrice,
            'keteranganBon'=> $request->keteranganBon
        ]);

        $output->delete();
    	return $client;
    }
    public function update(Request $request, Client $client)
    {
        if($request->lunas){
            if ($request->lunas == true) {
                $hutang = DB::connection('mysql3')->table('piutangs')->where('pembeli',$client->pembeli)->get();
                if($hutang){
                    DB::connection('mysql3')->table('piutangs')->where('pembeli',$client->pembeli)->update([
                        "total" => $hutang[0]->total - $client->hargaTotal
                    ]);
                }
                
            }
        }
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
    public function showSuratjalan($date)
    {
        return Client::where('tanggalPengiriman',$date)->get();
    }
}
