<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Models\Post;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->latest('id')->limit(30)->get();

        return view('feed', compact('posts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:280']]);

        $post = $request->user()->posts()->create($data);

        PostCreated::dispatch($post);

        if ($request->expectsJson()) {
            return response()->json(['id' => $post->id], 201);
        }

        return back();
    }
}
