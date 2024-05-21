@extends('admin.layout.body')
@section('content')

@if( session('status') )
    <div class="m-alert m-alert--icon m-alert--air alert alert-success alert-dismissible fade show" role="alert">
        <div class="m-alert__icon">
            <i class="la la-warning"></i>
        </div>
        <div class="m-alert__text">
            <strong>{{ session('status') }}!</strong>
        </div>
        <div class="m-alert__close">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            </button>
        </div>
    </div>
@endif

<div class="m-portlet">
    {{--  Header  --}}
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-name">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text">{{$page_title}}</h3>
            </div>
        </div>
    </div>
    {{--  End header  --}}
    
    {{--  Form  --}}
    <form class="m-form" action="{{$submit_action}}" method="get">
        <div class="m-portlet__body">
            {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.report_type') }}</label>
                <div class="col-5">
                    <select name="type" class="form-control m-input type">
                        <option value="1" @if(request()->type == 1) {{'selected'}}@endif>{{trans('admin.total_sales')}}</option>
                        <option value="2" @if(request()->type == 2) {{'selected'}}@endif>{{trans('admin.total_daily_sales')}}</option>
                        <option value="3" @if(request()->type == 3) {{'selected'}}@endif>{{trans('admin.total_weekly_sales')}}</option>
                        <option value="4" @if(request()->type == 4) {{'selected'}}@endif>{{trans('admin.total_monthly_sales')}}</option>
                    </select>
                </div>

                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-5">
                    <select name="service[]" class="form-control m-input service_id" multiple>
                        <option @if(isset(request()->service) && in_array(0,request()->service)) {{'selected'}} @endif value="0">{{trans('admin.all')}}</option>
                        @foreach($services as $service)
                            <option value="{{$service->id}}" @if(isset(request()->service) && in_array($service->id,request()->service)) {{'selected'}} @endif>{{$service->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group m-form__group row date-from-to">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.date_from') }}</label>
                <div class="col-5">
                    <input type="date" class="form-control m-input date_from" name="date_from" value="{{request()->date_from}}">
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.date_to') }}</label>
                <div class="col-5">
                    <input type="date" class="form-control m-input date_to" name="date_to" value="{{request()->date_to}}">
                </div>
            </div>

            <div class="form-group m-form__group row month">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.month') }}</label>
                <div class="col-5">
                    <input type="month" class="form-control m-input month" name="month" value="{{request()->month}}">
                </div>
            </div>

            {{--  End fields  --}}

        </div>

        {{--  Submit form  --}}
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ trans('admin.create') }}</button>
                        <!-- <button type="button" class="btn btn-brand export-report" data-action="{{route('export-sales-report')}}">{{trans('admin.export')}}</button> -->
                    </div>
                </div>
            </div>
        </div>
        {{--  End submit  --}}

    </form>
    {{--  End form  --}}
    
</div>

@if(count($_GET) > 0)
<div id="table">
    <div class="row">
        <div class="col-lg-12">
            <div class="m-portlet m-portlet--last m-portlet--head-lg m-portlet--responsive-mobile" id="main_portlet">
                {{--  Table section  --}}
                <div class="m-portlet__body">
                    <div class="m-section">
                        <div class="m-section__content">
                            <div class="table-responsive">
                                <table class="table table-bordered datatables" id="myTable">
                                    <thead>
                                        <tr>
                                            <th><b>#</b></th>
                                            <th><b>{{trans('admin.order_number')}}</b></th>
                                            <!-- <th><b>{{trans('admin.order_date')}}</b></th> -->
                                            <th><b>{{trans('admin.service')}}</b></th>
                                            <th><b>{{trans('admin.client')}}</b></th>
                                            <th><b>{{trans('admin.client_type')}}</b></th>
                                            <th><b>@lang('admin.building')</b></th>
                                            <th><b>@lang('admin.floor')</b></th>
                                            <th><b>@lang('admin.flat')</b></th>
                                            <th><b>{{trans('admin.teams')}}</b></th>
                                            <th><b>{{trans('admin.pay_method')}}</b></th>
                                            <th><b>{{trans('admin.date')}}</b></th>
                                            <!-- <th><b>{{trans('admin.start_work')}}</b></th>
                                            <th><b>{{trans('admin.end_work')}}</b></th>
                                            <th><b>{{trans('admin.working_hours')}}</b></th> -->
                                            <th><b>{{trans('admin.hand_work')}}</b></th>
                                            <th><b>{{trans('admin.final_price')}}</b></th>
                                            <th><b>{{trans('admin.coupon_discount')}}</b></th>
                                            <th><b>{{trans('admin.order_status')}}</b></th>
                                            <!-- <th><b>{{trans('admin.show_map')}}</b></th> -->
                                            <th><b>{{trans('admin.show_invoice')}}</b></th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        @if(count($data) == 0)
                                            <tr>
                                                <th scope="row" colspan="100%">
                                                    <h2 style="text-align:center">
                                                        {{trans('admin.no_results')}}
                                                    </th>
                                            </tr>
                                        @else 
                                            @foreach($data as $index => $row)
                                                <tr>
                                                    <th scope="row">{{$index + 1}}</th>
                                                    <th scope="row">{{$row->order_number}}</th>
                                                    <!-- <th scope="row">{{$row->visit_date}}</th> -->
                                                    <th scope="row">{{$row->orderService->name}}</th>
                                                    <th scope="row">{{$row->orderUser->name}}</th>
                                                    <th scope="row">
                                                        @if($row->orderUser->excellence_client == 1)
                                                            {{ trans('admin.excellence_client') }}
                                                        @else
                                                            {{ trans('admin.normal_client') }}
                                                        @endif
                                                    </th>
                                                    <th scope="row">@if($row->orderUser->building) {{$row->orderUser->building->name}} @endif</th>
                                                    <th scope="row">{{$row->floor}}</th>
                                                    <th scope="row">{{$row->orderUser->flat}}</th>
                                                    <th scope="row">{{$row->orderTeam->name}}</th>
                                                    <th scope="row">{{$row->orderPayMethod()}}</th>
                                                    <th scope="row">@if($row->team_start_at) {{date('Y-m-d',strtotime($row->team_start_at))}} @endif</th>
                                                    <!-- <th scope="row">@if($row->team_start_at) {{date('H:i:s',strtotime($row->team_start_at))}} @endif</th>
                                                    <th scope="row">@if($row->team_end_at) {{date('H:i:s',strtotime($row->team_end_at))}} @endif</th> 
                                                    <th scope="row">{{$row->workingHours()}}</th>-->
                                                    <th scope="row">{{ $row->hand_work }}</th>
                                                    <th scope="row">{{$row->orderInvoice->final_price}} {{trans('admin.currency')}}</th>
                                                    <th scope="row">{{$row->orderInvoice->coupon_discount}} {{trans('admin.currency')}}</th>
                                                    <th scope="row">{{$row->orderStatus()}}</th>
                                                    <!-- <th scope="row"><a href="https://maps.google.com/?q={{ $row->lat }},{{ $row->lng }}" target="_blank">{{  trans('admin.click_here') }}</a></th> -->
                                                    <th scope="row"><a href="{{ url('admin-panel') }}/show-invoice/{{ $row->id }}" target="_blank">{{  trans('admin.click_here') }}</a></th>
                                                </tr>
                                            @endforeach
                                        @endif 
                                    </tbody>
                                </table>
    
                                {{--  Pagination  --}}
                                @if(count($data) > 0)
                                <!-- <div class="container">
                                    <div class="text-center">
                                        {!! $data->appends(request()->query())->links() !!}
                                    </div>
                                </div> -->
                                @endif
                                {{--  End pagination  --}}
                            </div>
                        </div>
                    </div>
                </div>
                {{--  End table section --}}
    
            </div>
        </div>
    </div>
</div>
@endif



@endsection
