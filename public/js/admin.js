$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $(document).delegate('.clickEventEmergencyOrder', 'click',function() {
        var visit_date =$(this).find('.fc-event-inner').data('date');
        window.location =  base_url+'admin-panel/emergency-orders?search='+visit_date;
    });
    $(document).delegate('.clickEventOrder', 'click',function() {
        var visit_date = $(this).find('.fc-event-inner').data('date');
        window.location =  base_url+'admin-panel/orders?search='+visit_date;
    });
    $('.datatables').DataTable({
        dom: 'Bfrtip',
        pageLength: 50,
        lengthMenu: [100, 200, 500],
        buttons: [
            'excel'
        ]
    });
    $('.maintenanance').DataTable({
        dom: 'Bfrtip',
        pageLength: 50,
        "order": [[ 2,'desc' ]],
        lengthMenu: [100, 200, 500],
        buttons: [
            'excel'
        ]
    });
    $(".datetimepicker").datetimepicker({
        timeFormat:  "hh:mm A",
        showMeridian: true,
        autoclose: true,
        todayBtn: true
    });
    $(".datetimepicker-report").datetimepicker({
        format: "yyyy-mm-dd",
        showMeridian: true,
        autoclose: true,
        minView: 'month',
        todayBtn: true
    });
    $('.service_id').select2({
        'placeholder' : "{{ trans('admin.select_services') }}"
    });
    $('.user_id').select2({
        'placeholder' : "{{ trans('admin.select_user') }}"
    });
    $('.user_id_notification').select2({
        'placeholder' : "{{ trans('admin.select_users') }}"
    });
    // $(".filter-btn").click(function(){
    //     $(".filter-div").toggle(1000);
    // });
});
//=== show services in admins
$('.type').change(function(e){
    if($(this).val() == 'service'){
        $('.serviceAdmin').show();
    }else if($(this).val() == 'admin'){
        $('.serviceAdmin').hide();
    }
});
//=== Submit Form ===
$('.form').submit(function(e){

    e.preventDefault();
    
    $('.error-div').css('display', 'none');
    $('.error-messages').empty();

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

            console.log(error.responseJSON);
            console.log(error.responseJSON.errors);

            var errorSection = '';
            $('.error-div').css('display', 'block');
            $.each(error.responseJSON.errors, function (key, value) {
                errorSection += value;
                errorSection += '<br>';
            });

            $('.error-messages').append(errorSection);
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
    });
});
//=== End Script ===

//=== Delete From Grid ===
$('.delete-btn').click(function(e){

    e.preventDefault();

    var url = $(this).data('delete-url');

    swal({
        title: 'تأكيد الحذف ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "DELETE",
                url: url,
                dataType: 'json',
                enctype: 'multipart/form-data',
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الحذف',
                        timer: 2000
                    });      

                }
            });
        }
    })
})
//=== End Script ===

$('.delete_one_image').click(function(e){

    e.preventDefault();

    var url = $(this).data('delete-url');
    var id = $(this).data('id');
    
    swal({
        title: 'تأكيد الحذف ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "DELETE",
                url: url,
                dataType: 'json',
                enctype: 'multipart/form-data',
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الحذف',
                        timer: 2000
                    });      

                }
            });
        }
    })
})
//=== End Script ===


