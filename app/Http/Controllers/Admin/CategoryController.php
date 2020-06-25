<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.category.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
       $this->validate($request,[
           'name'=>'required|unique:categories',
          'image'=>'required|image|mimes:png,jpeg, jpg',
         ]);

        // get image from request file
         $image= $request->file('image');
         $slug = str_slug($request->name);
         if (isset($image))
         {

     //make unique name for image

             $currentDate = Carbon::now()->toDateString();
             $imagename = $slug. '-'.$currentDate.'-' .uniqid().'.' .$image->getClientOriginalExtension();

     // check image dir is exist

             if (!Storage::disk('public')->exists('category'))
             {
                 Storage::disk('public')->makeDirectory('category');
             }
     //resize image and upload

             $category= Image::make($image)->resize(1600,479)->stream();
             Storage::disk('public')->put('category/'.$imagename,$category);

             // check image/slider dir is exist

             if (!Storage::disk('public')->exists('category/slider'))
             {
                 Storage::disk('public')->makeDirectory('category/slider');
             }
             //resize image/slider and upload

             $slider= Image::make($image)->resize(500,333)->stream();
             Storage::disk('public')->put('category/slider/'.$imagename,$slider);


         }else{
             $imagename = 'default.png';
         }

         $category = new Category();
         $category-> name = $request->name;
         $category-> slug = $slug;
         $category-> image = $imagename;
        $category->save();
        Toastr::success('Category successfully save','Success');
        return redirect()->route('admin.category.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::find($id);
        return view('admin.category.edit', compact('category'));


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
        $this->validate($request,[
            'name'=>'required',
            'image'=>'mimes:png,jpeg, jpg',

        ]);

        // get image from request file
           $image= $request->file('image');
           $slug = str_slug($request->name);
           $category =Category::find($id);

        if (isset($image))
        {

            //make unique name for image

            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug. '-'.$currentDate.'-' .uniqid().'.' .$image->getClientOriginalExtension();

            // check image dir is exist

            if (!Storage::disk('public')->exists('category'))
            {
                Storage::disk('public')->makeDirectory('category');
            }
            // delete old image
            if (Storage::disk('public')->exists('category/'. $category->image)){
                Storage::disk('public')->delete('category/'.$category->image);
            }
            //resize image and upload

            $categoryImage= Image::make($image)->resize(1600,479)->stream();
            Storage::disk('public')->put('category/'.$imagename,$categoryImage);

            // check image/slider dir is exist

            if (!Storage::disk('public')->exists('category/slider'))
            {
                Storage::disk('public')->makeDirectory('category/slider');
            }
            // delete old image for slider
            if (Storage::disk('public')->exists('category/slider/'. $category->image))
            {
                Storage::disk('public')->delete('category/slider/'. $category->image);
            }
            //resize image/slider and upload

            $slider= Image::make($image)->resize(500,333)->stream();
            Storage::disk('public')->put('category/slider/'.$imagename,$slider);


        }else{
            $imagename = '$category->image';
        }


        $category-> name = $request->name;
        $category-> slug = $slug;
        $category-> image = $imagename;
        $category->save();
        Toastr::success('Category successfully Updated','Success');
        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $category = Category::find($id);
        if (Storage::disk('public')->exists('category/'. $category->image)){
            Storage::disk('public')->delete('category/'.$category->image);
        }
        if (Storage::disk('public')->exists('category/slider/'. $category->image)){
            Storage::disk('public')->delete('category/slider/'.$category->image);
        }
        $category->delete();
        Toastr::success('Category Successfully Deleted', 'success');
        return redirect()->back();
    }
}
