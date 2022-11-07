<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username', 'user_type', 'country_id', 'city_id', 'address', 'contact_number','email_verified_at',
        'player_id', 'latitude', 'longitude', 'status', 'last_notification_seen' , 'login_type', 'uid', 'fcm_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'country_id' => 'integer',
        'city_id' => 'integer',
        'status' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    
     protected $appends = [
        'profile_photo_url',
    ];


    public function routeNotificationForOneSignal()
    {
        return $this->player_id;
    }

    public function routeNotificationForFcm($notification)
    {
        return $this->fcm_token;
    }

    public function userCount($user_type = null)
    {
        $query = self::query();
        $query->when($user_type, function($q) use($user_type){
           return $q->where('user_type',$user_type);
        });
        return $query->count();
    }

    public function country(){
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function city(){
        return $this->belongsTo(City::class, 'city_id','id');
    }

    public function order(){
        return $this->hasMany(Order::class,'client_id','id')->withTrashed();
    }

    public function deliveryManOrder(){
        return $this->hasMany(Order::class,'delivery_man_id','id')->withTrashed();
    }

    public function deliveryManDocument(){
        return $this->hasMany(DeliveryManDocument::class,'delivery_man_id', 'id')->withTrashed();
    }

    protected static function boot(){
        parent::boot();
        static::deleted(function ($row) {
            
            switch ($row->user_type) {
                case 'client':
                    $row->order()->delete();
                    if($row->forceDeleting === true)
                    {
                        $row->order()->forceDelete();
                    }
                    break;
                case 'delivery_man':
                    if($row->forceDeleting === true){
                        $row->deliveryManOrder()->update(['delivery_man_id' => NULL ]);
                    }
                    break;
                default:
                    # code...
                    break;
                }
        });
        static::restoring(function($row) {
            $row->order()->withTrashed()->restore();
        });
    }
}
