<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Http\Resources\API\OrderResource;
use App\Http\Resources\API\OrderDetailResource;
use App\Models\Notification;

class OrderController extends Controller
{
    public function getList(Request $request)
    {
        $order = Order::myOrder();

        if( $request->has('status') && isset($request->status) ) {
            if(request('status') == 'trashed')
            {
                $order = $order->withTrashed();
            }else {
                $order = $order->where('status', request('status'));
            }
        };

        $order->when(request('client_id'), function ($q) {
            return $q->where('client_id', request('client_id'));
        });
        
        $order->when(request('delivery_man_id'), function ($query) {
            return $query->whereHas('delivery_man',function ($q) {
                $q->where('delivery_man_id',request('delivery_man_id'));
            });
        });

        $order->when(request('country_id'), function ($q) {
            return $q->where('country_id', request('country_id'));
        });

        $order->when(request('city_id'), function ($q) {
            return $q->where('city_id', request('city_id'));
        });

        if( request('from_date') != null && request('to_date') != null ){
            $order = $order->whereBetween('date',[ request('from_date'), request('to_date')]);
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $order->count();
            }
        }

        $order = $order->orderBy('date','desc')->paginate($per_page);
        $items = OrderResource::collection($order);

        $user = auth()->user();
        $all_unread_count = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0;

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
            'all_unread_count' => $all_unread_count,
        ];
        
        return json_custom_response($response);
    }

    public function getDetail(Request $request)
    {
        $id = $request->id;
        $order = Order::where('id',$id)->withTrashed()->first();

        if($order == null){
            return json_message_response(__('message.not_found_entry',['name' => __('message.order')]),400);
        }
        $order_detail = new OrderDetailResource($order);

        $order_history = optional($order)->orderHistory;

        $current_user = auth()->user();
        if(count($current_user->unreadNotifications) > 0 ) {
            $current_user->unreadNotifications->where('data.id',$id)->markAsRead();
        }

        $response = [
            'data' => $order_detail,
            'order_history' => $order_history
        ];
        
        return json_custom_response($response);
    }
}
