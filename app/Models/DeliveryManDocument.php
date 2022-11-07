<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryManDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [ 'delivery_man_id', 'document_id', 'is_verified' ];

    protected $casts = [
        'delivery_man_id' => 'integer',
        'document_id' => 'integer',
        'is_verified' => 'integer'
    ];
    
    public function delivery_man(){
        return $this->belongsTo(User::class,'delivery_man_id', 'id')->withTrashed();
    }   
    public function document(){
        return $this->belongsTo(Document::class,'document_id', 'id')->withTrashed();
    }

    public function verifyDeliveryManDocument($delivery_man_id)
    {
        $documents = Document::where('is_required',1)->where('status', 1)->withCount([
            'deliveryManDocument',
            'deliveryManDocument as is_verified_document' => function ($query) use($delivery_man_id) {
                $query->where('is_verified', 1)->where('delivery_man_id', $delivery_man_id);
            }])
        ->get();
    
        $is_verified = $documents->where('is_verified_document', 1);
    
        if(count($documents) == count($is_verified))
        {
            return true;
        } else {
            return false;
        }
    }

    public function scopeMyDocument($query)
    {
        $user = auth()->user();

        if(in_array($user->user_type, ['admin','demo_admin'])) {
            $query =  $query;
        }

        if($user->user_type == 'delivery_man') {
            $query = $query->where('delivery_man_id', $user->id);
        }

        return  $query->whereHas('document',function ($q) {
            $q->where('status',1);
        });
    }
 
}