//=== Cancel From Grid ===
$('.cancel-btn').click(function(e){

    e.preventDefault();

    var url = $(this).data('cancel-url');
    var problem = $('.problem option:selected').val();

    swal({
        title: 'تأكيد الغاء ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "POST",
                url: url+ '/' + problem,
                dataType: 'json',
                enctype: 'multipart/form-data',
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الالغاء',
                        timer: 2000
                    });      

                }
            });
        }
    })
})
//=== End Script ===

   
$('.printMaintenanance').click(function(){

    var thisButton = $(this);
    var url = $(this).data('printmaintenanance-url');
    var id =$(this).data('id');
    var workDetails = $('.work_details').val();
    var handWork = $('.hand_work').val();
    var materialsUsed = $('.materials_used').val();
    var handWorkPrice = $('.hand_work_price').val();
    var materialsUsedPrice = $('.materials_used_price').val();
    var type = $(this).data('type');

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        cache: false,
        data:{id:id,workDetails:workDetails,handWork:handWork,materialsUsed:materialsUsed,type:type,handWorkPrice:handWorkPrice,materialsUsedPrice:materialsUsedPrice},
        success: function (data) {

            //window.open(data['path'], '_blank');
            //thisButton.attr('href',data['path']);
        }, error: function (error) {

        }
    });
});
$('.printMaintenananceOnPage').click(function(){

    var url = $(this).data('printmaintenananceonpage-url');
    var id =$(this).data('id');
    var workDetails = $('.work_details').val();
    var handWork = $('.hand_work').val();
    var materialsUsed = $('.materials_used').val();
    var handWorkPrice = $('.hand_work_price').val();
    var materialsUsedPrice = $('.materials_used_price').val();
    var type = $(this).data('type');

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        cache: false,
        data:{id:id,workDetails:workDetails,handWork:handWork,materialsUsed:materialsUsed,type:type,handWorkPrice:handWorkPrice,materialsUsedPrice:materialsUsedPrice},
        success: function (data) {

            $('.printOnePage').html(data.data);
            var mode = 'iframe'; // popup
            var close = mode == "popup";
            var options = { mode : mode, popClose : close};
            $(".printOnePage").show();
            $(".printOnePage").printArea( options );
            $(".printOnePage").hide();

        }, error: function (error) {

        }
    });
});

$('.printOrderUp').click(function(){

    var thisButton = $(this);
    var url = $(this).data('printorderup-url');
    var id = $(this).data('id');
    var workDetails = $('.work_details').val();
    var handWork = $('.hand_work').val();
    var materialsUsed = $('.materials_used').val();
    var handWorkPrice = $('.hand_work_price').val();
    var materialsUsedPrice = $('.materials_used_price').val();
    var flat = $('.flat').val();
    var building = $('.building').val();
    var user_id = $('.user_id').val();
    var service_id = $('.service_id').val();
    var team_id = $('.team_id').val();

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        cache: false,
        data:{flat:flat,building:building,team_id:team_id,id:id,user_id:user_id,service_id:service_id,workDetails:workDetails,handWork:handWork,materialsUsed:materialsUsed,handWorkPrice:handWorkPrice,materialsUsedPrice:materialsUsedPrice},
        success: function (data) {

            window.open(data['path'], '_blank');
            thisButton.attr('href',data['path']);
        }, error: function (error) {

        }
    });
});
$('.printOnePageOrderUp').click(function(){

    var url = $(this).data('printorderup-url');
    var id = $(this).data('id');
    var workDetails = $('.work_details').val();
    var handWork = $('.hand_work').val();
    var materialsUsed = $('.materials_used').val();
    var handWorkPrice = $('.hand_work_price').val();
    var materialsUsedPrice = $('.materials_used_price').val();
    var flat = $('.flat').val();
    var building = $('.building').val();
    var user_id = $('.user_id').val();
    var service_id = $('.service_id').val();
    var team_id = $('.team_id').val();

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        cache: false,
        data:{flat:flat,building:building,team_id:team_id,id:id,user_id:user_id,service_id:service_id,workDetails:workDetails,handWork:handWork,materialsUsed:materialsUsed,handWorkPrice:handWorkPrice,materialsUsedPrice:materialsUsedPrice},
        success: function (data) {

            $('.printOnePage').html(data.data);
            var mode = 'iframe'; // popup
            var close = mode == "popup";
            var options = { mode : mode, popClose : close};
            $(".printOnePage").show();
            $(".printOnePage").printArea( options );
            $(".printOnePage").hide();

        }, error: function (error) {

        }
    });
});
//=== Stop Services ===
$('.stopServices').click(function(e){

    e.preventDefault();

    var url = $(this).data('stopservices-url');

    swal({
        title: 'تأكيد ايقاف التطبيق ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الايقاف'
      }).then((result) => {
        if (result.value) 
        {
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
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الايقاف',
                        timer: 2000
                    });      

                }
            });
        }
    })
})
//=== End Script ===

