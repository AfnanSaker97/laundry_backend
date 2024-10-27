<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel WebSocket Test</title>
    @vite('resources/js/app.js')
</head>
<body>
    <h1>WebSocket Tracking</h1>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            window.Echo.channel('user')
                .listen('TestingEvent', (data) => {
                    console.log('Coordinates updated:', data);
                });
        });
    </script>
</body>
</html>
