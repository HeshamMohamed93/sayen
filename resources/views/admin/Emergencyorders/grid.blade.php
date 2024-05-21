@extends('admin.layout.body')

@section('content')

{{--  Search   --}}
<div class="row">
    <form class="m-form" action="{{route('emergency-orders.index')}}" method="get" id="search_form">
        <div class="form-group m-form__group row ">
            <label class="col-1 col-form-label"></label>
            <div class="col-6">
                @if(request()->order_type)
                    <input type="hidden" name="order_type" class="form-control m-input" value="{{request()->order_type}}" >
                @endif
                <input type="text" name="search" class="form-control m-input" value="{{request()->search}}" >
            </div>
            <div class="col-2">
                <button type="submit" class="btn btn-secondary m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
                    <span>
                        <i class="la la-search"></i>
                        <span>@lang('admin.search')</span>
                    </span>
                </button>
            </div>
        </div>
    </form>
    @if(request()->search)
        <div class="col-2">
            <button class="btn btn-success m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air" onclick="window.location.href='{{route('emergency-orders.index')}}'">
                <span >
                    <i class="la la-times-circle"></i>
                    <span>@lang('admin.remove_search')</span>
                </span>
            </button>
        </div>
    @endif
</div>
{{--  End search  --}}

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
                    <div class="m-portlet__head-caption status-filter">
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
                    <div class="col-6">
                        <label for="name" class="col-2 col-form-label">{{ trans('admin.date_from') }}</label>
                        <input type="date" class="form-control m-input date_from filter-status" data-search_parameter="date_from" data-current-url="{{route('emergency-orders.index')}}" name="date_from" value="{{request()->date_from}}">
                    </div>
                    <div class="col-6">
                        <label for="name" class="col-2 col-form-label">{{ trans('admin.date_to') }}</label>
                        <input type="date" class="form-control m-input date_to filter-status" data-search_parameter="date_to" data-current-url="{{route('emergency-orders.index')}}" name="date_to" value="{{request()->date_to}}">
                    </div>
                    <div class="col-3">
                        <label>{{trans('admin.status')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="order_type" data-current-url="{{route('emergency-orders.index')}}">
                            <option value="0">{{ trans('admin.all') }}</option> 
                            <option value="1" {{request()->order_type == 1? 'selected' : ''}}>{{ trans('admin.new_order') }}</option> 
                            <option value="5" {{request()->order_type == 5? 'selected' : ''}}>{{ trans('admin.assigned_team') }}</option> 
                            <option value="2" {{request()->order_type == 2? 'selected' : ''}}>{{ trans('admin.start_order_work') }}</option> 
                            <option value="3" {{request()->order_type == 3? 'selected' : ''}}>{{ trans('admin.done_order') }}</option> 
                            <option value="4" {{request()->order_type == 4? 'selected' : ''}}>{{ trans('admin.cancelled_order') }}</option> 
							<option value="6" {{request()->order_type == 6? 'selected' : ''}}>{{ trans('admin.go_work') }}</option>
                            @if(Auth::user()->show_order_deleted == 1)
                                <option value="7" {{request()->order_type == 7? 'selected' : ''}}>{{ trans('admin.deleted') }}</option>
                            @endif
                        </select>
                    </div>
                    <!-- <div class="col-3">
                        <label>{{trans('admin.client')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="order_client" data-current-url="{{route('emergency-orders.index')}}">
                            <option value="0">{{ trans('admin.all') }}</option> 
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{request()->order_client == $user->id? 'selected' : ''}}>{{ $user->name }} - {{ $user->phone }}</option> 
                            @endforeach
                        </select>
                    </div>
                    -->
                    <div class="col-3">
                        <label>{{trans('admin.team')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="order_team" data-current-url="{{route('emergency-orders.index')}}">
                            <option value="0">{{ trans('admin.all') }}</option> 
                            @foreach($teams as $Kteam => $team)
                                <option value="{{ $Kteam }}" {{request()->order_team == $Kteam? 'selected' : ''}}>{{ $team }}</option> 
                            @endforeach
                        </select>
                    </div> 
                    <div class="col-3">
                        <label>{{trans('admin.service')}}</label>
                        <select class="form-control m-input filter-status" data-search_parameter="order_service" data-current-url="{{route('emergency-orders.index')}}">
                            <option value="0">{{ trans('admin.all') }}</option> 
                            @foreach($services as $Kservice => $service)
                                <option value="{{ $Kservice }}" {{request()->order_service == $Kservice? 'selected' : ''}}>{{ $service }}</option> 
                            @endforeach
                        </select>
                    </div>
                </div>
                {{--  Submit form  --}}
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions m-form__actions--solid">
                        <div class="row">
                            <div class="col-9">
                                <button class="btn btn-brand createFilter" data-current-url="{{route('emergency-orders.index')}}">{{ trans('admin.create') }}</button>
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
                                        <th><b>@lang('admin.order_number')</b></th>
                                        <th><b>@lang('admin.client')</b></th>
                                        <th><b>@lang('admin.building')</b></th>
                                        <th><b>@lang('admin.floor')</b></th>
                                        <th><b>@lang('admin.flat')</b></th>
                                        <th><b>@lang('admin.service')</b></th>
                                        <th><b>@lang('admin.visit_date')</b></th>
                                        <th><b>@lang('admin.order_team')</b></th>
                                        <th><b>@lang('admin.order_status')</b></th>
                                        <th><b>@lang('admin.control')</b></th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @if(count($orders) == 0)
                                        <tr>
                                            <th scope="row" colspan="100%">
                                                <h2 style="text-align:center">
                                                    {{trans('admin.no_results')}}
                                                </th>
                                        </tr>
                                    @else 
                                        @foreach($orders as $index => $order)
                                            <tr>
                                                <th scope="row">{{$index + 1}}</th>
                                                <th scope="row">{{$order->order_number}}</th>
                                                <th scope="row">@if($order->orderUser){{$order->orderUser->name.' '.$order->orderUser->last_name}} @endif</th>
                                                <th scope="row">@if($order->orderUser && $order->orderUser->building) {{$order->orderUser->building->name}} @endif</th>
                                                <th scope="row">@if($order->orderUser) {{$order->orderUser->floor}} @endif</th>
                                                <th scope="row">@if($order->orderUser) {{$order->orderUser->flat}} @endif</th>
                                                <th scope="row">{{$order->orderService->title}}</th>
                                                <th scope="row">{{$order->visitDate12HFormat()}}</th>
                                                <th scope="row">{{isset($order->orderTeam->name)? $order->orderTeam->name: trans('admin.no_assign_team')}}</th>
                                                <th scope="row">{{$order->orderStatus()}}</th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_show)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.show_details')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('emergency-orders.show', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-eye m--font-primary"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(Auth::user()->permissions->can_edit)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.edit')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('emergency-orders.edit', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-edit m--font-info"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    @if($order->status == 2 || $order->status == 5)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.cancel')}}" class="btn-group mr-2"  role="group" aria-label="First group">
                                                            <a type="button" class="m-btn m-btn m-btn--square btn btn-secondary" data-toggle="modal" data-target="#exampleModalLong{{ $order->id }}">
                                                            <i style="color:red" class="fas fa-times"></i>
                                                            </a>
                                                        </div>
                                                        <!-- Modal -->
                                                        <div class="modal fade" id="exampleModalLong{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle{{ $order->id }}" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLongTitle{{ $order->id }}">{{ trans('admin.cancel_order') }} </h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <form id="cancelForm">
                                                                            <select class="form-control m-input problem" name="problem" >
                                                                                @foreach($reportProblems as $key => $reportProblem)
                                                                                    <option value="{{ $key }}">{{ $reportProblem }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </form>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                                                                        <button type="button" class="btn btn-primary cancel-btn" data-cancel-url="{{url('admin-panel/cancelOrder',$order->id)}}"> {{ trans('admin.save') }} </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if((Auth::user()->permissions->can_edit || Auth::user()->permissions->can_show) && $order->status == 3)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.send_invoice')}}" class="btn-group mr-2 send-invoice" data-order-id="{{$order->id}}" role="group" aria-label="First group">
                                                            <a type="button" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-bars"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    @if((Auth::user()->permissions->can_edit || Auth::user()->permissions->can_show) && $order->status == 4 && $order->pay_method == '2' )
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.refund')}}" class="btn-group mr-2 refund" data-order-id="{{$order->id}}" role="group" aria-label="First group">
                                                            <a type="button" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                @php
                                                                    $earlier = new DateTime($order->visit_date);
                                                                    $later = new DateTime($order->cancelled_at);
                                                                    $diff = $later->diff($earlier)->format("%a")*24;
                                                                @endphp
                                                                @if($diff < 24)
                                                                    {{trans('admin.no_refund')}}
                                                                @endif
                                                                <i class="fa fa-undo"></i>
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
                                        {!! $orders->appends(request()->query())->links() !!}
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
