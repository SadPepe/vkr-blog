@extends('errors.layout')

@section('title', 'Технические работы')
@section('eyebrow', 'Ошибка 503')
@section('code', '503')
@section('heading', 'Скоро вернёмся')
@section('message', 'Сайт временно недоступен — мы обновляемся. Загляните через несколько минут.')

@section('actions')
    <button class="btn btn-primary" type="button" onclick="location.reload()">Обновить</button>
    <a class="btn btn-secondary" href="{{ url('/') }}">На главную</a>
@endsection
