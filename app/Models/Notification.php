<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
         'titel',
         'body',
         'data',
         'read_at',
         'receiver_id'
       
     ];
}
