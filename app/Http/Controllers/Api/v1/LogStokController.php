<?php

namespace App\Http\Controllers\Api\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LogStok;

class LogStokController extends Controller
{
    public function index()
    {
    	return LogStok::all();
    }
    public function show(LogStok $logStok)
    {
    	return $logStok;
    }
    public function store(Request $request)
    {
    	$request->validate([
    		'wheelId'=>"required",
    		'name'=>"required",
    		'quantity'=>"required",
    		'price'=>"required",
    		'date'=>"required",
    		'keterangan'=>"required",
    	]);

    	$LogStok = LogStok::create($request->all());
    	return $LogStok;
    }
    public function update(LogStok $logStok,Request $request)
    {
    	$logStok->update($request->all());
    	return $logStok;
    }
    public function destroy(LogStok $logStok)
    {
    	$logStok->delete();
    	return response()->json(["Data has been deleted."]);
    }
    public function showBasedOnWheelId($wheelId)
    {
    	return LogStok::where('wheelId',$wheelId)->get();
    }
    public function showBasedOnUniqueCode(Request $request)
    {
        $request->validate([
            'uniqueCode' => 'required'
        ]);
        return LogStok::where('uniqueCode',$request->uniqueCode)->get();
    }

}
