@php
    use App\View\DataModels\Main;
    $Main = Main::from($main);
@endphp
<!doctype html>
<html lang="{{str_replace('_', '-', app()->getLocale())}}"@if($Main->theme) data-theme="{{$Main->theme}}"@endif>
<head>
  <meta charset="utf-8">
  <title>{{config('app.name')}}</title>
  @head
  @vite('resources/css/app.css')
</head>
<body class="h-screen overflow-y-scroll scrollbar-visible {{$Main->classnames}}">
<x-topnav :topnav="$Main->topnav()"/>
@if($Main->leftNav)
  <x-left-nav/>
@elseif($Main->adminNav)
  <x-admin-nav/>
@endif
<div @class(['mt-16', 'lg:pl-56' => $Main->nav()])>
  <div class="min-h-[calc(100vh-4rem)]">{{$slot}}</div>
  <x-footer/>
</div>
@vite('resources/js/app.js')
</body>
</html>