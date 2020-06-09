@extends('layouts.public')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h1>Lista di tutti i post con il tag: {{ $tag->name }}</h1>
                <ul>
                    @forelse ($posts as $post)
                        <li>
                            <a href="{{ route('blog.show', ['slug' => $post->slug]) }}">
                                {{ $post->title }}
                            </a>
                        </li>
                    @empty
                        <li>Non ci sono ancora post con questo tag</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
