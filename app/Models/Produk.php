<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['satuan_id', 'nama_produk', 'harga_beli_terakhir', 'harga_jual', 'stok_saat_ini'])]
class Produk extends Model
{
    //
}
