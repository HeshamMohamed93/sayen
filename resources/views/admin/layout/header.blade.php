<head>
    <meta charset="utf-8" />
    
    <title>{{trans('admin.app_name')}}</title>
    <meta name="description" content="Latest updates and statistic charts"> 
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="refresh" content="900" >
    <!--begin::Web font -->
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
    <script>
      WebFont.load({
        google: {"families":["Montserrat:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
            sessionStorage.fonts = true;
        }
      });
    </script>
    <!--end::Web font -->

    <!--begin::Base Styles -->  
             
    <!--begin::Page Vendors --> 

            <!--end::Page Vendors -->
            <link href="{{asset('public/admin/assets/vendors/base/vendors.bundle.css')}}" rel="stylesheet" type="text/css" />
            <link href="{{asset('public/admin/assets/vendors/base/vendors.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
            <link href="{{asset('public/admin/assets/demo/demo3/base/style.bundle.css')}}" rel="stylesheet" type="text/css" />
            <link href="{{asset('public/admin/assets/demo/demo3/base/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
            <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.26.9/sweetalert2.min.css">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" integrity="sha512-ZKX+BvQihRJPA8CROKBhDNvoc2aDMOdAlcm7TUQY+35XYtrd3yh95QOOhsPDQY9QnKE0Wqag9y38OIgEvb88cA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            @if(\Request::route()->getName() == 'home')
              <link href="{{asset('public/admin/assets/plugins/calendar/calendar.css')}}" rel="stylesheet" type="text/css" />
            @endif
            <link href="{{asset('public/css/custom.css')}}" rel="stylesheet" type="text/css" />
            <!--end::Base Styles -->
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
            <link rel="shortcut icon" href="{{asset('public/img/logo.jpg')}}" /> 
            @yield('style')
            <style>
              .Y2023-02-05{
                top: 197px !important;
              }
              .YO2023-02-05{
                top: 226px !important;
              }
            </style>

</head>
