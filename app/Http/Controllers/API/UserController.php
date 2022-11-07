<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;
use Validator;
use Hash;
Use Auth;
use App\Http\Resources\API\UserResource;
use Illuminate\Support\Facades\Password;
use App\Models\Country;
use App\Models\City;
use App\Models\Order;
use App\Http\Resources\API\OrderResource;
use Carbon\Carbon;
use App\Models\AppSetting;
use App\Models\DeliveryManDocument;
use App\Models\Payment;

class UserController extends Controller
{

    public function dashboard(Request $request)
    {
        $dashboard_data = [];
        $dashboard_data['total_country'] = Country::count();
        $dashboard_data['total_city'] = City::count();
        $dashboard_data['total_client'] = User::userCount('client');
        $dashboard_data['total_delivery_man'] = User::userCount('delivery_man');
        $dashboard_data['total_order'] = Order::myOrder()->count();
        $dashboard_data['today_register_user'] = User::where('user_type','client')->whereDate('created_at',today())->count();
               
        $dashboard_data['app_setting'] = AppSetting::first();
        /*
        $upcoming_order = Order::myOrder()->whereDate('pickup_datetime','>=',Carbon::now()->format('Y-m-d H:i:s'))->orderBy('pickup_datetime','asc')->paginate(10);
        $dashboard_data['upcoming_order'] = OrderResource::collection($upcoming_order);
        */

        $upcoming_order = Order::myOrder()->whereNotIn('status',['draft', 'cancelled', 'completed'])->whereNotNull('pickup_point->start_time')
                        ->where('pickup_point->start_time','>=',Carbon::now()->format('Y-m-d H:i:s'))
                        ->orderBy('pickup_point->start_time','asc')->paginate(10);
        $dashboard_data['upcoming_order'] = OrderResource::collection($upcoming_order);

        $recent_order = Order::myOrder()->whereDate('date','<=',Carbon::now()->format('Y-m-d'))->orderBy('date','desc')->paginate(10);
        $dashboard_data['recent_order'] = OrderResource::collection($recent_order);

        $client = User::where('user_type','client')->orderBy('created_at','desc')->paginate(10);
        $dashboard_data['recent_client'] = UserResource::collection($client);

        $delivery_man = User::where('user_type','delivery_man')->orderBy('created_at','desc')->paginate(10);
        $dashboard_data['recent_delivery_man'] = UserResource::collection($delivery_man);

        $sunday = strtotime('sunday -1 week');
	    $sunday = date('w', $sunday) === date('w') ? $sunday + 7*86400 : $sunday;
        $saturday = strtotime(date('Y-m-d',$sunday).' +6 days');

        $week_start = date('Y-m-d 00:00:00',$sunday);
        $week_end = date('Y-m-d 23:59:59',$saturday);

        $dashboard_data['week'] = [
            'week_start'=> $week_start,
            'week_end'  => $week_end
        ];
        $weekly_order_count = Order::selectRaw('DATE_FORMAT(created_at , "%w") as days , DATE_FORMAT(created_at , "%Y-%m-%d") as date' )
                        ->whereBetween('created_at', [ $week_start, $week_end ])
                        ->get()->toArray();
        
                        $data = [];
        
        $order_collection = collect($weekly_order_count);
        for($i = 0; $i < 7 ; $i++){
            $total = $order_collection->filter(function ($value, $key) use($week_start, $i){
                return $value['date'] == date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day'));
            })->count();
            
            $data[] = [
                'day' => date('l', strtotime($week_start . ' + ' . $i . 'day')),
                'total' => $total,
                'date' => date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day')),    
            ];
        }

        $dashboard_data['weekly_order_count'] = $data;

        $user_week_report = User::selectRaw('DATE_FORMAT(created_at , "%w") as days , DATE_FORMAT(created_at , "%Y-%m-%d") as date' )
                        ->where('user_type','client')
                        ->whereBetween('created_at', [ $week_start, $week_end ])
                        ->get()->toArray();
        $data = [];
        
        $user_collection = collect($user_week_report);
        for($i = 0; $i < 7 ; $i++){
            $total = $user_collection->filter(function ($value, $key) use($week_start,$i){
                return $value['date'] == date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day'));
            })->count();
            
            $data[] = [
                'day' => date('l', strtotime($week_start . ' + ' . $i . 'day')),
                'total' => $total,
                'date' => date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day')),    
            ];
        }

        $dashboard_data['user_weekly_count'] = $data;
      
        $user = auth()->user();
        $dashboard_data['all_unread_count']  = isset($user->unreadNotifications) ? $user->unreadNotifications->count() : 0;
        
        $weekly_payment_report = Payment::selectRaw('DATE_FORMAT(created_at , "%w") as days , DATE_FORMAT(created_at , "%Y-%m-%d") as date, total_amount ' )
                        ->where('payment_status','paid')
                        ->whereBetween('created_at', [ $week_start, $week_end ])
                        ->get()->toArray();
        $data = [];

        $dashboard_data['weekly_sql'] = Payment::selectRaw('DATE_FORMAT(created_at , "%w") as days , DATE_FORMAT(created_at , "%Y-%m-%d") as date, total_amount ' )
                                        ->where('payment_status','paid')->whereBetween('created_at', [ $week_start, $week_end ])->toSql();
        $payment_collection = collect($weekly_payment_report);
        for($i = 0; $i < 7 ; $i++){
            $total_amount = $payment_collection->filter(function ($value, $key) use($week_start,$i){
                return $value['date'] == date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day'));
            })->sum('total_amount');
            
            $data[] = [
                'day' => date('l', strtotime($week_start . ' + ' . $i . 'day')),
                'total_amount' => $total_amount,
                'date' => date('Y-m-d',strtotime($week_start. ' + ' . $i . 'day')),    
            ];
        }

        $dashboard_data['weekly_payment_report'] = $data;

        return json_custom_response($dashboard_data);
    }

