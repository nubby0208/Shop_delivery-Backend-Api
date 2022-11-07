<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Resources\API\CityResource;

class CityController extends Controller
{
    public function getList(Request $request)
    {
        $city = City::query();
        
        $city->when(request('country_id'), function ($q) {
            return $q->where('country_id', request('country_id'));
        });

        $city->when(request('name'), function ($q) {
            return $q->where('name', 'LIKE', '%' . request('name') . '%');
        });

        if( $request->has('status') && isset($request->status) )
        {
            $city = $city->where('status',request('status'));
        }

        if( $request->has('is_deleted') && isset($request->is_deleted) && $request->is_deleted){
            $city = $city->withTrashed();
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $city->count();
            }
        }

        $city = $city->orderBy('name','asc')->paginate($per_page);
        $items = CityResource::collection($city);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }

    public function getDetail(Request $request)
    {
        $id = $request->id;
        $city = City::where('id',$id)->withTrashed()->first();

        if(empty($city))
        {
            $message = __('message.not_found_entry',[ 'name' => __('message.city') ]);
            return json_message_response($message,400);   
        }
        
        $city_detail = new CityResource($city);

        $response = [
            'data' => $city_detail
        ];
        
        return json_custom_response($response);
    }
}
