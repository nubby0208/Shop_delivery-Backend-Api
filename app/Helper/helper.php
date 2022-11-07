<?php
use \App\Models\OrderHistory;
use \App\Models\StaticData;
use App\Models\User;
use App\Models\Order;
use App\Notifications\OrderNotification;
use App\Notifications\CommonNotification;
use App\Notifications\OrderCreate;

function json_message_response( $message, $status_code = 200)
{	
	return response()->json( [ 'message' => $message ], $status_code );
}

function json_custom_response( $response, $status_code = 200 )
{
    return response()->json($response,$status_code);
}

function json_list_response( $data )
{
    return response()->json(['data' => $data]);
}

function json_pagination_response($items)
{
    return [
        'total_items' => $items->total(),
        'per_page' => $items->perPage(),
        'currentPage' => $items->currentPage(),
        'totalPages' => $items->lastPage()
    ];
}

function saveOrderHistory($data)
{
    $admin = \App\Models\User::where('user_type','admin')->first();
    $data['datetime'] = date('Y-m-d H:i:s');
    
    $user_type = auth()->user()->user_type;
    $history_data = [];
    $sendTo = [];
    $order_id = $data['order']->id;
    $data['order'] = Order::find($order_id);
    switch ($data['history_type']) {
        case 'draft':
            $data['history_message'] = __('message.order_draft');
            $history_data = [
                'client_id' => $data['order']->client_id,
                'client_name' => isset($data['order']->client) ? $data['order']->client->name : '',
            ];
            break;

        case 'create':
            $data['history_message'] = __('message.order_create');
            $history_data = [
                'client_id' => $data['order']->client_id,
                'client_name' => isset($data['order']->client) ? $data['order']->client->name : '',
            ];
            $sendTo = removeValueFromArray(['admin', 'client'], $user_type);
            break;
        case 'active':
            $data['history_message'] = __('message.order_active');
            $history_data = [
                'client_id' => $data['order']->client_id,
                'client_name' => isset($data['order']->client) ? $data['order']->client->name : '',
            ];
            $sendTo = ['client'];
            break;
        case 'payment_status_message':
            $data['history_message'] = __('message.payment_status_message', [ 'status' => $data['payment_status'], 'id' => $order_id  ]);

            $history_data = [
                'payment_status'=> $data['payment_status'],
                'order_id' => $data['order_id'],
            ];
            break;
            $sendTo = ['admin','client'];
        case 'delayed':
            $data['history_message'] = __('message.order_delayed');
            $history_data = [
                'reason' => $data['order']->reason,
                'status' => $data['order']->status,
                'status_label' => StaticData::orderStatus($data['order']->status),
            ];
            $sendTo = removeValueFromArray(['admin', 'client', 'delivery_man'],$user_type);
            break;
        
        case 'cancelled':
            $data['history_message'] = __('message.cancelled_order');
            $history_data = [
                'reason' => $data['order']->reason,
                'status' => $data['order']->status,
                'status_label' => StaticData::orderStatus($data['order']->status),
            ];
            $sendTo = removeValueFromArray(['admin', 'client', 'delivery_man'],$user_type);
            break;
        
        case 'courier_assigned':
            $data['history_message'] = __('message.courier_assigned.delivery_man',[ 'id' => $order_id ]);
            $history_data = [
                'delivery_man_id' => $data['order']->delivery_man_id,
                'delivery_man_name' => optional($data['order']->delivery_man)->name,
                'auto_assign' => $data['order']->auto_assign,
            ];
            $sendTo = ['client','delivery_man'];
            break;
        case 'courier_auto_assign_cancelled':
            $data['history_message'] = __('message.courier_auto_assign_cancelled.client',[ 'id' => $order_id, 'delivery_person' => optional($data['order']->delivery_man)->name ]);
            $history_data = [
                'delivery_man_id' => $data['order']->delivery_man_id,
                'delivery_man_name' => optional($data['order']->delivery_man)->name,
                'auto_assign' => $data['order']->auto_assign,
            ];
            break;
        case 'courier_transfer':
            $data['history_message'] = __('message.courier_transfer.delivery_man', ['id' => $order_id ]);
            $history_data = [
                'delivery_man_id' => $data['order']->delivery_man_id,
                'delivery_man_name' => optional($data['order']->delivery_man)->name,
            ];
            $sendTo = ['delivery_man'];
            break;
        
        case 'courier_picked_up':
            $data['history_message'] = __('message.courier_picked_up');
            
            $history_data = [
                'delivery_man_id' => $data['order']->delivery_man_id,
                'delivery_man_name' => optional($data['order']->delivery_man)->name,
            ];
            $sendTo = ['admin', 'client'];
            break;
        case 'courier_departed':
            $data['history_message'] = __('message.courier_departed', ['id' => $order_id ]);
            $history_data = [
                'delivery_man_id' => $data['order']->delivery_man_id,
                'delivery_man_name' => optional($data['order']->delivery_man)->name,
            ];
            $sendTo = ['admin', 'client'];
            break;
        
        case 'courier_arrived':
            $data['history_message'] = __('message.courier_arrived');
            
            $history_data = [
                'order_id' => $data['order_id'],
            ];
            $sendTo = ['admin', 'client'];
            break;
            
        case 'completed':
            $data['history_message'] = __('message.order_completed', ['id' => $order_id ]);
            
            $history_data = [
                'order_id' => $data['order_id'],
            ];
            $sendTo = ['admin', 'client'];
            break;
        case 'failed':
            $data['history_message'] = __('message.order_failed', ['id' => $order_id, 'reason' => $data['order']->reason ]);
            $history_data = [
                'reason' => $data['order']->reason,
                'status' => $data['order']->status,
                'status_label' => StaticData::orderStatus($data['order']->status),
            ];
            $sendTo = removeValueFromArray(['admin', 'client', 'delivery_man'],$user_type);
            break;
        default:
            # code...
            $history_data = [];
            break;
    }
    $data['history_data'] = json_encode($history_data);

    OrderHistory::create($data);

    $notification_data = [
        'id'   => $data['order']->id,
        'type' => $data['history_type'],
        'subject' => __('message.order_notification_title',[ 'id' => $order_id ]),
        'message' => $data['history_message'],
    ];

    foreach($sendTo as $send){
        
        switch ($send)
        {
            case 'admin':
                $user = User::whereUserType('admin')->first();
                if($data['history_type'] == 'create') {
                    $template_data = $notification_data;
                    $template_data['message_subject'] = "New Order #".$data['order']->id." Created";
                    $template_data['message_body'] = "<p>Hi,</p><p>The order #".$data['order']->id." has been created by ". optional($data['order']->client)->name.".</p>\n\n<p>Please login to your account and check order details.</p>\n\n<p>Regards,<br />". env('APP_NAME') ."</p>";
                    $user->notify(new OrderCreate($template_data));
                }
                break;
            case 'client':
                $user = User::whereId( $data['order']->client_id )->first();
                if($data['history_type'] == 'courier_assigned') {
                    $notification_data['message'] = __('message.courier_assigned.client',[ 'id' => $order_id, 'delivery_person' => $history_data['delivery_man_name'] ]);
                }

                if($data['history_type'] == 'courier_transfer') {
                    $notification_data['message'] = __('message.courier_transfer.client',[ 'id' => $order_id, 'delivery_person' => $history_data['delivery_man_name'] ]);
                }
                break;

            case 'delivery_man':
                $user = User::whereId( $data['order']->delivery_man_id )->first();
                if($data['history_type'] == 'courier_assigned') {
                    $notification_data['message'] = __('message.courier_assigned.delivery_man',[ 'id' => $order_id ]);
                }

                if($data['history_type'] == 'courier_transfer') {
                    $notification_data['message'] = __('message.courier_transfer.delivery_man',[ 'id' => $order_id ]);
                }
                break;
        }
        
        if($user != null){

            $user->notify(new OrderNotification($notification_data));
            $user->notify(new CommonNotification($notification_data['type'], $notification_data));
        }
    }

}

