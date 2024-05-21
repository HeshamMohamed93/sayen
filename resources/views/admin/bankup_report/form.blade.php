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
                <label for="name" class="col-1 col-form-label">{{ trans('admin.bankup_from') }}</label>
                <div class="col-11">
                    <select name="bankup_from" class="form-control m-input">
                        <option value="1" @if(request()->bankup_from == 1) {{'selected'}}@endif>{{trans('admin.client')}}</option>
                        <option value="2" @if(request()->bankup_from == 2) {{'selected'}}@endif>{{trans('admin.owner')}}</option>
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
                        <button type="button" class="btn btn-brand export-report" data-action="{{route('export-bankup-report')}}">{{trans('admin.export')}}</button>
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
                                <table class="table table-bordered" id="myTable">
                                    <thead>
                                        <tr>
                                            <th><b>#</b></th>
                                            <th><b>{{trans('admin.order_number')}}</b></th>
                                            <th><b>{{trans('admin.service')}}</b></th>
                                            <th><b>{{trans('admin.bankup_from')}}</b></th>
                                            <th><b>{{trans('admin.teams')}}</b></th>
                                            <th><b>{{trans('admin.visit_date')}}</b></th>
                                            <th><b>{{trans('admin.final_price')}}</b></th>
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
                                                    <th scope="row">{{$row->orderService->name}}</th>
                                                    <th scope="row">
                                                        @if($row->orderService->pay_by == 1)
                                                            {{trans('admin.client')}}
                                                        @else 
                                                            {{trans('admin.owner')}}
                                                        @endif
                                                    </th>
                                                    <th scope="row">{{$row->orderTeam->name}}</th>
                                                    <th scope="row">{{$row->visit_date}}</th>
                                                    <th scope="row">{{$row->orderInvoice->final_price}} {{trans('admin.currency')}}</th>
                                                </tr>
                                            @endforeach
                                        @endif 
                                    </tbody>
                                </table>
    
                                {{--  Pagination  --}}
                                @if(count($data) > 0)
                                <div class="container">
                                    <div class="text-center">
                                        {!! $data->appends(request()->query())->links() !!}
                                    </div>
                                </div>
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
