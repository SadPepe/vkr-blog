@extends('errors.layout')

@section('title', 'Страница не найдена')
@section('eyebrow', 'Ошибка 404')
@section('code', '404')
@section('heading', 'Такой страницы нет')
@section('message', 'Возможно, статья была удалена или адрес набран с ошибкой. Вернитесь в ленту — там точно есть что почитать.')

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">На главную</a>
    <a class="btn btn-secondary" href="{{ url('/search') }}">Поиск по блогу</a>
    <button class="btn btn-secondary" type="button" onclick="history.back()">Назад</button>
@endsection