function removeValueFromArray($array = [], $find = null)
{
    foreach (array_keys($array, $find) as $key) {
        unset($array[$key]);
    }

    return array_values($array);
}

function getSingleMedia($model, $collection = 'profile_image', $skip=true   )
{
    if (!\Auth::check() && $skip) {
        return asset('images/user/user.png');
    }
    $media = null;
    if ($model !== null) {
        $media = $model->getFirstMedia($collection);
    }

    if (getFileExistsCheck($media))
    {
        return $media->getFullUrl();
    } else {
        switch ($collection) {
            case 'profile_image':
                $media = asset('images/user/user.png');
                break;
            case 'site_logo':
                $media = asset('images/logo.png');
                break;
            case 'site_favicon':
                $media = asset('images/favicon.png');
                break;
            default:
                $media = asset('images/default.png');
                break;
        }
        return $media;
    }
}

function getFileExistsCheck($media)
{
    $mediaCondition = false;

    if($media) {
        if($media->disk == 'public') {
            $mediaCondition = file_exists($media->getPath());
        } else {
            $mediaCondition = \Storage::disk($media->disk)->exists($media->getPath());
        }
    }
    return $mediaCondition;
}

function uploadMediaFile($model,$file,$name)
{
    if($file) {
        $model->clearMediaCollection($name);
        if (is_array($file)){
            foreach ($file as $key => $value){
                $model->addMedia($value)->toMediaCollection($name);
            }
        }else{
            $model->addMedia($file)->toMediaCollection($name);
        }
    }

    return true;
}

function getAttachments($attchments)
{
    $files = [];
    if (count($attchments) > 0) {
        foreach ($attchments as $attchment) {
            if (getFileExistsCheck($attchment)) {
                array_push($files, $attchment->getFullUrl());
            }
        }
    }

    return $files;
}

function getMediaFileExit($model, $collection = 'profile_image')
{
    if($model==null){
        return asset('images/user/user.png');
    }

    $media = $model->getFirstMedia($collection);

    return getFileExistsCheck($media);
}

function timeAgoFormat($date)
{
    if($date == null){
        return '-';
    }

    $diff_time= \Carbon\Carbon::createFromTimeStamp(strtotime($date))->diffForHumans();

    return $diff_time;
}

function convertUnitvalue($unit)
{
    switch ($unit) {
        case 'mile':
            return 3956;
            break;
        default:
            return 6371;
            break;
    }
}