    public function register(UserRequest $request)
    {
        $input = $request->all();
                
        $password = $input['password'];
        $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'client';
        $input['password'] = Hash::make($password);

        if( in_array($input['user_type'],['delivery_man']))
        {
            $input['status'] = isset($input['status']) ? $input['status']: 0;
        }
        $user = User::create($input);
        
        $message = __('message.save_form',['form' => __('message.'.$input['user_type']) ]);
        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        $response = [
            'message' => $message,
            'data' => $user
        ];
        return json_custom_response($response);
    }

    public function login()
    {      
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            
            $user = Auth::user();

            if(request('player_id') != null){
                $user->player_id = request('player_id');
            }
            
            if(request('fcm_token') != null){
                $user->fcm_token = request('fcm_token');
            }

            $user->save();
            
            $success = $user;
            $success['api_token'] = $user->createToken('auth_token')->plainTextToken;
            $success['profile_image'] = getSingleMedia($user,'profile_image',null);
            $is_verified_delivery_man = false;
            if($user->user_type == 'delivery_man') {
                $is_verified_delivery_man = DeliveryManDocument::verifyDeliveryManDocument($user->id);
            }
            $success['is_verified_delivery_man'] = (int) $is_verified_delivery_man;
            unset($success['media']);

            return json_custom_response([ 'data' => $success ], 200 );
        }
        else{
            $message = __('auth.failed');
            
            return json_message_response($message,400);
        }
    }

    public function userList(Request $request)
    {
        $user_type = isset($request['user_type']) ? $request['user_type'] : 'client';
        
        $user_list = User::query();
        
        $user_list->when(request('user_type'), function ($q) use($user_type) {
            return $q->where('user_type', $user_type);
        });

        $user_list->when(request('country_id'), function ($q) {
            return $q->where('country_id', request('country_id'));
        });

        $user_list->when(request('city_id'), function ($q) {
            return $q->where('city_id', request('city_id'));
        });

        if( $request->has('status') && isset($request->status) )
        {
            $user_list = $user_list->where('status',request('status'));
        }
        
        if( $request->has('is_deleted') && isset($request->is_deleted) && $request->is_deleted){
            $user_list = $user_list->withTrashed();
        }


        $per_page = config('constant.PER_PAGE_LIMIT');
        if( $request->has('per_page') && !empty($request->per_page))
        {
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page == -1 ){
                $per_page = $user_list->count();
            }
        }
        
        $user_list = $user_list->paginate($per_page);

        $items = UserResource::collection($user_list);

        $response = [
            'pagination' => json_pagination_response($items),
            'data' => $items,
        ];
        
        return json_custom_response($response);
    }

    public function userDetail(Request $request)
    {
        $id = $request->id;

        $user = User::where('id',$id)->withTrashed()->first();
        if(empty($user))
        {
            $message = __('message.user_not_found');
            return json_message_response($message,400);   
        }

        $user_detail = new UserResource($user);

        $response = [
            'data' => $user_detail
        ];
        return json_custom_response($response);

    }

    public function changePassword(Request $request){
        $user = User::where('id',\Auth::user()->id)->first();

        if($user == "") {
            $message = __('message.user_not_found');
            return json_message_response($message,400);   
        }
           
        $hashedPassword = $user->password;

        $match = Hash::check($request->old_password, $hashedPassword);

        $same_exits = Hash::check($request->new_password, $hashedPassword);
        if ($match)
        {
            if($same_exits){
                $message = __('message.old_new_pass_same');
                return json_message_response($message,400);
            }

			$user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();
            
            $message = __('message.password_change');
            return json_message_response($message,200);
        }
        else
        {
            $message = __('message.valid_password');
            return json_message_response($message,400);
        }
    }

    public function updateProfile(Request $request)
    {   
        $user = \Auth::user();
        if($request->has('id') && !empty($request->id)){
            $user = User::where('id',$request->id)->first();
        }
        if($user == null){
            return json_message_response(__('message.not_found_entry',['name' => __('message.client')]),400);
        }

        $user->fill($request->all())->update();

        if(isset($request->profile_image) && $request->profile_image != null ) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        $user_data = User::find($user->id);
        
        $message = __('message.updated');
        // $user_data['profile_image'] = getSingleMedia($user_data,'profile_image',null);
        unset($user_data['media']);
        $response = [
            'data' => new UserResource($user_data),
            'message' => $message
        ];
        return json_custom_response( $response );
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if($request->is('api*')){

            $clear = request('clear');
            if( $clear != null ) {
                $user->$clear = null;
            }
            $user->save();
            return json_message_response('Logout successfully');
        }
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($response), 'status' => true], 200)
            : response()->json(['message' => __($response), 'status' => false], 400);
    }
    
    public function socialLogin(Request $request)
    {
        $input = $request->all();

        $user_data = User::where('email',$input['email'])->first();

        if($input['login_type'] === 'mobile'){
            $user_data = User::where('username',$input['username'])->where('login_type','mobile')->first();
        }

        if( $user_data != null ) {
            if( !isset($user_data->login_type) || $user_data->login_type  == '' )
            {
                if($request->login_type === 'google')
                {
                    $message = __('validation.unique',['attribute' => 'email' ]);
                } else {
                    $message = __('validation.unique',['attribute' => 'username' ]);
                }
                return json_message_response($message,400);
            }
            $message = __('message.login_success');
        } else {

            if($request->login_type === 'google')
            {
                $key = 'email';
                $value = $request->email;
            } else {
                $key = 'username';
                $value = $request->username;
            }
            
            $trashed_user_data = User::where($key,$value)->whereNotNull('login_type')->withTrashed()->first();
            
            if ($trashed_user_data != null && $trashed_user_data->trashed())
            {
                if($request->login_type === 'google'){
                    $message = __('validation.unique',['attribute' => 'email' ]);
                } else {
                    $message = __('validation.unique',['attribute' => 'username' ]);
                }
                return json_message_response($message,400);
            }

            if($request->login_type === 'mobile' && $user_data == null ){
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false
                ];
                return json_custom_response($otp_response);
            }

            $password = !empty($input['accessToken']) ? $input['accessToken'] : $input['email'];
            
            $input['user_type']  = "user";
            $input['display_name'] = $input['first_name']." ".$input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'client';
            $user = User::create($input);
    
            $user_data = User::where('id',$user->id)->first();
            $message = __('message.save_form',['form' => $input['user_type'] ]);
        }
        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;
        $user_data['profile_image'] = getSingleMedia($user_data,'profile_image',null);
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $user_data
        ];
        return json_custom_response($response);
    }

    public function updateUserStatus(Request $request)
    {
        $user_id = $request->id;
        $user = User::where('id',$user_id)->first();

        if($user == "") {
            $message = __('message.user_not_found');
            return json_message_response($message,400);
        }
        $user->status = $request->status;
        $user->save();

        $message = __('message.update_form',['form' => __('message.status') ]);
        $response = [
            'data' => new UserResource($user),
            'message' => $message
        ];
        return json_custom_response($response);
    }

    public function updateAppSetting(Request $request)
    {
        $data = $request->all();
        AppSetting::updateOrCreate(['id' => $request->id],$data);
        $message = __('message.save_form',['form' => __('message.app_setting') ]);
        $response = [
            'data' => AppSetting::first(),
            'message' => $message
        ];
        return json_custom_response($response);
    }

    public function getAppSetting(Request $request)
    {
        if($request->has('id') && isset($request->id)){
            $data = AppSetting::where('id',$request->id)->first();
        } else {
            $data = AppSetting::first();
        }

        return json_custom_response($data);
    }

    public function deleteUser(Request $request)
    {
        $user = User::find($request->id);

        $message = __('message.msg_fail_to_delete',['item' => __('message.'.$user->user_type) ]);
        
        if( $user != '' ) {
            $user->delete();
            $message = __('message.msg_deleted',['name' => __('message.'.$user->user_type) ]);
        }
        
        if(request()->is('api/*')){
            return json_custom_response(['message'=> $message , 'status' => true]);
        }
    }

    public function userAction(Request $request)
    {
        $id = $request->id;
        $user = User::withTrashed()->where('id',$id)->first();
        
        $message = __('message.not_found_entry',['name' => __('message.'.$user->user_type) ]);
        if($request->type === 'restore'){
            $user->restore();
            $message = __('message.msg_restored',['name' => __('message.'.$user->user_type) ]);
        }

        if($request->type === 'forcedelete'){
            $user->forceDelete();
            $message = __('message.msg_forcedelete',['name' => __('message.'.$user->user_type) ]);
        }

        return json_custom_response(['message'=> $message, 'status' => true]);
    }
}
