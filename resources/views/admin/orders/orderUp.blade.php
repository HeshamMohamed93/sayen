@extends('admin.layout.body')

@section('content')

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
                </div>
            </div>
            <div class="filter-div">
                {{--  Submit form  --}}
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions m-form__actions--solid">
                        <div class="row">
                            <div class="col-9">
                                <a class="btn btn-brand" href="{{route('order-up.create')}}">{{ trans('admin.create') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        <th><b>@lang('admin.team')</b></th>
                                        <th><b>@lang('admin.visit_date') <br />(@lang('admin.date'))</b></th>
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
                                                <th scope="row"> @if($order->orderUser) {{$order->orderUser->name.' '.$order->orderUser->last_name}} @endif</th>
                                                <th scope="row"> @if($order->orderTeam) {{$order->orderTeam->name}} @endif</th>
                                                <th scope="row">{{$order->visitDate12HFormat()}}</th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_edit)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.edit')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('order-up.edit', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-edit m--font-info"></i>
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
