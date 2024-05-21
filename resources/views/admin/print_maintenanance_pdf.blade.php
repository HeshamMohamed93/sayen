<!DOCTYPE html>
<html lang="ar">
    <head>
        <meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
        <style>
            *{
            	box-sizing: border-box;
            }
			body{
				position: relative;
                font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif, 'Cairo';
				height: 100vh;
			}
            /* @font-face {
              font-family: Cairo;
              src: url('{{ URL::to('resources/assets/back/fonts/Cairo-Regular.ttf')}}') format('ttf'),
            	 		 url('{{ URL::to('resources/assets/back/fonts/Cairo-Regular.woff2')}}') format('woff2'),
            			 url('{{ URL::to('resources/assets/back/fonts/Cairo-Regular.woff')}}') format('woff');
            } */
            @media print {
                .pagebreak { page-break-before: always; } /* page-break-after works, as well */
            }
            .full {
            	width: 100%;
            	position: relative;
            	margin:auto;
            	color:#000 !important;
            }
            .info_ar {
            	height:60px;
            	width: 100%;            	
            	padding:0;            	
            	font-size:14px;
            	display: flex;
            	border-bottom: 2px solid;
            }
            .info_ar h2 {
            	margin:0;
            	padding:0 0 10px 0;
            	font-size:28px;
            	font-weight: bold;
            	text-align: center;
            	font-family: 'Cairo';
            }
            .info_ar h3 {
            	margin:0;
            	padding:0;
            	font-size:18px;
            	text-align: center;
            	font-family: 'Cairo';
            }
            .patient {
            	width: 100%;
            	padding: 5px;
            	font-size: 18px;
            	margin: 30px 0;
            	display: inline-block;
            }
            .medicine {
            	width: 100%;
            	padding: 5px;
            	font-size: 14px;
            }
            .medicine table tr td{
            	direction: RTL;
            	border:2px solid #ccc !important;
            }
            tbody {
                border:1px solid #ccc !important;
            }
            .patient tbody td{
                padding: 5px !important;
            }
            .analysis {
            	width: 100%;
            	padding:5px;
            	font-size: 16px;
            }
            .rays {
            	width: 100%;
            	padding:5px;
            	font-size: 16px;
            }
            .note {
            	width: 100%;
            	padding:5px;
            	font-size: 12px;
            }
			.col-6{
				width: 50%;
			}
			.doc-name{
				text-align: center;
				font-size: 30px;
				font-weight: 600;
			}
			.specialty{
				font-size: 23px;
				text-align: center;
				
			}
			th, td{
				font-size: 17px !important;
				padding: 5px;
				text-align: right;
			}
			td img{
				max-width: 100%;
				max-height: 50px;
			}
			.body img{
				max-width: 75px;
				margin: 0px 60px;
			}
			.pagebreak{
				background-color: dodgerblue;
				color: #fff;
				margin: 0;
				padding: 2px;
				text-align: center;
				font-size: 21px;
				position: absolute;
				right: 0;
				left: 0;
				bottom: 0;
			}
			.A5{
				height: 550px;
			}
			.logo{
                max-width: 750px;
                max-height: 80px;
            }
			.Signature2{
				width: 100%;
				margin: 0 auto;
				margin-bottom: 30px;
			}
			.col-3{
				width: 24%;
				display: inline-block;
				font-size: 24px;
			}
			.header{
				direction: rtl;
			}
			.box{
				padding: 14px;
				border: 1px solid #000;
				margin-bottom: 35px;
				padding-top: 0;
				text-align: right;
				direction: rtl;
				padding-bottom: 20px;
				padding-left: 69px;
			}
			.box .head-title h3{
				margin-bottom: 0;
			}
			.box .head-title {
				position: relative;
			}
			.left{
				position: absolute;
				left: 15px;
			}
			.box p{
    			margin-top: 5px;
				position: relative;
				width: 100%;
			}
			.box span{
				font-size: 17px;
				font-weight: 600;
			}
			.line{
				display: flex;
			}
			.space{
				width: 49%;
				display: inline-block
			}
			.halfspace{
				width: 49%;
				display: inline-block;
				float: left
			}
            </style>
    </head>
    <body>
        <div class="full A5">
    		<div class="header">
				<div class="col-12" style="width=100%">
					@if($image)
						<img src="public/uploads/{{ $image }}" class="logo">
					@else
						<img src="{{ url('public/img/alam.jpg') }}" class="logo">
					@endif
				</div> 
    			<div class="info_ar">
    				<div class="col-6">
						<div class="doc-name">{{ trans('admin.service_report') }}</div>
					</div>
    			</div>
    		</div>
    		<div class="patient">
    			<table style="width:100%; text-align: left; border-collapse: collapse; margin:0 auto; border:1px solid #000;">
					<tr style=" border:1px solid #000;">
						<th colspan="2" style="text-align: center; border:1px solid #000;">{{ $data->order_number }}</th>
    					<td colspan="2" style="text-align: center; border:1px solid #000;"> {{trans('admin.order_number')}}</td>
    				</tr>
					<tr style=" border:1px solid #000;">
						<th style=" border:1px solid #000;">{{$data->orderService->name}}</th>
    					<td style=" border:1px solid #000;">{{trans('admin.type_service')}}</td>
    					<th style=" border:1px solid #000;">@if($data->team_start_at) {{date('H:i:s',strtotime($data->team_start_at))}} @endif </th>
    					<td style=" border:1px solid #000;">{{ trans('admin.start_work') }}</td>
    				</tr>
    				<tr style=" border:1px solid #000;">
    					<th style=" border:1px solid #000;"></th>
    					<td style=" border:1px solid #000;"></td>
						<th style=" border:1px solid #000;">@if($data->team_start_at) {{date('Y-m-d',strtotime($data->team_start_at))}} @endif</th>
    					<td style=" border:1px solid #000;">{{ trans('admin.date') }}</td>
    				</tr>
    				<tr style=" border:1px solid #000;">
                        <th style=" border:1px solid #000;"></th>
    					<td style=" border:1px solid #000;"></td>
    					<th style=" border:1px solid #000;">@if($data->orderUser && $data->orderUser->building) {{$data->orderUser->building->name}} @endif</th>
    					<td style=" border:1px solid #000;">{{ trans('admin.building') }}</td>
    				</tr>
                    <tr style=" border:1px solid #000;">
                        <th style=" border:1px solid #000;"></th>
    					<td style=" border:1px solid #000;"></td>
    					<th style=" border:1px solid #000;">@if($data->orderUser) {{$data->orderUser->flat}} @endif</th>
    					<td style=" border:1px solid #000;">{{ trans('admin.flat') }}</td>
    				</tr>
					<tr>
						<td></td>
						<td></td>
						<td>@if($data->orderUser && $data->orderUser->building) {{$data->orderUser->building->owner_name}} @endif</td>
						<td>المالك</td>
					</tr>
    			</table>
    		</div>
    		<div class="body">
        	    
				<div class="Signature2">
					<div class="box">
						<div class="head-title">
							<h3>{{ trans('admin.work_details') }} :</h3>
							<p>{{ $workDetails }}</p>
							<h4>{{ trans('admin.client') }} : {{ $data->orderUser->name }}</h4>
							<h4>{{ trans('admin.phone') }} : {{ $data->orderUser->phone }}</h4>
							
						</div>
					</div>
					<div class="box" style="padding-bottom:30px">
						<div class="head-title">
							<h3>المواد المستخدمة وأعمال الصيانة:</h3>
							<p>الفني : {{isset($data->orderTeam->name)? $data->orderTeam->name: trans('admin.no_assign_team')}}</p>
							<div class="line">
								
								
							</div>
							<table style="width:100%; text-align: left; border-collapse: collapse; margin:0 auto; border:0;  margin-bottom: 15px;">
								<tbody>
									<tr style="padding:0; ">
										<td  style="padding:0; width: 70%">
											<p>{{ trans('admin.materials_used') }} : {{ $materialsUsed }} </p>
										</td>
										<td style="padding:0;">
											<p>التكلفة: {{ $materialsUsedPrice.' ('.trans('admin.currency').')' }}</p>
										</td>
									</tr>
								</tbody>
								
							</table>
							<table style="width:100%; text-align: left; border-collapse: collapse; margin:0 auto; border:0;  margin-bottom: 15px;">
								<tbody>
									<tr style="padding:0; ">
										<td  style="padding:0; width: 70%">
											<p>{{ trans('admin.hand_work') }} : {{ $handWork }}  </p>
										</td>
										<td style="padding:0;">
											<p>التكلفة: {{ $handWorkPrice.' ('.trans('admin.currency').')' }}</p>
										</td>
									</tr>
								</tbody>
							</table>
							
							<span>التكلفة الإجمالية : {{ $handWorkPrice + $materialsUsedPrice }}</span>
						</div>
					</div>
					<div class="box">
						<div class="head-title">
							<h3>التوقيعات:</h3>
							<table style="width:100%; text-align: left; border-collapse: collapse; margin:0 auto; border:0;  margin-bottom: 15px;">
								<tbody>
									<tr style="padding:0; ">
										<td  style="padding:0; width: 30%;text-align:center">
											<p>توقيع المشرف المسئول </p>
										</td>
										<td style="padding:0; width: 30%;text-align:center">
											<p>توقيع الفني  </p>
										</td>
										<td style="padding:0; width: 30%;text-align:center">
											<span>أعتماد الحسابات </span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
    	</div>
    </body>
</html>