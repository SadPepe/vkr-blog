@extends('errors.layout')

@section('title', 'Доступ закрыт')
@section('eyebrow', 'Ошибка 403')
@section('code', '403')
@section('heading', 'Сюда нельзя')
@section('message', 'У вас нет прав на этот раздел. Если это ошибка — войдите под нужным аккаунтом или вернитесь на главную.')

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">На главную</a>
    <a class="btn btn-secondary" href="{{ url('/login') }}">Войти</a>
@endsection
