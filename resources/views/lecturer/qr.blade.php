<!DOCTYPE html>
<html>
<head>
    <title>QR Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col items-center justify-center min-h-screen bg-gray-100">

    <h2 class="text-2xl font-semibold mb-6">Scan This QR Code</h2>

    <div class="bg-white p-6 rounded-xl shadow-md">
        {!! QrCode::size(250)->generate($url) !!}
    </div>

</body>
</html>