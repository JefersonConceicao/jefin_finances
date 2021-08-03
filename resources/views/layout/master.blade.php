<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> Jefin Finances </title>
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
        rel="stylesheet" 
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" 
        crossorigin="anonymous"
    >

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/app.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/bootstrap.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/pages/auth.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/pages/chat.css') }} "/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/pages/dripicons.css') }} "/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/pages/email.css') }} "/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/pages/error.css') }} "/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/vendors/iconly/bold.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/vendors/perfect-scrollbar/perfect-scrollbar.css') }}"  />
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/vendors/bootstrap-icons/bootstrap-icons.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/vendors/fontawesome/all.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/vendors/chartjs/Chart.min.css')  }}"/>
    <link rel="stylesheet" href="{{ asset('css/mazer-theme/app.css') }} "/> 
    <link rel="stylesheet" href="{{ asset('vendor/jquery-datetimepicker/build/jquery.datetimepicker.min.css')}}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }} "/>   
</head>
<body style="background-color:#e7e7e7">
    @yield('content')
    <script 
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
        crossorigin="anonymous"
    ></script>
    <script src="{{ asset('css/mazer-theme/vendors/fontawesome/all.min.js') }}"> </script>
    <script src="{{ asset('css/mazer-theme/vendors/jquery/jquery.min.js') }}"> </script>
    <script src="{{ asset('js/mazer-theme/bootstrap.bundle.min.js') }}"> </script>
    <script src="{{ asset('js/mazer-theme/main.js') }}"> </script>
    <script src="{{ asset('vendor/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js')}}"> </script>
    <script src="{{ asset('vendor/inputmask/dist/jquery.inputmask.js') }}"> </script>
    <script src="{{ asset('vendor/jquery-mask/dist/jquery.mask.min.js')}}"> </script>
    <script src="{{ asset('css/mazer-theme/vendors/chartjs/Chart.bundle.min.js') }}"> </script>
    <script src="{{ asset('js/app.js') }}"> </script>
</body>
</html>