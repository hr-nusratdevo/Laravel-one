<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthorController extends Controller
{
    public function index()
    {
         $authors =User::authors()

            ->withCount('posts')
            ->withCount('comments')
            ->withCount('favorite_posts')
            ->get();
        return view('admin.authors', compact('authors'));

    }



    public function destroy($id)
    {
        $authors = User::findOrFail($id)->delete();
        Toastr::success('Author Successfully Deleted', 'success');
        return redirect()->back();
    }
}
