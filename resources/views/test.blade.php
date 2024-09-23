<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel WebSocket Test</title>
</head>
<body>
    <h1>WebSocket Tracking</h1>

    @vite('resources/js/app.js')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            window.Echo.channel('user')
    .listen('TestingEvent', (data) => {
        console.log('Coordinates updated:', data); // تأكد من عرض كل البيانات
    });
        });
    </script>
</body>
</html>
