<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaticData;
use App\Http\Resources\API\StaticDataResource;

class StaticDataController extends Controller
{
    public function getList(Request $request)
    {
        $staticdata = StaticData::query();
        
        $staticdata->when(request('type'), function ($q) {
            return $q->where('type', request('type'));
        });

        $staticdata->when(request('keyword'), function ($q) {
            return $q->where('value', 'LIKE', '%' . request('keyword') . '%');
        });
        
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $staticdata->count();
            }
        }

        $staticdata = $staticdata->orderBy('label','asc')->paginate($per_page);
        $items = StaticDataResource::collection($staticdata);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
}
