<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
 "tgl_bayar",
 "jumlah_bayar",
 "id",
 "nisn",
 "id_spp",
 "id_user",
 
 ];
}
?>