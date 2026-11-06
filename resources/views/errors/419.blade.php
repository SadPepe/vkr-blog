@extends('errors.layout')

@section('title', 'Сессия истекла')
@section('eyebrow', 'Ошибка 419')
@section('code', '419')
@section('heading', 'Страница устарела')
@section('message', 'Похоже, вы долго не обновляли страницу. Обновите её и попробуйте снова — данные формы придётся ввести заново.')

@section('actions')
    <button class="btn btn-primary" type="button" onclick="location.reload()">Обновить страницу</button>
    <a class="btn btn-secondary" href="{{ url('/') }}">На главную</a>
@endsection
