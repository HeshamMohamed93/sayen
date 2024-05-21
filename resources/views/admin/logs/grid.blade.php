@extends('admin.layout.body')

@section('content')

<br>

<div class="row">
    <div class="col-lg-12">
        <div class="m-portlet m-portlet--last m-portlet--head-lg m-portlet--responsive-mobile" id="main_portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-wrapper">
                    {{--  Grid title  --}}
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            <h3 class="m-portlet__head-text">{{$page_title}}</h3>
                        </div>
                    </div>
                    {{--  End grid title  --}}
                    
                    {{--  Filter  --}}
                    <div class="m-portlet__head-caption status-filter filter-btn">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="fa fa-filter"></i>
                            </span>
                            
                        </div>
                    </div>
                    
                    {{--  End filter  --}}

                </div>
            </div>
            <div class="filter-div">
                <div class="row">
                    <!-- <div class="col-6">
                        <label for="name" class="col-2 col-form-label">{{ trans('admin.date_from') }}</label>
                        <input type="date" class="form-control m-input date_from filter-status" data-search_parameter="date_from" data-current-url="{{route('orders.index')}}" name="date_from" value="{{request()->date_from}}">
                    </div> -->
                    <div class="col-3">
                        <label>{{trans('admin.type')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="type" data-current-url="{{route('logs.index')}}">
                            <option value="all">{{ trans('admin.all') }}</option> 
                            <option value="order" {{request()->type == 'order'? 'selected' : ''}}>{{ trans('admin.orders') }}</option>
                            <option value="emergency_orders" {{request()->type == 'emergency_orders'? 'selected' : ''}}>{{ trans('admin.emergency_orders') }}</option>  
                        </select>
                    </div>

                    <div class="col-6">
                        <label for="name" class="col-2 col-form-label">{{ trans('admin.orders') }}</label>
                        <input type="text" class="form-control m-input filter-status" data-search_parameter="order_id" data-current-url="{{route('logs.index')}}" name="order_id" value="{{request()->order_id}}">
                    </div>
                    <div class="col-3">
                        <label>{{trans('admin.admins')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="admin_id" data-current-url="{{route('logs.index')}}">
                            <option value="0">{{ trans('admin.all') }}</option> 
                            @foreach($admins as $Kteam => $aadmin)
                                <option value="{{ $Kteam }}" {{request()->admin_id == $Kteam? 'selected' : ''}}>{{ $aadmin }}</option> 
                            @endforeach
                        </select>
                    </div>
                </div>
                {{--  Submit form  --}}
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions m-form__actions--solid">
                        <div class="row">
                            <div class="col-9">
                                <button class="btn btn-brand createFilter" data-current-url="{{route('logs.index')}}">{{ trans('admin.create') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{--  End submit  --}}
            </div>
            {{--  Table section  --}}
            <div class="m-portlet__body">
                <div class="m-section">
                    <div class="m-section__content">
                        <div class="table-responsive">
                            <table class="table table-bordered datatables" id="myTable">
                                <thead>
                                    <tr>
                                        <th><b>#</b></th>
                                        <th><b>@lang('admin.description')</b></th>
                                        <th><b>@lang('admin.orders')</b></th>
                                        <th><b>@lang('admin.type')</b></th>
                                        <th><b>@lang('admin.admin')</b></th>
                                        <th><b>@lang('admin.date')</b></th>
                                        <th><b>@lang('admin.control')</b></th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @if(count($logs) == 0)
                                        <tr>
                                            <th scope="row" colspan="100%">
                                                <h2 style="text-align:center">
                                                    {{trans('admin.no_results')}}
                                                </th>
                                        </tr>
                                    @else 
                                        @foreach($logs as $index => $order)
                                            <tr>
                                                <th scope="row">{{$index + 1}}</th>
                                                <th scope="row">{{$order->description}}</th>
                                                <th scope="row">{{$order->subject_id}}</th>
                                                <th scope="row">{{($order->subject_type == 'App\Order')?trans('admin.orders'):trans('admin.emergency_orders')}}</th>
                                                <th scope="row">{{ \App\Admin::find($order->causer_id)->name }}</th>
                                                <th scope="row">{{$order->created_at}}</th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_show)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.show_details')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('logs.show', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-eye m--font-primary"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </th>
                                            </tr>
                                        @endforeach
                                    @endif 
                                </tbody>
                            </table>

                            {{--  Pagination  
                                <div class="container">
                                    <div class="text-center">
                                        {!! $logs->appends(request()->query())->links() !!}
                                    </div>
                                </div>
                            --}}
                            {{--  End pagination  --}}
                        </div>
                    </div>
                </div>
            </div>
            {{--  End table section --}}

        </div>
    </div>
</div>
@endsection
