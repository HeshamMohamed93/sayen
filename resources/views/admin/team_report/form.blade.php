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
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.report_date_to') }}</label>
                    <div class="col-5">
                        <div class="col-11" >
                            <input type="text" class="form-control m-input datetimepicker-report" value="{{ old('team-report-from') }}" name="team-report-from" >
                        </div>
                    </div>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.report_date_from') }}</label>
                    <div class="col-5">
                        <div class="col-11" >
                            <input type="text" class="form-control m-input datetimepicker-report" value="{{ old('team-report-to') }}" name="team-report-to" >
                        </div>
                    </div>
                </div>
            {{--  End fields  --}}
            {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <select name="service_id[]" class="form-control m-input service_id" multiple>
                        <option value="0" @if(isset(request()->service_id) && in_array(0,request()->service_id)) {{'selected'}}@endif>{{trans('admin.all')}}</option>
                        @foreach($services as $service)
                            <option value="{{$service->id}}" @if(isset(request()->service_id) && in_array($service->id,request()->service_id)) {{'selected'}}@endif>{{$service->name}}</option>
                        @endforeach
                    </select>
                </div>
            {{--  End fields  --}}
        </div>

        {{--  Submit form  --}}
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ trans('admin.create') }}</button>
                        <!-- <button type="button" class="btn btn-brand export-report" data-action="{{route('export-team-report')}}">{{trans('admin.export')}}</button> -->
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
                                            <th><b>{{trans('admin.name')}}</b></th>
                                            <th><b>{{trans('admin.service')}}</b></th>
                                            <th><b>{{trans('admin.total_orders')}}</b></th>
                                            <th><b>{{trans('admin.working_hours')}}</b></th>
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
                                                    <th scope="row">{{$row->name}}</th>
                                                    <th scope="row">
                                                        @foreach(\App\TeamService::where('team_id',$row->id)->get() as $serviceTeam)
                                                            @if($loop->last)    
                                                                {{ ($serviceTeam->Service)?$serviceTeam->Service->name:''}} 
                                                            @else
                                                            {{ ($serviceTeam->Service)?$serviceTeam->Service->name:''}} - 
                                                            @endif
                                                        @endforeach
                                                    </th>
                                                    @if($row->fromDate != null || $row->toDate != null)
                                                        <th scope="row">{{$row->teamOrdersWithDate($row->fromDate,$row->toDate)}}</th>
                                                    @else
                                                        <th scope="row">{{$row->teamOrders()}}</th>
                                                    @endif
                                                    <th scope="row">{{$row->workingHours()}}</th>
                                                </tr>
                                            @endforeach
                                        @endif 
                                    </tbody>
                                </table>
    
                                {{--  Pagination  --}}
                                <!-- @if(count($data) > 0)
                                <div class="container">
                                    <div class="text-center">
                                        {!! $data->appends(request()->query())->links() !!}
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
