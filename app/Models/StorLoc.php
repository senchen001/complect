<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorLoc extends Model
{
    use HasFactory;
    
    protected $table = 'storlocs';
    
    protected $fillable = [
        'storloc',
        'storlocdescr'
    ];
}
