<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryApproval extends Model
{
     
    use HasFactory;
    protected $fillable = [
        'labrarian',
        'stor_loc',
        'place_code',
        'inv_num',
        'copies_count',
        'book_descr'
    ];
}
