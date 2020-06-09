<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\Tag;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::all();
        return view('admin.posts.index', ['posts' => $posts]);
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
        return view('admin.posts.create', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'content' => 'required',
            'cover_image_file' => 'image'
        ]);

        // recupero tutti i dati del form
        $dati = $request->all();

        // creo un nuovo oggetto post
        $post = new Post();
        // compilo tutti i dati compilabili in automatico
        $post->fill($dati);

        if(!empty($dati['cover_image_file'])) {
            // l'utente ha impostato un'Immagine
            $cover_image = $dati['cover_image_file'];
            // carico l'immagine
            $cover_image_path = Storage::put('uploads', $cover_image);
            // assegno la path dell'immagine al post
            $post->cover_image = $cover_image_path;
        }

        // recupero il titolo e genero lo slug corrispondente
        $slug_originale = Str::slug($dati['title']);
        $slug = $slug_originale;
        // verifico che nel db non esista uno slug uguale
        $post_stesso_slug = Post::where('slug', $slug)->first();
        $slug_trovati = 1;
        // ciclo finché non trovo uno slug libero (non ancora esistente)
        while(!empty($post_stesso_slug)) {
            $slug = $slug_originale . '-' . $slug_trovati;
            $post_stesso_slug = Post::where('slug', $slug)->first();
            $slug_trovati++;
        }
        // assegno lo slug
        $post->slug = $slug;
        // salvo il post a db
        $post->save();

        if(!empty($dati['tag_id'])) {
            // sono stati selezionati dei tag => li assegno al post
            $post->tags()->sync($dati['tag_id']);
        }

        // faccio redirect all'homepage admin dei post
        return redirect()->route('admin.posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return view('admin.posts.show', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        $categories = Category::all();
        $tags = Tag::all();
        return view('admin.posts.edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags
        ]);
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
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'content' => 'required',
            'cover_image_file' => 'image'
        ]);

        // recupero il post dal db
        $post = Post::find($id);
        $dati = $request->all();

        if(!empty($dati['cover_image_file'])) {
            // se il post aveva già un'immagine di copertina, la cancello prima di collegare quella nuova
            if(!empty($post->cover_image)) {
                // cancello l'immagine precedente
                Storage::delete($post->cover_image);
            }
            // carico la nuova immagine
            $cover_image = $dati['cover_image_file'];
            $cover_image_path = Storage::put('uploads', $cover_image);
            // assegno l'indirizzo della nuova immagine al post
            $dati['cover_image'] = $cover_image_path;
        }

        // aggiorno il post
        $post->update($dati);

        if(!empty($dati['tag_id'])) {
            // sono stati selezionati dei tag => li assegno al post
            $post->tags()->sync($dati['tag_id']);
        } else {
            $post->tags()->sync([]);
        }

        // faccio redirect all'homepage admin dei post
        return redirect()->route('admin.posts.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        $post_image = $post->cover_image;
        if(!empty($post_image)) {
            // elimino l'immagine di copertina
            Storage::delete($post_image);
        }
        if($post->tags->isNotEmpty()) {
            $post->tags()->sync([]);
        }
        $post->delete();
        return redirect()->route('admin.posts.index');
    }
}
