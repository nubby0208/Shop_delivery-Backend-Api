<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryManDocument;

class DeliveryManDocumentController extends Controller
{
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

        $result = DeliveryManDocument::updateOrCreate(['id' => $request->id], $data);
        
        uploadMediaFile($result,$request->delivery_man_document, 'delivery_man_document');

        $message = __('message.update_form',[ 'form' => __('message.delivery_man_document') ] );
		if($result->wasRecentlyCreated){
			$message = __('message.save_form',[ 'form' => __('message.delivery_man_document') ] );
		}

        if($request->is('api/*')) {
            return json_message_response($message);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delivery_man_document = DeliveryManDocument::find($id);
        $message = __('message.msg_fail_to_delete',['item' => __('message.delivery_man_document')] );
        
        if($delivery_man_document != '') {
            $delivery_man_document->delete();
            $message = __('message.msg_deleted',['name' => __('message.delivery_man_document')] );
        }
        if(request()->is('api/*')){
            return json_custom_response(['message'=> $message , 'status' => true]);
        }
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $delivery_man_document = DeliveryManDocument::withTrashed()->where('id',$id)->first();
        $message = __('message.not_found_entry',['name' => __('message.delivery_man_document')] );
        if($request->type === 'restore'){
            $delivery_man_document->restore();
            $message = __('message.msg_restored',['name' => __('message.delivery_man_document')] );
        }

        if($request->type === 'forcedelete'){
            $delivery_man_document->forceDelete();
            $message = __('message.msg_forcedelete',['name' => __('message.delivery_man_document')] );
        }

        return json_custom_response(['message'=> $message, 'status' => true]);
    }
}
