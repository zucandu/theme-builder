<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <title>Zucandu - Theme Builder App</title>
    <meta name="description" content="">
</head>
<body>
    <div id="themebuilder-platform"></div>
</body>
</html>
<script>
const zucConfig = @json($configs)
</script>
<script src="{{ mix('js/app.js') }}"></script>