<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogStok extends Model
{
    public $table = "LogStoks";
    protected $fillable=[
    	'wheelId',
    	'uniqueCode',
    	'name',
    	'price',
    	'quantity',
    	'date',
    	'keterangan',
    ];
}
