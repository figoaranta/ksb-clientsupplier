<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable=[
    	'nomorBon',
    	'penjual',
    	'pembeli',
    	'alamatPenerima',
    	'tanggalBayar',
    	'tanggalPengiriman',
    	'terbayar',
    	'order',
    	'lunas',
    ]
}
