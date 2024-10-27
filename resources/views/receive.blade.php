<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WebSocket Example</title>
</head>
<body>
    <h1>Location Sender</h1>
    <div id="location"></div>

    <script>
        // إنشاء اتصال WebSocket
        const socket = new WebSocket('ws://127.0.0.1:8080/app/bdyibm6siappm5afxfgc?protocol=7&client=js&version=4.3.1&flash=false');

        // عند فتح الاتصال
        socket.addEventListener('open', function () {
            console.log('WebSocket is connected.');

            // دالة لإرسال الإحداثيات
            function sendLocation(carId, latitude, longitude) {
                const data = {
                    car_id: carId,
                    latitude: latitude,
                    longitude: longitude,
                };
                // إرسال البيانات عبر WebSocket
                socket.send(JSON.stringify(data));
                console.log('Coordinates sent:', data);
            }

            // محاكاة الحصول على إحداثيات من جهاز GPS
            setInterval(() => {
                const carId = '1'; // معرّف السيارة
                const latitude = (Math.random() * 180 - 90).toFixed(6); // محاكاة خط العرض
                const longitude = (Math.random() * 360 - 180).toFixed(6); // محاكاة خط الطول

                sendLocation(carId, latitude, longitude); // إرسال الإحداثيات
            }, 5000); // إرسال كل 5 ثواني
        });

        // معالجة الرسائل الواردة
     //   socket.addEventListener('message', function (event) {
      //      const message = JSON.parse(event.data);
        //    console.log('Message from server:', message);
         //   document.getElementById('location').innerHTML += `Car ID: ${message.car_id}, Latitude: ${message.latitude}, Longitude: ${message.longitude}<br>`;
        //});
    </script>
</body>
</html>
