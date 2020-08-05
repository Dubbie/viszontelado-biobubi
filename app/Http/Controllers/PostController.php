<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['create', 'store', 'update', 'delete']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('post.index')->with([
            'posts' => Post::orderByDesc('created_at')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'post-title' => 'required',
            'post-thumbnail' => 'required|file|image',
            'post-content' => 'required',
        ]);


        $thumbnailPath = $request->file('post-thumbnail')->storePublicly('public/thumbnails');

        $post = new Post();
        $post->title = $data['post-title'];
        $post->thumbnail_path = $thumbnailPath;
        $post->content = $data['post-content'];
        $post->user_id = \Auth::id();
        $post->save();

        return redirect(action('PostController@index'))->with([
            'success' => 'Bejegyzés sikeresen hozzáadva',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $postId
     * @return \Illuminate\Http\Response
     */
    public function destroy($postId)
    {
        $post = Post::find($postId);
        \Storage::delete($post->thumbnail_path);

        // Im dumb as fuck
        try {
            $post->delete();
        } catch (\Exception $e) {
            \Log::error('Hiba történt a bejegyzés törlésekor!');
            \Log::error('Bejegyzés azonosító: ' . $postId);
            \Log::error(sprintf('%s %s', $e->getCode(), $e->getMessage()));

            return redirect(url()->previous(action('PostController@index')))->with([
                'error' => 'Hiba történt a bejegyzés törlésekor!',
            ]);
        }

        return redirect(url()->previous(action('PostController@index')))->with([
            'success' => 'Bejegyzés sikeresen törölve!',
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function handleUpload(Request $request) {
        if ($request->file('upload') && $request->file('upload')->isValid()) {
            $path = $request->file('upload')->storePublicly('public/uploads');
            $outputPath = str_replace('public/', '', $path);

            return [
                'url' => url('/storage/' . $outputPath)
            ];
        } else {
            dd($request->file('upload')->getError());
        }
    }

    /**
     * @param $postId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showPublic($postId) {
        $post = Post::find($postId);

        return view('post.show')->with([
            'post' => $post,
        ]);
    }
}
