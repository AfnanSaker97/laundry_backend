<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Location Tracker</title>
    @vite('resources/js/app.js')
</head>
<body>
<script src="{{ mix('js/app.js') }}"></script>

    <h1>Car Location Tracker</h1>

    <script>
      
      if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('delivery-tracking')
        .listen('.location-updated', (data) => {
            console.log(data.latitude, data.longitude);
        });
} else {
    console.log('Echo is not defined yet');
}

    </script>
</body>
</html>
