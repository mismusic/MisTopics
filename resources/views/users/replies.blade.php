@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="col-md-8 offset-md-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">首页</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.show', $user->id) }}">用户资料</a></li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
        <div class="list-group">
            @foreach($replies as $reply)
                <div class="list-group-item">
                    {{ $reply->content }}<small class="ml-3">{{ $reply->created_at }}</small>
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            {!! $replies->render() !!}
        </div>
    </div>
@stop
@section('javascript')
    <script>
        var vm = new Vue({
            el: '#app',
            data: {
                user: null,
            },
            created: function () {
                utils.getUser().then((res) => {
                    console.log(res);
                    this.user = res;
                });
            },
        });
    </script>
@stop