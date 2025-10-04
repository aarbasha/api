<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('../../resources/css/app.css')

    <link rel="stylesheet" href="{{ asset('../../css/app.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <title>
        @yield('title')
    </title>
</head>

<body class=" bg-gray-700 text-white">

    <header class="bg-red-500 w-full h-[60px]">
        tha header
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-green-500 w-full h-[60px]">
        tha footer
    </footer>
</body>

</html>
