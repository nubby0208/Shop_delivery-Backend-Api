<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;

class Order extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [ 'client_id', 'date', 'pickup_point', 'delivery_point', 'country_id', 'city_id', 'parcel_type', 'total_weight', 'total_distance', 'pickup_datetime', 'delivery_datetime', 'parent_order_id', 'status', 'payment_id', 'payment_collect_from', 'delivery_man_id', 'fixed_charges', 'extra_charges', 'total_amount', 'pickup_confirm_by_client', 'pickup_confirm_by_delivery_man', 'reason', 'weight_charge', 'distance_charge' , 'total_parcel' , 'auto_assign', 'cancelled_delivery_man_ids' ];

    protected $casts = [
        'client_id' => 'integer',
        'country_id' => 'integer',
        'city_id' => 'integer',
        'parent_order_id' => 'integer',
        'payment_id' => 'integer',
        'delivery_man_id' => 'integer',

        'total_weight' => 'double',
        'total_distance' => 'double',
        'fixed_charges' => 'double',
        'weight_charge' => 'double',
        'distance_charge' => 'double',
        'total_amount' => 'double',
        'pickup_confirm_by_client' => 'integer',
        'pickup_confirm_by_delivery_man' => 'integer',
        'auto_assign' => 'integer',
        'total_parcel' => 'integer',
    ];
    public function client(){
        return $this->belongsTo(User::class, 'client_id','id');
    }

    public function delivery_man(){
        return $this->belongsTo(User::class, 'delivery_man_id','id');
    }

    public function payment(){
        return $this->belongsTo(Payment::class, 'payment_id','id');
    }

    public function retunOrdered()
    {
        return $this->hasMany(Order::class, 'parent_order_id');
    }

    public function scopeMyOrder($query){
        $user = auth()->user();
        if(in_array($user->user_type, ['admin','demo_admin'])) {
            return $query;
            // return $query->withTrashed();
        }

        if($user->user_type == 'client') {
            return $query->where('client_id', $user->id);
        }

        if($user->user_type == 'delivery_man')
        {
            return $query->whereHas('delivery_man',function ($q) use($user) {
                $q->where('delivery_man_id',$user->id);
            });
        }
        return $query;
    }

    public function orderHistory(){
        return $this->hasMany(OrderHistory::class,'order_id','id')->withTrashed();
    }

    protected static function boot(){
        parent::boot();
        static::deleted(function ($row) {
            $row->orderHistory()->delete();
            if($row->forceDeleting === true)
            {
                $row->orderHistory()->forceDelete();
            }
        });
        static::restoring(function($row) {
            $row->orderHistory()->withTrashed()->restore();
        });
    }

    public function getPickupPointAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }

    public function getDeliveryPointAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }

    public function getExtraChargesAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }

    public function setPickupPointAttribute($value)
    {
        $this->attributes['pickup_point'] = isset($value) ? json_encode($value) : null;
    }

    public function setDeliveryPointAttribute($value)
    {
        $this->attributes['delivery_point'] = isset($value) ? json_encode($value) : null;
    }

    public function setExtraChargesAttribute($value)
    {
        $this->attributes['extra_charges'] = isset($value) ? json_encode($value) : null;
    }

    public function getCreatedAtAttribute($value)
    {
        return isset($value) ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }
    public function getUpdatedAtAttribute($value)
    {
        return isset($value) ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }
    public function getDeletedAtAttribute($value)
    {
        return isset($value) ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function country(){
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function city(){
        return $this->belongsTo(City::class, 'city_id','id');
    }
    
    public function getCancelledDeliveryManIdsAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : [];
        return $val;
    }

    public function setCancelledDeliveryManIdsAttribute($value)
    {
        $this->attributes['cancelled_delivery_man_ids'] = isset($value) ? json_encode($value) : [];
    }
}
