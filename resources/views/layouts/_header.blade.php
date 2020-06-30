<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mr-auto">
                <!--  分类数据 -->
                @foreach($sharedCategories as $sharedCategory)
                    <li class="nav-item dropdown">
                        <a class="nav-link {{ isset($sharedCategory['children']) ? 'dropdown-toggle' : '' }}" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ $sharedCategory['name'] }}
                        </a>
                        @if(isset($sharedCategory['children']))
                            <div class="dropdown-menu">
                                @foreach($sharedCategory['children'] as $two)
                                    <a href="#" class="dropdown-item">{{ $two['name'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            <template v-if="user">
                <ul class="navbar-nav navbar-right">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img :src="user.avatar" :alt="user.username" :title="user.username" class="rounded-circle">
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" :href="'/users/' + user.id">个人中心</a>
                            <a class="dropdown-item" href="#">修改用户资料</a>
                            <a class="dropdown-item" :href="'/users/' + user.id + '/set?type=phone'">设置手机</a>
                            <a class="dropdown-item" :href="'/users/' + user.id + '/set?type=password'">设置密码</a>
                            <a class="dropdown-item" :href="'/users/' + user.id + '/set?type=email'">设置邮箱</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" style="cursor: pointer;" id="logout">退出登录</a>
                        </div>
                    </li>
                </ul>
            </template>
            <template v-else>
                <ul class="navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.login') }}">登录 / 注册</a>
                    </li>
                </ul>
            </template>
        </div>
    </div>
</nav>