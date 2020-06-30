@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <img class="card-img-top img-thumbnail" :src="getUser.avatar" :alt="getUser.username" :title="getUser.username">
                    <div class="card-body">
                        <h4>个人简介</h4>
                        <div class="card-text">
                            @{{ getUser.introduction }}
                        </div>
                        <hr />
                        <h4>注册时间</h4>
                        <div class="card-text">
                            <small>@{{ getUser.created_at }}</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-text">
                            @{{ getUser.username }} @{{ getUser.email }}
                        </h4>
                    </div>
                </div>
                <hr>
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#topics">Ta的话题</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#replies">Ta的回复</a>
                            </li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane active container" id="topics">
                                <div class="user-list" v-for="topic of topics">
                                    <div class="list">
                                        <a :href="'/topics/' + topic.id">@{{ topic.title }}</a>
                                    </div>
                                </div>
                                <div class="card-text user-more font-weight-bold">
                                    <a class="card-link" href="{{ route('users.topics', $id) }}">#查看更多话题</a>
                                </div>
                            </div>
                            <div class="tab-pane container" id="replies">
                                <div class="user-list" v-for="reply of replies">
                                    <div class="list">
                                        <span class="">@{{ reply.content }}</span><small class="ml-3">@{{ reply.created_at }}</small>
                                    </div>
                                </div>
                                <div class="card-text user-more font-weight-bold">
                                    <a class="card-link" href="{{ route('users.replies', $id) }}">#查看更多回复</a>
                                </div>
                            </div>
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
                getUser: {},
                topics: [],
                replies: [],
            },
            created () {
                utils.getUser().then((res) => {
                    console.log(res);
                    this.user = res;
                });
                var auth = new Authorization('users/{user}');
                if (auth.handle()) {
                    axios.get('/{{ get_api_prefix("/") }}users/{{ $id }}').then((res) => {
                        var user = res.data.data;
                        console.log(user);
                        this.topics = user.topics;
                        this.replies = user.replies;
                        delete user.topics;
                        delete user.replies;
                        this.getUser = user;
                    }).catch((error) => {
                        console.log(error.response);
                        swal(error.response.data.msg, '', 'error');
                    })
                }
            }
        })
    </script>
@stop