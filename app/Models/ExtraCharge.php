<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraCharge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [ 'title', 'charges_type', 'charges', 'country_id', 'city_id', 'status' ];

    public function country(){
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function city(){
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    protected $casts = [
        'charges' => 'double',
        'status' => 'integer',
        'city_id' => 'integer',
        'country_id' => 'integer'
    ];

}
