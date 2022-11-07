<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [ 'name', 'status', 'is_required' ];

    public function deliveryManDocument()
    {
        return $this->hasMany(DeliveryManDocument::class, 'document_id', 'id' );
    }

    protected $casts = [
        'status' => 'integer',
        'is_required' => 'integer'
    ];

}
