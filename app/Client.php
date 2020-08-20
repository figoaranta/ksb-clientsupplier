<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable=[
    	'nomorBon',
    	'pembeli',
    	'penerima',
    	'alamatPenerima',
    	'tanggalBayar',
    	'tanggalPengiriman',
    	'terbayar',
        'keteranganGudang',
    	'order',
        'hargaTotal',
        'barangTotal',
        'keteranganBon',
    	'lunas',
    ];
}
