<?php

namespace App\Http\Controllers;
use App\Client;
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
	    	'order'=> 'required',
	    	// 'lunas'=> 'required',
    	]);

    	$client = Client::create($request->all());
    	return $client;
    }
    public function update(Request $request, Client $client)
    {
    	$client->update($request->all());
    	return $client;
    }
    public function destroy(Client $client)
    {
    	$client->delete();
    	return response()->json(["Data has been deleted."]);
    }
}
