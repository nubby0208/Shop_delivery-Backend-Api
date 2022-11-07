<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Http\Resources\API\PaymentResource;

class PaymentController extends Controller
{
    public function paymentSave(Request $request)
    {
        $data = $request->all();
        $data['datetime'] = isset($request->datetime) ? date('Y-m-d H:i:s',strtotime($request->datetime)) : date('Y-m-d H:i:s');
        $result = Payment::updateOrCreate(['id' => $request->id],$data);
        
        $order = Order::find($request->order_id);
        $order->payment_id = $result->id;
        
        $order->save();
        
        $status_code = 200;
        if($result->payment_status == 'paid')
        {
            $message = __('message.payment_completed');
        } else {
            $message = __('message.payment_status_message',['status' => __('message.'.$result->payment_status), 'id' => $order->id  ]);
        }

        if($result->payment_status == 'failed')
        {
            $status_code = 400;
        }
        
        $history_data = [
            'history_type' => 'payment_status_message',
            'payment_status'=> $result->payment_status,
            'order_id' => $order->id,
            'order' => $order,
        ];

        saveOrderHistory($history_data);

        return json_message_response($message,$status_code);
    }

    public function getList(Request $request)
    {
        $payment = Payment::myPayment();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page)){
            
            if(is_numeric($request->per_page))
            {
                $per_page = $request->per_page;
            }

            if($request->per_page == -1 ){
                $per_page = $payment->count();
            }
        }

        $payment = $payment->orderBy('id','desc')->paginate($per_page);
        $items = PaymentResource::collection($payment);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }
}
