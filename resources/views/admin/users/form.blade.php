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
    <form class="m-form form" @if(isset($submit_action)) action="{{$submit_action}}" @endif enctype="multipart/form-data">
        
        @if(!isset($submit_action))
            <fieldset disabled={true}>
        @else 
            @csrf() @method($method)
        @endif

        <div class="m-portlet__body">
            
            {{--  Error section  --}}
            <div class="m-alert m-alert--icon alert alert-danger error-div" role="alert" id="m_form_1_msg" style="display:none;">
                <div class="m-alert__icon">
                    <i class="la la-warning"></i>
                </div>
                <div class="m-alert__text error-messages"></div>  
                <div class="m-alert__close">
                    <button type="button" class="close" data-close="alert" aria-label="Close"></button>
                </div>
            </div>
            {{--  End error  --}}
            
            {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.image') }}</label>
                <input type="file" class="image" name="image" value="">
                <img src="{{ isset($user->image) ? $user->image_path: asset('public/img/default_service.png') }}" class="preview" width="200" height="200"/>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                <div class="col-11">
                    <input type="text" name="name" class="form-control m-input" placeholder="{{ trans('admin.name') }}" value="{{ isset($user->name) ? $user->name: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="last_name" class="col-1 col-form-label">{{ trans('admin.last_name') }}</label>
                <div class="col-11">
                    <input type="text" name="last_name" class="form-control m-input" placeholder="{{ trans('admin.last_name') }}" value="{{ isset($user->last_name) ? $user->last_name: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.phone') }}</label>
                <div class="col-11">
                    <input type="text" name="phone" class="form-control m-input" placeholder="{{ trans('admin.phone') }}" value="{{ isset($user->phone) ? $user->phone: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.email') }}</label>
                <div class="col-11">
                    <input type="text" name="email" class="form-control m-input" placeholder="{{ trans('admin.email') }}" value="{{ isset($user->email) ? $user->email: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.password') }}</label>
                <div class="col-11">
                    <input type="password" name="password" class="form-control m-input" placeholder="{{ trans('admin.password') }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.excellence_client') }}</label>
                <div class="col-11">
                    <select name="excellence_client" class="form-control m-input change-excellence-client">
                        <option value="1" @if(isset($user->excellence_client)) @if($user->excellence_client == 1) {{'selected'}} @endif @endif>{{trans('admin.yes')}}</option>
                        <option value="2" @if(isset($user->excellence_client)) @if($user->excellence_client == 2) {{'selected'}} @endif @endif>{{trans('admin.no')}}</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group m-form__group row building">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.building') }}</label>
                <div class="col-11">
                    <select class="form-control m-input" name="building_id">
                        @foreach ($buildings as $building)

                            @if(isset($user->building_id) && $user->building_id == $building->id && $building->deleted_at != null)
                                <option value="{{$building->id}}"selected disabled>{{$building->name}} ({{trans('admin.deleted')}})</option>

                            @elseif($building->deleted_at == null)
                                <option value="{{$building->id}}" @if(isset($user->building_id)) @if($building->id == $user->building_id) {{'selected'}} @endif @endif>{{$building->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group m-form__group row building">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.flat') }}</label>
                <div class="col-11">
                    <input type="flat" name="flat" class="form-control m-input" placeholder="{{ trans('admin.flat') }}" value="{{ isset($user->flat) ? $user->flat: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.excellence_client_verified') }}</label>
                <div class="col-11">
                    <select name="excellence_client_verified" class="form-control m-input">
                        <option value="0" @if(isset($user->excellence_client_verified)) @if($user->excellence_client_verified == 0) {{'selected'}} @endif @endif>{{trans('admin.no')}}</option>
                        <option value="1" @if(isset($user->excellence_client_verified)) @if($user->excellence_client_verified == 1) {{'selected'}} @endif @endif>{{trans('admin.yes')}}</option>
                    </select>
                </div>
            </div>
            {{--  End fields  --}}
            @if(isset($orders))
            <h5> {{ trans('admin.orders') }}</h5>
                <div class="m-section">
                    <div class="m-section__content">
                        <div class="table-responsive">
                            <table class="table table-bordered datatables" id="myTable">
                                <thead>
                                    <tr>
                                        <th><b>#</b></th>
                                        <th><b>@lang('admin.order_number')</b></th>
                                        <th><b>@lang('admin.client')</b></th>
                                        <th><b>@lang('admin.service')</b></th>
                                        <th><b>@lang('admin.visit_date') <br />(@lang('admin.date'))</b></th>
                                        <th><b>@lang('admin.visit_date') <br />(@lang('admin.time'))</b></th>
                                        <th><b>@lang('admin.order_team')</b></th>
                                        <th><b>@lang('admin.order_status')</b></th>
                                        <th><b>@lang('admin.team_start_date')</b></th>
                                        <th><b>@lang('admin.team_end_date')</b></th>
                                        <th><b>@lang('admin.working_hours')</b></th>
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
                                                <th scope="row">{{$order->orderUser->name}}</th>
                                                <th scope="row">{{$order->orderService->name}} @if(!empty($order->order_id) && $order->warranty == 0 ) - {{ trans('admin.warranty') }} @endif</th>
                                                <th scope="row">{{$order->visitDate12HFormat()}}</th>
                                                <th scope="row">{{$order->visitDate12HFormatTime()}}</th>
                                                <th scope="row">{{isset($order->orderTeam->name)? $order->orderTeam->name: trans('admin.no_assign_team')}}</th>
                                                <th scope="row">{{$order->orderStatus()}}</th>
                                                <th scope="row">{{$order->teamStartDate12HFormat()}}</th>
                                                <th scope="row">{{$order->teamEndDate12HFormat()}}</th>
                                                <th scope="row">{{$order->workingHours()}}</th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_show)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.show_details')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('orders.show', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-eye m--font-primary"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(Auth::user()->permissions->can_edit)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.edit')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('orders.edit', $order->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
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
                                                                    @if(isset($reportProblems))
                                                                        <div class="modal-body">
                                                                            <form id="cancelForm">
                                                                                <select class="form-control m-input problem" name="problem" >
                                                                                    @foreach($reportProblems as $key => $reportProblem)
                                                                                        <option value="{{ $key }}">{{ $reportProblem }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </form>
                                                                        </div>
                                                                    @endif
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
            @endif                    
        </div>

        {{--  Submit form  --}}
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ trans('admin.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
        {{--  End submit  --}}

    </form>
    {{--  End form  --}}
    
</div>


@endsection
