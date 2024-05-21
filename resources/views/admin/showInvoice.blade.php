<!DOCTYPE html>
<html lang="en" dir="rtl">
  <head>
    <meta charset="utf-8">
    <title>Order Details</title>
    <style>
        .clearfix:after {
        content: "";
        display: table;
        clear: both;
        }

        a {
        color: #5D6975;
        text-decoration: underline;
        }

        body {
        position: relative;
        width: 21cm;  
        height: 19.7cm; 
        margin: 0 auto; 
        color: #001028;
        background: #FFFFFF; 
        font-family: Arial, sans-serif; 
        font-size: 16px; 
        font-family: Arial;
        }

        header {
        padding: 10px 0;
        margin-bottom: 30px;
        }

        #logo {
        text-align: center;
        margin-bottom: 10px;
        }

        #logo img {
        width: 90px;
        }

        h1 {
        border-top: 1px solid  #5D6975;
        border-bottom: 1px solid  #5D6975;
        color: #5D6975;
        font-size: 2em;
        line-height: 1.4em;
        font-weight: normal;
        text-align: center;
        margin: 0 0 20px 0;
        background: url(dimension.png);
        }

        #project {
        float: right;
        }

        #project span {
        color: #5D6975;
        text-align: right;
        width: 100px;
        margin-right: 10px;
        display: inline-block;
        font-size: 1.2em;
        }

        #company {
        float: left;
        text-align: right;
        }

        #project div,
        #company div {
        white-space: nowrap;     
        padding:5px 0;   
        border-bottom: 1px solid;
        }

        table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        margin-bottom: 20px;
        font-size: 20px;
        }

        table tr:nth-child(2n-1) td {
        background: #F5F5F5;
        }

        table th,
        table td {
        text-align: center;
        }

        table th {
        padding: 5px 20px;
        color: #5D6975;
        border-bottom: 1px solid #2c2c2c;
        border-top: 1px solid #2c2c2c;
        white-space: nowrap;        
        font-weight: normal;
        }

        table .service,
        table .desc {
        text-align: right;
        }

        table td {
        padding: 10px;
        text-align: right;
        }

        table td.service,
        table td.desc {
        vertical-align: top;
        }

        table td.unit,
        table td.qty,
        table td.total {
        /*font-size: 1.2em;*/
        }

        table td.grand {
        border-top: 1px solid #5D6975;;
        }

        #notices .notice {
        color: #5D6975;
        font-size: 1.2em;
        }

        footer {
        color: #5D6975;
        width: 100%;
        height: 30px;
        position: absolute;
        bottom: 0;
        border-top: 1px solid #C1CED9;
        padding: 8px 0;
        text-align: center;
        }
    </style>
  </head>
  <body>
    <header class="clearfix">
      <div id="logo">
        <img src="{{asset('public/img/logo.png')}}">
      </div>
      <h1>{{ trans('admin.order_number') }} {{ $order->order_number }}</h1>
      <div id="company" class="clearfix">
        <div>Alam Altamyouz Co.</div>
        <div>المملكة العربية السعودية</div>
        <div>0090509997657</div>
        <div><a href="mailto:company@example.com">info@sayen.co</a></div>
      </div>
      <div id="project">
        <div><span>{{ trans('admin.client_type') }}</span> 
            @if($order->orderUser->excellence_client == 1)
                {{ trans('admin.excellence_client') }}
            @else
                {{ trans('admin.client') }}
            @endif
        </div>
        <div><span>{{ trans('admin.client') }}</span> {{ $order->orderUser->name}}</div>
        <div><span>{{ trans('admin.phone') }}</span>  {{ $order->orderUser->phone}}</div>
        @if($order->team_start_at)
            <div><span>{{ trans('admin.date') }}</span>  {{date('Y-m-d',strtotime($order->team_start_at))}} </div>
        @endif
        <div><span>{{ trans('admin.teams') }}</span>{{ $order->orderTeam->name }} </div>
		 <div><span>{{ trans('admin.building') }}</span>  @if($order->orderUser->building) {{$order->orderUser->building->name}} @endif</div>
		 <div><span>{{ trans('admin.floor') }}</span> {{ $order->floor }}</div>
		 <div><span>{{ trans('admin.flat') }}</span>  {{ $order->orderUser->flat }}</div>
		 <div><span>{{ trans('admin.pay_method') }}</span>  {{ $order->orderPayMethod() }}</div>
		 
      </div>
    </header>
    <main>
      <table>
        <thead>
          <tr>
            <th class="service">{{ trans('admin.service') }}</th>
            <th class="desc">{{ trans('admin.description') }} </th>
            <th>{{ trans('admin.price') }} </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="service">{{ $order->orderService->name }}</td>
            <td class="desc">{{ $order->notes}}</td>
            <td class="unit"></td>
          </tr>
            <tr>
              <td colspan="2" class="grand total">{{ trans('admin.team_added_price') }}</td>
              <td class="grand total">@if($order->orderInvoice->team_added_price) {{ array_sum($order->orderInvoice->teamAddedPrice()) }} @else 0 @endif {{trans('admin.currency')}}</td>
            </tr>
          <tr>
            <td colspan="2" class="grand total">{{ trans('admin.hand_work') }}</td>
            <td class="grand total">@if($order->hand_work) {{ $order->hand_work }} @else 0 @endif {{trans('admin.currency')}}</td>
          </tr>

          @if($order->orderInvoice->coupon_discount)
            <tr>
              <td colspan="2">{{ trans('admin.coupon_discount') }}</td>
              <td class="total">{{ $order->orderInvoice->coupon_discount }} {{trans('admin.currency')}}</td>
            </tr>
          @endif
          <!-- <tr>
            <td colspan="2">TAX 25%</td>
            <td class="total">$1,300.00</td>
          </tr> -->
          
          <tr>
            <td colspan="2" class="grand total">{{ trans('admin.final_price_before_tax') }}</td>
            <td class="grand total">{{ $totalBefore }} {{ trans('admin.currency') }}</td>
          </tr>

          <tr>
            <td colspan="2" class="grand total">{{ trans('admin.tax') }} ( {{ $setting->value_added }}%)</td>
            <td class="grand total">{{ $tax }} {{trans('admin.currency')}}</td>
          </tr>

          <tr>
            <td colspan="2" class="grand total">{{ trans('admin.final_price_after_tax') }}</td>
            <td class="grand total">{{ $totalAfter }} {{trans('admin.currency')}}</td>
          </tr>

        </tbody>
      </table>
      <!-- <div id="notices">
        <div>NOTICE:</div>
        <div class="notice">A finance charge of 1.5% will be made on unpaid balances after 30 days.</div>
      </div> -->
    </main>
    <!-- <footer>
    </footer> -->
  </body>
</html>
