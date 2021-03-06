<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'type','amount','description','date','attachment', 'user_id'
    ];

    public function user()
    {
    	return $this->belongsTo('App\User','user_id');
    }
}
