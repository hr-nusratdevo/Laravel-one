<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Notifications\AuthorPostApproval;
use App\Notifications\SubscriberNotification;
use App\Post;
use App\Subscriber;
use App\Tag;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return view('admin.post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.post.create', compact('categories','tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'title'=>'required',
            'image'=>'required|image|mimes:png,jpeg, jpg',
            'categories'=>'required',
            'tags'=>'required',
            'body'=>'required',
        ]);

        // get image from request file
        $image= $request->file('image');
        $slug = str_slug($request->title);
        if (isset($image))
        {

            //make unique name for image

            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug. '-'.$currentDate.'-' .uniqid().'.' .$image->getClientOriginalExtension();

            // check image dir is exist

            if (!Storage::disk('public')->exists('post'))
            {
                Storage::disk('public')->makeDirectory('post');
            }
            //resize image and upload

            $postImage= Image::make($image)->resize(1600,1066)->stream();
            Storage::disk('public')->put('post/'.$imageName,$postImage);



        }else{
            $imageName = 'default.png';
        }

            $post = new Post();
            $post ->user_id= Auth::id();
            $post-> title = $request->title;
            $post-> slug = $slug;
            $post-> image = $imageName;
            $post-> body = $request->body;
         if (isset($request->status)){
            $post-> status = true;
          } else
              {$post-> status = false;

             }
            $post-> is_approved = true;
            $post->save();
            $post-> categories()->attach($request-> categories );
            $post-> tags()->attach($request-> tags );


            $subscribers =Subscriber::all();
            foreach ($subscribers as $subscriber)
            {
               Notification::route('mail', $subscriber->email)
                   ->notify(new SubscriberNotification($post));
            }



            Toastr::success('Post Successfully Save','Success');
            return redirect()->route('admin.post.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.post.show' , compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.post.edit', compact('post','categories','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
         $this->validate($request,[
            'title'=>'required',
            'image'=>'image|mimes:png,jpeg, jpg',
            'categories'=>'required',
            'tags'=>'required',
            'body'=>'required',
        ]);

        // get image from request file
        $image= $request->file('image');
        $slug = str_slug($request->title);
        if (isset($image))
        {

            //make unique name for image

            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug. '-'.$currentDate.'-' .uniqid().'.' .$image->getClientOriginalExtension();

            // check image dir is exist

            if (!Storage::disk('public')->exists('post'))
            {
                Storage::disk('public')->makeDirectory('post');
            }

            //delete old post image
            if (Storage::disk('public')->exists('post/'.$post->image))
            {
                Storage::disk('public')->delete('post/'.$post->image);
            }
            //resize image and upload

            $postImage= Image::make($image)->resize(1600,1066)->stream();
            Storage::disk('public')->put('post/'.$imageName,$postImage);



        }else{
            $imageName = $post->image;
        }


        $post ->user_id= Auth::id();
        $post-> title = $request->title;
        $post-> slug = $slug;
        $post-> image = $imageName;
        $post-> body = $request->body;
        if (isset($request->status)){
            $post-> status = true;
        } else
        {$post-> status = false;

        }
        $post-> is_approved = true;
        $post->save();
        $post-> categories()->sync($request-> categories );
        $post-> tags()->sync($request-> tags );


        Toastr::success('Post Successfully Updated','Success');
        return redirect()->route('admin.post.index');
    }



    public function pending()
    {
        $posts = Post::where('is_approved', false)->get();
        return view('admin.post.pending',compact('posts'));
    }

    public function approve($id)
    {
        $post = Post::find($id);
        if ($post ->is_approved == false)
        {
            $post ->is_approved = true;
            $post->save();
            $post->user->notify(new AuthorPostApproval($post));
            Toastr::success('Post Successfully Approved','Success');

            $subscribers =Subscriber::all();
            foreach ($subscribers as $subscriber)
            {
                Notification::route('mail', $subscriber->email)
                    ->notify(new SubscriberNotification($post));
            }

        }else{ Toastr::info('Post Is Already Approved','info');}
        return redirect()->back();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if (Storage::disk('public')->exists('post/'.$post->image))
        {
            Storage::disk('public')->delete('post/'.$post->image);
        }
         $post->categories()->detach();
         $post->tags()->detach();
         $post->delete();


        Toastr::success('Post Successfully Deleted','Success');
        return redirect()->back ();
    }
}