$('.radio-check').on('change',function(){
    $('#submit_btn').attr('disabled',false);
    $('.radio-check').parent().parent().removeClass('selected-form');
    $('input[type="radio"]:checked').parent().parent().addClass('selected-form');
});
$('.device_number').on('change',function(){
    var device = $(this).val();
    if(device == 1){
        $('.numbersDiv').show();
    }else if(device == 0){
        $('.numbersDiv').hide();
    }
});
//=== Stop Services ===
$('.saveEditMaintenananceReport').click(function(e){

    e.preventDefault();

    var url = $(this).data('saveeditmaintenanancereport-url');
    var id =$(this).data('id');

    var form = $('#form-report')[0];
    var data = new FormData(form);
    var type = $(this).data('type');
    data.append("type", type);
    data.append("id", id);
    swal({
        title: 'تأكيد الحفظ  ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                enctype: 'multipart/form-data',
                mimeType: "multipart/form-data",
                cache: false,
                processData: false,
                contentType: false,
                data:data,
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الحفظ',
                        timer: 2000
                    });      

                }
            });
        }
    })
});
//=== End Script ===

$('.saveEditOrderUp').click(function(e){

    e.preventDefault();

    var url = $(this).data('saveeditorderup-url');
    var id =$(this).data('id');

    var form = $('#form-report')[0];
    var data = new FormData(form);
    var type = $(this).data('type');
    data.append("type", type);
    data.append("id", id);
    swal({
        title: 'تأكيد الحفظ  ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                enctype: 'multipart/form-data',
                mimeType: "multipart/form-data",
                cache: false,
                processData: false,
                contentType: false,
                data:data,
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الحفظ',
                        timer: 2000
                    });      

                }
            });
        }
    })
});
//=== End Script ===




//=== Stop Services ===
$('.removeTestOrder').click(function(e){

    e.preventDefault();

    var url = $(this).data('removetestorder-url');

    swal({
        title: 'تأكيد حذف الطلبات ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الحذف'
      }).then((result) => {
        if (result.value) 
        {
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
                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الحذف',
                        timer: 2000
                    });      

                }
            });
        }
    })
});
//=== End Script ===

//=== Preview Uploaded Image ===
$('.image').change(function(){
    if (this.files && this.files[0])
    {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.preview').attr('src', e.target.result);
            $('.current_image').val('');
        }
        reader.readAsDataURL(this.files[0]);
    }
})
//=== End Script ===

// === Google map script ===
function initMap() 
{
    var map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: lat, lng: lng },
        zoom: 12,
        disableDefaultUI: true
    });
    
    var marker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: map,
        draggable: false,
    });

    var map = new google.maps.Map(document.getElementById("map-team"), {
        center: { lat: team_lat, lng: team_lng },
        zoom: 12,
        disableDefaultUI: true
    });
    
    var marker = new google.maps.Marker({
        position: { lat: team_lat, lng: team_lng },
        map: map,
        draggable: false,
    });
}
//=== End Script ===

$('.filter-status').select2();
// === Filter order grid ===
$('.createFilter').on('click', function (){
    var search_parameter = '?';
    $('.filter-status').each(function(i, obj) {
        if(search_parameter == '?'){
            search_parameter += $(obj).data('search_parameter')+'='+$(obj).val();
        }else if(search_parameter != '?'+$(obj).data('search_parameter')+'='+$(obj).val()){
            search_parameter += '&'+ $(obj).data('search_parameter')+'='+$(obj).val();
        }
    });
    if($('input[name="search"]').length > 0)
    {
        search_parameter += '&search='+$('input[name="search"]').val();
    }
    var url = $(this).data('current-url') + search_parameter; 
    window.location = url;
});
//=== End Script ===

$('.addNewReason').click(function(){
    var newDiv = $('.addNew');
    newDiv.append($('.clonDIv').find('.reason_div').clone());
    removeReason();
});
removeReason();
function removeReason(){
    $('.removeReason').click(function(){
        $(this).closest('.reason_div').remove();
    });
}
// === Get service teams ===
$('.change-order-service').on('change', function(){

    $.ajax({
        type: "GET",
        url: service_teams_url+'?service_id='+$(this).val(),
        dataType: 'json',
        enctype: 'multipart/form-data',
        mimeType: "multipart/form-data",
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {

            $('.service-teams').empty();
            
            $('.service-teams').append("<option disabled selected>لم يتم تحديد فني</option>");

            $.each(data.teams, function() {
                $('.service-teams').append($("<option />").val(this.id).text(this.name));
            });

        },error: function (error) {
            $('.service-teams').empty();
            $('.service-teams').append("<option disabled selected>لم يتم تحديد فني</option>");
        }
    });
});
//=== End Script ===

