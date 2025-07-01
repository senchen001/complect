<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class borrowedBook extends Model
{
     use HasFactory;
    protected $fillable = [
        'labrarian',
        'reader',
        'inv_num',
        'book_descr',
        'db',
        'giveDate',
        'returnDate'
    ];
}
