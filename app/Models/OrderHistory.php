<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderHistory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [ 'order_id', 'datetime', 'history_type', 'history_message', 'history_data' ];

    public function getHistoryDataAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }
    protected $casts = [
        'order_id' => 'integer',
    ];

}
