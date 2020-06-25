<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings');
    }

    public function update(Request $request)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required |email',
            'image'=>'image|mimes:png,jpeg, jpg',

        ]);

        // get image from request file
        $image= $request->file('image');
        $slug = str_slug($request->name);
        $user = User::findOrFail(Auth::id());
        if (isset($image))
        {

            //make unique name for image

            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug. '-'.$currentDate.'-' .uniqid().'.' .$image->getClientOriginalExtension();

            // check image dir is exist

            if (!Storage::disk('public')->exists('profile'))
            {
                Storage::disk('public')->makeDirectory('profile');
            }

            //delete old post image
            if (Storage::disk('public')->exists('profile/'.$user->image))
            {
                Storage::disk('public')->delete('profile/'.$user->image);
            }
            //resize image and upload

            $profile= Image::make($image)->resize(500,500)->stream();
            Storage::disk('public')->put('profile/'.$imageName,$profile);



        }else{
            $imageName = $user->image;
        }

        $user-> name = $request->name;
        $user-> email = $request->email;
        $user-> image = $imageName;
        $user-> about = $request->about;
        $user->save();


        Toastr::success('Profile Successfully Updated','Success');
        return redirect()->back();
    }


    public function updatepassword(Request $request)
    {
        $this->validate($request,[
            'old_password'=>'required',
            'password'=>'required|confirmed']);

        $hastedpassword = Auth::user()->password;

        if (Hash::check($request->old_password ,$hastedpassword)) {
            if (!Hash::check($request->password, $hastedpassword))
            {
                $user=User::find(Auth::id());
                $user->password = Hash::make($request->password );
                $user->save();
                Toastr::success('Password Successfully Change','Success');
                Auth::logout();
                return redirect()->back();
            }else
            {
                Toastr::error('New Password Cannot be same as old Password','Error');
                return redirect()->back();
            }

        }else
        {
            Toastr::error('Current Password not match','Error');
            return redirect()->back();
        }
    }
}
