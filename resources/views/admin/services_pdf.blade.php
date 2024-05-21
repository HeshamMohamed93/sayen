<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>A simple, clean, and responsive HTML invoice template</title>

		<!-- Favicon -->
		<link rel="icon" href="./images/favicon.png" type="image/x-icon" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css" integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">
		<!-- Invoice styling -->
		<style>
			body {
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif, 'Cairo';
				text-align: center;
				color: #777;
			}

			body h1 {
				font-weight: 300;
				margin-bottom: 0px;
				padding-bottom: 0px;
				color: #000;
			}

			body h3 {
				font-weight: 300;
				margin-top: 10px;
				margin-bottom: 20px;
				font-style: italic;
				color: #555;
			}

			body a {
				color: #06f;
			}

			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
				border-collapse: collapse;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}
			
		</style>
	</head>

	<body>
		<h1>{{ trans('admin.invoice_order') }}</h1>
		<div class="invoice-box">
			<table>
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img class="rounded-circle" src="{{asset('public/img/logo.jpg')}}" alt="Sayen logo" style="width: 100%; max-width: 150px" />
								</td>

								<td>
									Order #: {{ $data->order_number }}<br />
									Created: {{ date('l jS \of F Y',strtotime($data->orderInvoice->created_at)) }}<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									{{ $data->address }}
								</td>

								<td>
									{{ $data->orderUser->name }}<br />
									{{ $data->orderUser->phone }}<br />
									{{ $data->orderUser->email }}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<!-- <tr class="details">
					<td>احمد</td>

					<td>1000</td>
				</tr> -->

				<tr class="heading">
					<td>{{ trans('admin.invoice_data') }}</td>

					<td></td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.pay_status')}}</td>

					<td>{{ $data->orderPayMethod() }}</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.service')}}</td>

					<td>{{$data->orderService->name}}</td>
				</tr>

				<tr class="item">
					<td>{{trans('admin.teams')}}</td>

					<td>{{$data->orderTeam->name}}</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.visit_date')}}</td>

					<td>@if($data->team_start_at) {{date('Y-m-d',strtotime($data->team_start_at))}} @endif</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.start_work')}}</td>

					<td>@if($data->team_start_at) {{date('H:i:s',strtotime($data->team_start_at))}} @endif</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.end_work')}}</td>

					<td>@if($data->team_end_at) {{date('H:i:s',strtotime($data->team_end_at))}} @endif</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.working_hours')}}</td>

					<td>{{$data->workingHours()}}</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.final_price')}}</td>

					<td>{{$data->orderInvoice->final_price}} {{trans('admin.currency')}}</td>
				</tr>
				<tr class="item">
					<td>{{trans('admin.order_status')}}</td>

					<td>{{$data->orderStatus()}}</td>
				</tr>

				<tr class="item last">
					<td>{{trans('admin.notes')}}</td>

					<td>{{$data->notes}}</td>
				</tr>

				<tr class="total">
					<td></td>

					<td>{{trans('admin.final_price')}} : {{$data->orderInvoice->final_price}} {{trans('admin.currency')}}</td>
				</tr>
			</table>
		</div>
	</body>
</html>
