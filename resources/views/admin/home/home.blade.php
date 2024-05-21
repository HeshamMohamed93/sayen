@extends('admin.layout.body')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand calendar">
                    <div id='calendar'></div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$users}}<h3><small>{{ trans('admin.users') }}</small> <i class="fa fa-users"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_1"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$services}}<h3><small>{{ trans('admin.services') }}</small> <i class="fa fa-cogs"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_2"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$total_profit.' '.trans('admin.currency')}}<h3><small>{{ trans('admin.total_profit') }}</small> <i class="fa fa-credit-card"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_3"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$total_orders}}<h3><small>{{ trans('admin.orders') }}</small> <i class="fa fa-calendar"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_4"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$total_pay_cache}}<h3><small>{{ trans('admin.pay_cache') }}</small> <i class="fa fa-credit-card"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_5"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row m-row--full-height">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="m-portlet  m-portlet--border-bottom-brand ">
                    <div class="m-portlet__body">
                        <div class="m-widget26">
                            <div class="m-widget26__number">{{$total_pay_online}}<h3><small>{{ trans('admin.pay_online') }}</small> <i class="fa fa-credit-card-alt"></i></h3></div>
                            <div class="m-widget26__chart" style="height:90px; width: 220px;">
                                <canvas id="m_chart_quick_stats_6"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="m--space-30"></div>
            </div>
        </div>
    </div>
</div>

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

<script> 
    var orders = [];
    @foreach ($orders as $order)
        orders.push({title: '{{$order->total.' '.trans_choice("admin.order", $order->total)}}', start: '{{$order->visit_date}}', className:['clickEventOrder','YO{{$order->visit_date}}'], backgroundColor:'#03a9f3'});    
    @endforeach
    console.log(orders);
    @foreach ($emergency_orders as $emergency_order)
        orders.push({title: '{{$emergency_order->total.' '.trans_choice("admin.order", $emergency_order->total)}}', start: '{{$emergency_order->visit_date}}', className:['clickEventEmergencyOrder','Y{{$emergency_order->visit_date}}'],backgroundColor:'red'});    
    @endforeach
</script>

@endsection
