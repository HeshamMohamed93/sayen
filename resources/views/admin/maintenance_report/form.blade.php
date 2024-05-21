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
            <label for="name" class="col-1 col-form-label">{{ trans('admin.date_from') }}</label>
                <div class="col-5">
                    <div class="col-11" >
                        <input type="text" class="form-control m-input datetimepicker-report" value="{{ old('maintence-report-from') }}" name="maintence-report-from" >
                    </div>
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.date_to') }}</label>
                <div class="col-5">
                    <div class="col-11" >
                        <input type="text" class="form-control m-input datetimepicker-report" value="{{ old('maintence-report-to') }}" name="maintence-report-to" >
                    </div>
                </div>
            </div>
            {{--  End fields  --}}
            {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.report_type') }}</label>
                <div class="col-5">
                    <select name="type" class="form-control m-input maintence-report-type">
                        <option value="1" @if(request()->type == 1) {{'selected'}}@endif>{{trans('admin.user')}}</option>
                        <option value="2" @if(request()->type == 2) {{'selected'}}@endif>{{trans('admin.building')}}</option>
                    </select>
                </div>
                
                <div class="col-6 user">
                    <select name="user_id" class="form-control m-input service_id">
                    <option @if(request()->user_id == 0) {{'selected'}}@endif value="0">{{trans('admin.all')}}</option>
                        @foreach($users as $user)
                            <option value="{{$user->id}}" @if(request()->user_id == $user->id) {{'selected'}}@endif>{{($user->name)? $user->name : $user->phone}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 building">
                    <select name="building_id" class="form-control m-input">
                        <option @if(request()->building_id == 0) {{'selected'}}@endif value="0">{{trans('admin.all')}}</option>
                        @foreach($buildings as $building)
                            <option value="{{$building->id}}" @if(request()->building_id == $building->id) {{'selected'}}@endif>{{($building->name)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
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
            {{--  End fields  --}}
        </div>

        {{--  Submit form  --}}
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ trans('admin.create') }}</button>
                        <!-- <button type="button" class="btn btn-brand export-report" data-action="{{route('export-maintenance-report')}}">{{trans('admin.export')}}</button> -->
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
                                <table class="table table-bordered maintenanance" id="myTable">
                                    <thead>
                                        <tr>
                                            <th><b>#</b></th>
                                            <th><b>{{trans('admin.order_number')}}</b></th>
                                            <th><b>{{trans('admin.order_date')}}</b></th>
                                            <th><b>{{trans('admin.type_order')}}</b></th>
                                            <th><b>{{trans('admin.service')}}</b></th>
                                            <th><b>{{trans('admin.client')}}</b></th>
                                            <th><b>{{trans('admin.client_type')}}</b></th>
                                            <th><b>{{trans('admin.address')}}</b></th>
                                            <th><b>{{trans('admin.teams')}}</b></th>
                                            <th><b>{{trans('admin.visit_date')}}</b></th>
                                            <th><b>{{trans('admin.start_work')}}</b></th>
                                            <th><b>{{trans('admin.end_work')}}</b></th>
                                            <th><b>{{trans('admin.working_hours')}}</b></th>
                                            <th><b>{{trans('admin.visiting_fee')}}</b></th>
                                            <th><b>{{trans('admin.status')}}</b></th>
                                            <th><b>{{trans('admin.show_report')}}</b></th>
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
                                                    <th scope="row">{{$row->created_at}}</th>
                                                    <th scope="row">{{ trans("admin.$row->type_order")}}</th>
                                                    <th scope="row">{{$row->orderService->name}}</th>
                                                    <th scope="row">@if($row->orderUser) {{ $row->orderUser->name }} @endif</th>
                                                    <th scope="row">
                                                        @if($row->orderUser && $row->orderUser->excellence_client == 1)
                                                            {{ trans('admin.excellence_client') }}
                                                        @else
                                                            {{ trans('admin.normal_client') }}
                                                        @endif
                                                    </th>
                                                    <th scope="row">
                                                        @if($row->orderUser)
                                                            {{ $row->address.' - '.trans('admin.floor').' '.$row->floor.' - '.trans('admin.flat').' '.$row->orderUser->flat }}
                                                        @else
                                                            {{ $row->address.' - '.trans('admin.floor') }}
                                                        @endif
                                                    </th>
                                                    <th scope="row">@if($row->orderTeam) {{$row->orderTeam->name}} @endif</th>
                                                    <th scope="row">@if($row->team_end_at) {{date('Y-m-d',strtotime($row->team_end_at))}} @endif</th>
                                                    <th scope="row">@if($row->team_start_at) {{date('H:i:s',strtotime($row->team_start_at))}} @endif</th>
                                                    <th scope="row">@if($row->team_end_at) {{date('H:i:s',strtotime($row->team_end_at))}} @endif</th>
                                                    <th scope="row">@if($row->workingHours()) {{$row->workingHours()}} @endif</th>
                                                    <th scope="row">@if($row->orderInvoice) {{$row->orderInvoice->final_price}} {{trans('admin.currency')}} @endif</th>
                                                    <th scope="row">@if($row->orderStatus()) {{$row->orderStatus()}} @endif</th>
                                                    <th scope="row"><a href="{{ url('admin-panel') }}/edit-maintenance-report/{{ $row->type_order }}/{{ $row->id }}"  target="_blank" style="cursor: pointer;" data-id="{{ $row->id }}"  >{{  trans('admin.click_here') }}</a></th>
                                                    <!-- <th scope="row"><a href="#" style="cursor: pointer;" class="printMaintenanance" data-id="{{ $row->id }}" data-printMaintenanance-url="{{url('admin-panel/print-maintenanance')}}">{{  trans('admin.click_here') }}</a></th> -->
                                                    <!-- <th scope="row"><a href="https://maps.google.com/?q={{ $row->lat }},{{ $row->lng }}" target="_blank">{{  trans('admin.click_here') }}</a></th> -->
                                                </tr>
                                            @endforeach
                                        @endif 
                                    </tbody>
                                </table>
    
                                {{--  Pagination  --}}
                                <!-- @if(count($data) > 0)
                                <div class="container">
                                    <div class="text-center">
                                         $data->appends(request()->query())->links() 
                                    </div>
                                </div>
                                @endif -->
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
