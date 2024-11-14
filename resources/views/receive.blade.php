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
        if (window.Echo) {
            console.log(window.Echo);
         window.Echo.channel('delivery-tracking')
                .listen('.location-updated', (data) => {
                    console.log('Order status updated: ', data);
              });
            } else {
          console.error("Echo is not defined");
}

    


    </script>
</body>
</html>
