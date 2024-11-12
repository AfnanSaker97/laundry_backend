<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Location Tracker</title>
    @vite('resources/js/app.js')
</head>
<body>
    <h1>Car Location Tracker</h1>

    <script>
     document.addEventListener("DOMContentLoaded", function () {
            window.Echo.channel('delivery-tracking')
                .listen('.location-updated', (data) => {
                    console.log("New location received:", data.latitude, data.longitude);
                });
        });
    </script>
</body>
</html>
