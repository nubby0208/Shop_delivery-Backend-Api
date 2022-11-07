<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryManDocument;
use App\Http\Resources\API\DeliveryManDocumentResource;

class DeliveryManDocumentController extends Controller
{
    public function getList(Request $request)
    {
        $delivery_man_document = DeliveryManDocument::myDocument();

        $delivery_man_document->when(request('delivery_man_id'), function ($q) {
            return $q->where('delivery_man_id', request('delivery_man_id'));
        });
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $delivery_man_document->count();
            }
        }

        $delivery_man_document = $delivery_man_document->orderBy('id','desc')->paginate($per_page);
        $items = DeliveryManDocumentResource::collection($delivery_man_document);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
}