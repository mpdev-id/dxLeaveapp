@extends('template.admin')

@section('title', 'Homepage')
@section('content')

<div x-data="{ open: false }">
    <button @click="open = !open" class="btn btn-primary">Click Me</button>
    <div x-show="open" class="text-center text-7xl mt-5 p-5">
        Hello world, I am
        <span class="text-rotate">
            <span>
              <span class="bg-teal-400 text-white px-2 rounded-3xl">Web Developer</span>
              <span class="bg-red-400 text-white px-2 rounded-3xl">Laravel Specialist</span>
              <span class="bg-blue-400 text-white px-2 rounded-3xl">Spiderman</span>
            </span>
          </span>
    </div>
</div>
@endsection