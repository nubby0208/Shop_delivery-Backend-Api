<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'name', 'status', 'code', 'distance_type', 'weight_type', 'links'
    ];

    protected $casts = [
        'status' => 'integer'
    ];
    
    public function getLinksAttribute($value)
    {
        $val = isset($value) ? json_decode($value, true) : null;
        return $val;
    }

    public function setLinksAttribute($value)
    {
        $this->attributes['links'] = isset($value) ? json_encode($value) : null;
    }

    public function cities(){
        return $this->hasMany(City::class,'country_id','id');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($row) {
            $row->cities()->delete();
            if($row->forceDeleting === true)
            {
                $row->cities()->forceDelete();
            }
        });
        static::restoring(function($row) {
            $row->cities()->withTrashed()->restore();
        });
    }
}
