@extends('web.layouts.app')

@section('title', app()->getLocale() === 'km' ? 'សេវាកម្ម' : 'Service')

@section('content')
    {{-- Empty service page placeholder as requested. --}}
    <section aria-label="{{ app()->getLocale() === 'km' ? 'ទំព័រសេវាកម្ម' : 'Service page' }}" style="min-height: 320px;"></section>
@endsection
