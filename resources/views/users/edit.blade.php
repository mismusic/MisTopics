@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <h5 class="card-header">{{ $title }}</h5>
            <div class="card-body">
                <div class="form-group">
                    <label for="phone">手机号</label>
                    <input type="text" id="phone" class="form-control" value="{{ $user->phone }}">
                </div>
                <div class="form-group">
                    <lable for="verification_code">验证码</lable>
                    <div class="row">
                        <div class="col-md-3 col-4">
                            <input type="text" id="verification_code" class="form-control">
                        </div>
                        <div class="col-md-9 col-8">
                            <button class="btn btn-primary">发送</button>
                        </div>
                    </div>
                </div>
            </div>
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
            created () {
                utils.getUser().then((res) => {
                    this.user = res;
                })
            }
        })
    </script>
@stop