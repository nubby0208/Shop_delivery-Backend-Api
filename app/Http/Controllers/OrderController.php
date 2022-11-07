<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StaticData;
use App\Models\Notification;
use App\Traits\OrderTrait;
use App\Models\AppSetting;

class OrderController extends Controller
{
    use OrderTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $result = Order::updateOrCreate(['id' => $request->id], $data);

        $message = __('message.update_form',[ 'form' => __('message.order') ] );
		if($result->wasRecentlyCreated){
			$message = __('message.save_form',[ 'form' => __('message.order') ] );
		}

        $history_data = [
            'history_type' => $result->status,
            'order_id' => $result->id,
            'order' => $result,
        ];
        saveOrderHistory($history_data);

        if( $result->status == 'create' ) {
            $app_setting = AppSetting::first();
            if( isset($app_setting) && $app_setting->auto_assign == 1 ) {
                $this->autoAssignOrder($result, $request->all());
            }
        }
        if($request->is('api/*')) {
            $response = [
                'order_id' => $result->id,
                'message' => $message
            ];
            return json_custom_response($response);
		}
    }

    public function AutoAssignCancelOrder(Request $request)
    {
        $order_data = Order::find($request->id);

        $result = $this->autoAssignOrder($order_data,$request->all());

        $message = __('message.updated');
        if( $result->delivery_man_id == null ) {
            $message = __('message.save_form',[ 'form' => __('message.order') ] );
        }
        if($request->is('api/*')) {
            $response = [
                'order_id' => $result->id,
                'message' => $message
            ];
            return json_custom_response($response);
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $old_status = $order->status;

        $order->fill($request->all())->update();

        uploadMediaFile($order, $request->pickup_time_signature, 'pickup_time_signature');
        uploadMediaFile($order, $request->delivery_time_signature, 'delivery_time_signature');
        $message = __('message.update_form',[ 'form' => __('message.order') ] );

        $payment = Payment::where('order_id',$id)->first();

        if(in_array(request('status'), ['delayed', 'cancelled', 'failed']) ) {
            $history_data = [
                'history_type' => request('status'),
                'order_id' => $id,
                'order' => $order,
            ];
        
            saveOrderHistory($history_data);
        }

        /* if($order->payment_id != null)
        {
            $paymentdata = [
                'payment_status' => isset($request->payment_status) ? $request->payment_status : 'pending'
            ];
            
            $payment->update($paymentdata);

            $history_data = [
                'history_type' => 'payment_status_message',
                'payment_status'=> $request->payment_status,
                'order_id' => $id,
                'order' => $order,
            ];

            saveOrderHistory($history_data);
        } */

        if(in_array(request('status'), ['courier_picked_up', 'courier_arrived', 'completed', 'courier_departed']) ) {
            $history_data = [
                'history_type' => request('status'),
                'order_id' => $id,
                'order' => $order,
            ];
            
            saveOrderHistory($history_data);
        }

        
        if($request->is('api/*')) {
            return json_message_response($message);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        $message = __('message.msg_fail_to_delete',['item' => __('message.order')] );
        
        if( $order != '' ) {
            $order->delete();
            $message = __('message.msg_deleted',['name' => __('message.order')] );
        }
        
        if(request()->is('api/*')){
            return json_custom_response(['message'=> $message , 'status' => true]);
        }
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $order = Order::withTrashed()->where('id',$id)->first();

        $message = __('message.not_found_entry',['name' => __('message.order')] );
        if($request->type === 'restore'){
            $order->restore();
            $message = __('message.msg_restored',['name' => __('message.order')] );
        }

        if($request->type === 'forcedelete'){
            $order->forceDelete();
            $search = "id".'":'.$id;
            Notification::where('data','like',"%{$search}%")->delete();
            $message = __('message.msg_forcedelete',['name' => __('message.order')] );
        }

        if($request->type == 'courier_assigned') {
            if($order->delivery_man_id != null)
            {
                $message = __('message.couriertransfer');
                $history_type = 'courier_transfer';
            } else {
                $message = __('message.courierassigned');
                $history_type = 'courier_assigned';
            }

            $order->update(['delivery_man_id' => $request->delivery_man_id, 'status' => $request->status]);
            $history_data = [
                'history_type' => $history_type,
                'order_id' => $id,
                'order' => $order,
            ];
            
            saveOrderHistory($history_data);
        }

        if($request->type == 'courier_departed') {
            $order->update([ 'status' => $request->status ]);
            $history_data = [
                'history_type' => 'courier_departed',
                'order_id' => $id,
                'order' => $order,
            ];
            
            saveOrderHistory($history_data);
        }

        if($request->type == 'completed') {
            $order->update([ 'status' => $request->type ]);
            $history_data = [
                'history_type' => 'completed',
                'order_id' => $id,
                'order' => $order,
            ];
            
            saveOrderHistory($history_data);
        }

        return json_custom_response(['message'=> $message, 'status' => true ]);
    }
}
