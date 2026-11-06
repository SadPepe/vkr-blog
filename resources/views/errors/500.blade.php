@extends('errors.layout')

@section('title', 'Внутренняя ошибка')
@section('eyebrow', 'Ошибка 500')
@section('code', '500')
@section('heading', 'Что-то сломалось')
@section('message', 'Мы уже разбираемся. Попробуйте обновить страницу через минуту. Если не пройдёт — напишите администратору.')

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">На главную</a>
    <button class="btn btn-secondary" type="button" onclick="location.reload()">Обновить</button>
@endsection
