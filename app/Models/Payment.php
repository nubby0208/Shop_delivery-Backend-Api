<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [ 
        'order_id', 'client_id', 'datetime', 'total_amount', 'payment_type', 'txn_id', 'payment_status', 'transaction_detail'
    ];

    protected $casts = [
        'client_id' => 'integer',
        'order_id' => 'integer',
        'total_amount' => 'double',
    ];

    public function client(){
        return $this->belongsTo(User::class, 'client_id', 'id')->withTrashed();
    }

    public function order(){
        return $this->belongsTo(Order::class, 'order_id', 'id')->withTrashed();
    }

    public function scopeMyPayment($query)
    {
        $user = auth()->user();

        if(in_array($user->user_type, ['admin','demo_admin'])) {
            return $query;
        }

        if($user->user_type == 'client') {
            return $query->where('client_id', $user->id);
        }

        if($user->user_type == 'delivery_man')
        {
            return $query->whereHas('order',function ($q) use($user) {
                $q->where('delivery_man_id',$user->id);
            });
        }

        return $query;
    }

    public function getTransactionDetailAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }

    public function setTransactionDetailAttribute($value)
    {
        $this->attributes['transaction_detail'] = isset($value) ? json_encode($value) : null;
    }

}