// === Get sub services ===
$('.changeService').on('change', function(){
    var id = $(this).val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: serviceChange,
        data:{id:id},
        success: function (data) {
            if(data){
                $('.subServiceDiv').empty();
                $('.subServiceDiv').append(data);
            }else{
                $('.subServiceDiv').empty();
            }
        }
    });
});
//=== End Script ===

// === Send Offer Notifications ===
$('.offerSendNotification').on('click', function(){
    var id = $(this).data('id');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        method: "POST",
        url: offerSendNotification,
        data:{id:id},
        success: function (data) {
            
        }
    });
});
//=== End Script ===

// ==== Change order status action ===
$(document).ready(function(){

    // 1 = new, 2 = current, 3 = done, 4 = cancel, 5 = assign to team via admin
    $('.order-status').load('change', function () {
        
        var status = $(this).children("option:selected").val();
        $('.order-status-value').val(status);
        
        if(status == 1 || status == 2 || status == 3 || status == 4)  //=== new || current || done || cancel
        {
            if(status == 1)
            {
                $('.visit-date').css('pointer-events', '');
            }
            else
            {
                $('.visit-date').css('pointer-events', 'none');
            }
            if(status == 2){
                $('.service-teams').css('pointer-events', '');
                $('.change-order-service').css('pointer-events', 'none');
            }else{
                $('.service-teams').css('pointer-events', 'none');
            }
            
        }

        else if(status == 5)  //=== assign
        {
            $('.visit-date').css('pointer-events', '');
            $('.service-teams').css('pointer-events', '');
            $('.change-order-service').css('pointer-events', '');
        }
    });
    
    $('.order-status').on('change', function () {

        var status = $(this).children("option:selected").val();
        $('.order-status-value').val(status);
        $('.problemDiv').hide();

        if(status == 1 || status == 2 || status == 3 || status == 4)  //=== new || current || done || cancel
        {
            if(status == 1)
            {
                $('.visit-date').css('pointer-events', '');
            }
            else
            {
                $('.visit-date').css('pointer-events', 'none');
            }
            if(status == 4){
                $('.problemDiv').show();
            }

            if(status == 2){
                $('.service-teams').css('pointer-events', '');
                $('.change-order-service').css('pointer-events', 'none');
            }else{
                $('.service-teams').css('pointer-events', 'none');
            }
        }

        else if(status == 5)  //=== assign
        {
            $('.visit-date').css('pointer-events', '');
            $('.service-teams').css('pointer-events', '');
            $('.change-order-service').css('pointer-events', '');
        }
    });
    
    $('.change-excellence-client').on('change', function () {

        var type = $(this).children("option:selected").val();

        if(type == 1)
        {
            $('.building').show();
        }
        else
        {
            $('.building').hide();
        } 
    });

    $('.change-excellence-client').load('change', function () {

        var type = $(this).children("option:selected").val();

        if(type == 1)
        {
            $('.building').show();
        }
        else
        {
            $('.building').hide();
        } 
    });

    $('.type').on('change', function () {

        var type = $(this).children("option:selected").val();
        $('.month').hide();

        if(type == 1)
        {
            $('.date-from-to').show();
            $('.date_to').removeAttr('disabled');
        }
        else if(type == 2 || type == 3)
        {
            $('.date-from-to').show();
            $('.date_to').attr('disabled', true);
        }
        else if(type == 4)
        {
            $('.date-from-to').hide();
            $('.month').show();
        } 
    });

    $('.type').load('change', function () {

        var type = $(this).children("option:selected").val();
        $('.month').hide();

        if(type == 1)
        {
            $('.date-from-to').show();
            $('.date_to').removeAttr('disabled');
        }
        else if(type == 2 || type == 3)
        {
            $('.date-from-to').show();
            $('.date_to').attr('disabled', true);
        }
        else if(type == 4)
        {
            $('.date-from-to').hide();
            $('.month').show();
        }
    });

    $('.maintence-report-type').on('change', function () {

        var type = $(this).children("option:selected").val();

        if(type == 1)
        {
            $('.building').hide();
            $('.user').show();
        }
        else if(type == 2)
        {
            $('.user').hide();
            $('.building').show();
        }
    });

    $('.maintence-report-type').load('change', function () {

        var type = $(this).children("option:selected").val();

        if(type == 1)
        {
            $('.building').hide();
            $('.user').show();
        }
        else if(type == 2)
        {
            $('.user').hide();
            $('.building').show();
        }
    });
});
//=== End Script ===

