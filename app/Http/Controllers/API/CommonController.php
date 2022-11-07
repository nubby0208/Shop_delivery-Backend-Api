<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Validator;
class CommonController extends Controller
{
    public function placeAutoComplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
            'country_code' => 'required',
            'language' => 'required'
        ]);

        if ( $validator->fails() ) {
            $data = [
                'status' => 'false',
                'message' => $validator->errors()->first(),
                'all_message' =>  $validator->errors()
            ];

            return json_custom_response($data,400);
        }
        
        $google_map_api_key = env('GOOGLE_MAP_API_KEY');
        // $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.request('search_text').'&components=country:'.request('country_code').'&language:'.request('language').'&key='.$google_map_api_key);
        $response = Http::withHeaders([
            'Accept-Language' => request('language'),
        ])->get('https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.request('search_text').'&components=country:'.request('country_code').'&key='.$google_map_api_key);
        return $response->json();
    }

    public function placeDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ( $validator->fails() ) {
            $data = [
                'status' => 'false',
                'message' => $validator->errors()->first(),
                'all_message' =>  $validator->errors()
            ];

            return json_custom_response($data,400);
        }
        
        $google_map_api_key = env('GOOGLE_MAP_API_KEY');
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$request->placeid.'&key='.$google_map_api_key);

        return $response->json();
    }
}