<?php

namespace App\Http\Controllers\Admin;

use App\Subscriber;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriberController extends Controller
{
    public function index()
    {
       $subscribers = Subscriber::Latest()->get();
        return view('admin.subscriber', compact('subscribers'));
    }



    public function destroy($subscriber)
    {
        $subscriber = Subscriber::findOrFail($subscriber)->delete();
        Toastr::success('Subscriber Successfully Deleted','Success');
        return redirect()->back();
    }
}
