<!--start footer-->
<footer class="footer bg-main text-white" >
   <div class="container pt-20">
      <div class="row">
         <div class="col-md-6 col-6">
            <div class="container text-right">
               <div class="row">
                  {{--  <div class="col-md-6">
                     <h4 class="font-16">سياسة الخصوصية</h4>
                  </div>  --}}
                  {{--  <div class="col-md-6">
                     <h4 class="font-16">الشروط والاحكام</h4>
                  </div>  --}}
               </div>
            </div>
         </div>
         <div class="col-md-6 col-6">
            <h4 class="font-16">{{ trans('frontend.all_rights_saved') }} {{date('Y')}}</h4>
         </div>
      </div>
   </div>
</footer>
<!--end footer-->
<script
   src="https://code.jquery.com/jquery-3.5.1.min.js"
   integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
   crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
   integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
   crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
   integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
   crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/360fc66779.js" crossorigin="anonymous"></script>
<script src="{{asset('public/frontend/src/js/wow.min.js')}}"></script>
<script src="{{asset('public/frontend/src/js/main.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.26.9/sweetalert2.all.min.js"></script>

<script>
$('.form').submit(function(e){

   e.preventDefault();
   
   $('.message-error').empty();
   $('.name-error').empty();
   $('.email-error').empty();
   var url = $('form').attr("action");

   $.ajaxSetup({
       headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
   });

   $.ajax({
       type: "POST",
       url: url,
       dataType: 'json',
       enctype: 'multipart/form-data',
       mimeType: "multipart/form-data",
       contentType: false,
       cache: false,
       processData: false,
       data: new FormData(this),

       success: function (data) {
           swal({
               position: 'center',
               type: 'success',
               title: data.message,
               timer: 2000
           });
           
           window.location =  data.redirect;

       },error: function (error) {

           if(error.responseJSON.errors.name)
           {
              $('.name-error').text(error.responseJSON.errors.name);
           }
           if(error.responseJSON.errors.email)
           {
              $('.email-error').text(error.responseJSON.errors.email);
           }
           if(error.responseJSON.errors.message)
           {
              $('.message-error').text(error.responseJSON.errors.message);
           }
       }
   });
});
</script>
