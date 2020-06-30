<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $data['subject'] }}</title>
</head>
<body>
    <h1>{{ $data['subject'] }}</h1>
    <p style="color: green">{{ $data['username'] }}你好，请点击链接进行邮箱号的激活：</p>
    <p><a href="{{ $data['uri'] }}">{{ $data['uri'] }}</a></p>
    <p><b>提示：验证邮箱号的有效时间为{{ $data['expires_in'] }}分钟，请及时进行验证！</b></p>
    <p>如果不是本人操作，请忽略该邮件</p>
</body>
</html>