<!doctype html>
<html lang="{{str_replace('_', '-', app()->getLocale())}}"@if($theme) data-theme="{{$theme}}"@endif>
<head>
  <meta charset="utf-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{config('app.name')}}</title>
  @vite('resources/css/app.css')
</head>
<body class="h-screen overflow-y-scroll scrollbar-visible {{$classnames}}">
<x-topnav :leftNav="$leftNav"/>
@if($leftNav)
  <x-left-nav/>
@endif
<div @class(['mt-16', 'lg:pl-56' => $leftNav])>{{$slot}}</div>
@vite('resources/js/app.js')
</body>
</html>
