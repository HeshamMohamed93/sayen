<div id="error-messages" style="color:red"></div>
<form accept-charset="UTF-8" action="https://api.moyasar.com/v1/payments.html" method="POST" id="form_id">
   <input type="hidden" name="publishable_api_key" value="{{Config::get('moyasar.publishable_key')}}" />
   <input type="hidden" name="amount" value="{{$total_price}}" />
   <input type="hidden" name="callback_url" value="{{URL::to('/').'/api/v2/user/success-pay?order_id='.$order_id.'&device_type='.$device_type}}" />
   <input type="hidden" name="source[type]" value="creditcard" />
   <label>full name:</label>
   <input type="text" name="source[name]" value=""/>
   <br><label>credit card number:</label>
   <input type="text" name="source[number]" value=""/>
   <br><label>month:</label>
   <input type="text" name="source[month]" value=""/>
   <br><label>year:</label>
   <input type="text" name="source[year]" value=""/>
   <br><label>cvc:</label>
   <input type="text" name="source[cvc]" value=""/>
   <button type="button" id="submit_button_id">Purchase</button>
</form>

<script src="{{asset('public/admin/assets/vendors/base/vendors.bundle.js')}}" type="text/javascript"></script>

<script>
   $("#submit_button_id").click(function(event){
      event.preventDefault();
      
      $('#error-messages').empty();

      var form_data = $("#form_id").serialize();
      
      $.ajax({
         url: "https://api.moyasar.com/v1/payments",
         type: "POST",
         data: form_data,
         dataType: "json",
         error: function(e) {
            var errorSection = '';
            
            $.each(e.responseJSON.errors, function (key, value) {
               errorSection += key+' '+value[0];
               errorSection += '<br>';
            });
            
            $('#error-messages').append(errorSection);
         },
      })
      .done(function(data){
         var payment_id = data.id;
         var url = data.source.transaction_url;
         window.location.href=url;
      })
   });
</script>