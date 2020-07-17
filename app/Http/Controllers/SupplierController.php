<?php

namespace App\Http\Controllers;
use App\Supplier;
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
    	return $supplier;
    }
    public function store(Request $request)
    {
    	$request->validate([
    		'nomorBon' => 'required',
	    	'penjual' => 'required',
	    	'pembeli'=> 'required',
	    	'alamatPenerima'=> 'required',
	    	// 'tanggalBayar'=> 'required',
	    	// 'tanggalPengiriman'=> 'required',
	    	// 'terbayar'=> 'required',
	    	'order'=> 'required',
	    	// 'lunas'=> 'required',
    	]);

    	$supplier = Supplier::create($request->all());
    	return $supplier;
    }
    public function update(Request $request, Supplier $supplier)
    {
    	$supplier->update($request->all());
    	return $supplier;
    }
    public function destroy(Supplier $supplier)
    {
    	$supplier->delete();
    	return response()->json(["Data has been deleted."]);
    }
}