// === Active / Deactivate user / team ====
$('.change-status').click(function(){

    var message = '';
    var data = {};
    var url = $(this).data('change-status-url');

    if($(this).data('status') == 1)
    {
        message = 'تأكيد الغاء التنشيط ؟';
    }
    else if($(this).data('status') == 0)
    {
        message = 'تأكيد اعادة التنشيط ؟';
    }

    swal({
        title: message,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            });
        
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                contentType: false,

                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: data.message,
                        timer: 2000
                    });

                    window.location =  data.redirect;

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم التحديث',
                        timer: 2000
                    });
                }
            });
        }
    })
})
//=== End Script ===

// === Generate copoun ===
function generateCode()
{
    var code = '';
    while(code.length < 10 && code.length != 10)
    {
        code += Math.floor(Math.random() * 100) + 1;
    }
    $('.code').val(code);
}
//=== End Script ===

// === Seen notification status ===
function SeenNotification(){
    $('.order-notification').on('click', function(e){
        e.preventDefault();

        var redirect_url = $(this).attr('href');
        var notification_id = $(this).data('notification-id');

        $.ajax({
            type: "GET",
            url: base_url+'admin-panel/notification-seen/'+notification_id,
            dataType: 'json',
            contentType: false,
            success: function (data) {

                window.location =  redirect_url;

            }, error: function (error) {

            }
        });
    });
}
SeenNotification();
// === End function ===

// === Seen notification status ===
$('.send-invoice').on('click', function(e){
    e.preventDefault();

    var order_id = $(this).data('order-id');

    swal({
        title: 'ارسال الفاتورة للعميل ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajax({
                type: "GET",
                url: base_url+'admin-panel/send-invoice/'+order_id,
                dataType: 'json',
                contentType: false,

                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: 'تم ارسال الفاتورة',
                        timer: 2000
                    });

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: 'لم يتم الارسال',
                        timer: 2000
                    });
                }
            });
        }
    })
});
// === End function ===

//=== Export report ===
$('.export-report').click(function(e){
    var url = $('.export-report').data("action")+'?';
    $("form :input").each(function(index, elm){
        if(typeof elm.name !== 'undefined')
        {
            url = url + elm.name+'='+elm.value+"&";
        }
      });
      window.open(url,'_blank');
      window.close();
});
//=== End Script ==

// === Refund ===
$('.refund').on('click', function(e){
    e.preventDefault();

    var order_id = $(this).data('order-id');

    swal({
        title: 'استعادة المبلغ للعميل ؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'الغاء'
      }).then((result) => {
        if (result.value) 
        {
            $.ajax({
                type: "GET",
                url: base_url+'admin-panel/refund/'+order_id,
                dataType: 'json',
                contentType: false,

                success: function (data) {

                    swal({
                        position: 'center',
                        type: 'success',
                        title: 'تم استعادة المبلغ',
                        timer: 2000
                    });

                }, error: function (error) {

                    swal({
                        position: 'center',
                        type: 'warning',
                        title: error.responseJSON.errors,
                        timer: 2000
                    });
                }
            });
        }
    })
});
readAllNotification();
function readAllNotification(){
    $('.readAllNotification').on('click', function(e){
        $.ajaxSetup({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
        });
        $.ajax({
            type: "post",
            url: base_url+'admin-panel/notification-readAll',
            dataType: 'json',
            contentType: false,
            success: function (data) {

                autoRefresh_div();
                SeenNotification();
                
            }, error: function (error) {

            }
        });
    });
}

    
function autoRefresh_div(){
    $.ajax({
        type: "get",
        url: base_url+'admin-panel/notification-reload',
        dataType: 'json',
        contentType: false,
        success: function (data) {

            $("#reloadNotification").html(data);
            SeenNotification();
            readAllNotification();
        }, error: function (error) {

        }
    });
}
autoRefresh_div(); // on load
setInterval(autoRefresh_div, 5000); // every 5 seconds

// === End function ===