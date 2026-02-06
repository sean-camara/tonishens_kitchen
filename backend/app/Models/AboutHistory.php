<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutHistory extends Model
{
    protected $table = 'about_history';

    protected $fillable = ['content'];
}
