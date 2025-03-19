<!doctype html>
<head>
  <meta charset="utf-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{config('app.name')}}</title>
  @vite('resources/css/app.css')
</head>
<body class="h-screen overflow-y-scroll scrollbar-visible {{$classnames}}">
<x-topnav/>
<div class="mt-16">{{$slot}}</div>
@vite('resources/js/app.js')
</body>