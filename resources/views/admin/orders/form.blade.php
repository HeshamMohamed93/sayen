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
                <h3 class="m-portlet__head-text">{{$page_title}} #{{$order->order_number}}</h3>
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

            {{-- Client --}}
            <div class="disable-section">
                <h3 class="m-portlet__head-text">
                    {{trans('admin.client_data') }}
                </h3>
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderUser->name.' '.$order->orderUser->last_name}}">
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.phone') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderUser->phone}}">
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.address') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->address.' - '.trans('admin.floor').' '.$order->floor}}">
                    </div>
                </div>
            </div>

            @if($order->orderUser->excellence_client == 1 && $order->orderUser->building)
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.building') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderUser->building->name}}">
                    </div>
                </div>
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.flat') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderUser->flat}}">
                    </div>
                </div>
            @endif

            <div class="form-group m-form__group row">
                <div id="map" style="width: 100%; height: 400px;"></div>
            </div>

            {{-- Order --}}
            <br>
            <h3 class="m-portlet__head-text">
                {{trans('admin.order_data') }}
            </h3>
            <label for="name" class="col-1 col-form-label">{{ trans('admin.order_images') }}</label>
            <div class="image-set form-group m-form__group row">
                @foreach($order->orderImages() as $order_image)
                    <a class="example-image-link" href="{{ $order_image }}" data-lightbox="example-set" data-title="{{$order->notes}}">
                        <img class="example-image preview" width="200" height="200" src="{{ $order_image }}" alt="{{$order->notes}}">
                    </a>
                @endforeach
                
            </div>
            <!-- <label for="name" class="col-1 col-form-label">{{ trans('admin.order_images') }}</label>
            <div class="form-group m-form__group row">
                @foreach($order->orderImages() as $order_image)
                    <img src="{{ $order_image }}" class="preview" width="200" height="200"/>
                @endforeach
            </div> -->
            @if($order->finish_image)
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_finish_images') }}</label>
                <div class="form-group m-form__group row">
                    @foreach($order->orderFinishImages() as $order_finish_image)
                        <img src="{{ $order_finish_image }}" class="preview" width="200" height="200"/>
                    @endforeach
                </div>
            @endif
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <select class="form-control m-input change-order-service" name="service_id" >
                        @foreach ($services as $service)
                            @if($order->service_id == $service->id && $service->deleted_at != null)
                                <option value="{{$service->id}}"selected disabled>{{$service->name}}</option>

                            @elseif($order->service_id == $service->id && $service->deleted_at == null)
                                <option value="{{$service->id}}"selected>{{$service->name}}</option>

                            @else
                                <option value="{{$service->id}}">{{$service->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_status') }}</label>
                <div class="col-11">
                    <input type="hidden" class="order-status-value" name="status" value="{{$order->status}}" />
                    <select class="form-control order-status">

                            <option @if($order->status == 1) selected @endif value="1">{{ trans('admin.new_order') }}</option>
                            <option @if($order->status == 3) selected @endif value="3">{{ trans('admin.done_order') }}</option>
                            <option @if($order->status == 5) selected @endif value="5">{{ trans('admin.assign_order_to_team') }}</option>
                            <option @if($order->status == 6) selected @endif value="6">{{ trans('admin.go_work') }}</option>
                            <option @if($order->status == 2) selected @endif value="2">{{ trans('admin.current_order') }}</option>
                            <option @if($order->status == 4) selected @endif value="4">{{ trans('admin.cancel_order') }}</option>
                        <!-- @if($order->status == 1)
                            <option value="1" selected disabled>{{ trans('admin.new_order') }}</option>
                            <option value="5">{{ trans('admin.assign_order_to_team') }}</option>
                            <option value="4">{{ trans('admin.cancel_order') }}</option>

                        @elseif($order->status == 5)
                            <option value="5" selected disabled>{{ trans('admin.assign_order_to_team') }}</option>
                            <option value="2">{{ trans('admin.current_order') }}</option>
                            <option value="4">{{ trans('admin.cancel_order') }}</option>
                        @elseif($order->status == 2)
                            <option value="2" selected disabled>{{ trans('admin.current_order') }}</option>
                            <option value="3">{{ trans('admin.done_order') }}</option>

                        @elseif($order->status == 3)
                            <option value="3" selected disabled>{{ trans('admin.done_order') }}</option>

                        @elseif($order->status == 4)
                            <option value="4" selected disabled>{{ trans('admin.cancel_order') }}</option>
                        @elseif($order->status == 6)
                            <option value="5">{{ trans('admin.assign_order_to_team') }}</option>
                            <option value="6" selected disabled>{{ trans('admin.go_work') }}</option>
                            <option value="4">{{ trans('admin.cancel_order') }}</option>
                        @endif -->
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row problemDiv" style="display:none">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.cancel_reason') }}</label>
                <div class="col-11">
                    <select class="form-control m-input problem" name="problem" >
                        @foreach($reportProblems as $key => $reportProblem)
                            <option value="{{ $key }}">{{ $reportProblem }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_team') }}</label>
                <div class="col-11">
                    <select class="form-control m-input service-teams" name="team_id">
                        @if(!isset($order->orderTeam->name))
                            <option disabled selected>{{trans('admin.no_assign_team')}}</option>
                        @endif

                        @foreach($serviceTeam as $team)
                            @if($order->team_id == $team->id && $team->deleted_at != null)
                                <option value="{{$team->id}}" selected>{{$team->name}}</option>
                            @elseif($team->deleted_at == null)
                                <option value="{{$team->id}}" {{($order->team_id == $team->id)? 'selected' : ''}}>{{$team->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            @if(isset($order->orderTeam->name))
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.phone') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderTeam->phone}}" readonly>
                    </div>
                </div>
            @endif

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.visit_date') }}</label>
                <div class="col-11" >
                    <input type="text" class="form-control m-input visit-date datetimepicker" name="visit_date" value="{{$order->visit_date->format('Y-m-d H:i')}}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_notes') }}</label>
                <div class="col-11">
                    <textarea class="form-control m-input" readonly>{{$order->notes}}</textarea>
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.start_end_work') }}</label>
                <div class="col-11">
                    <input type="text" class="form-control m-input" value="{{ isset($order->team_start_at) ? $order->team_start_at.' - '.$order->team_end_at.' ('.$total_duration.')' : trans('admin.not_start_working')}}" readonly>
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.hand_work').' ('.trans('admin.currency').')' }}</label>
                <div class="col-11">
                    <input class="form-control m-input"  type="number" name="hand_work" value="{{$order->hand_work}}">
                </div>
            </div>

            @if($order->status == 4)
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.cancelled_at') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->cancelled_at}}" readonly>
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.cancel_reason') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{($order->cancel_reason != null) ? $order->orderCancelReason->problem : ''}}" readonly>
                    </div>
                </div>
            @endif

            {{-- Invoice --}}
            <div class="disable-section">
                <br>
                <h3 class="m-portlet__head-text">
                    {{trans('admin.invoice_data') }}
                </h3>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.pay_status') }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input" value="{{$order->orderPayMethod().' - '.$order->orderPayStatus()}}">
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.pay_by') }}</label>
                    <div class="col-11">
                        <label class="form-control m-input">
                            {{($order->orderInvoice && $order->orderInvoice->pay_by == 1)? trans('admin.client') : trans('admin.owner')}}
                        </label>
                    </div>
                </div>

                @if($order->orderTransaction)
                    <div class="form-group m-form__group row">
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.visa_company') }}</label>
                        <div class="col-2">
                            <label class="form-control m-input">{{$order->orderTransaction->company}}</label>
                        </div>
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.visa_username') }}</label>
                        <div class="col-2">
                            <label class="form-control m-input">{{$order->orderTransaction->name}}</label>
                        </div>
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.visa_number') }}</label>
                        <div class="col-2">
                            <label class="form-control m-input">{{$order->orderTransaction->number}}</label>
                        </div>
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.visa_paid_amount') }}</label>
                        <div class="col-2">
                            <label class="form-control m-input">{{$order->orderTransaction->pay_amount.' '.trans('admin.currency')}}</label>
                        </div>

                    </div>
                @endif

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.initial_price').' ('.trans('admin.currency').')' }}</label>
                    <div class="col-11">
                        <input type="text" class="form-control m-input"  @if($order->status == 2) type="number" name="initial_price" style="pointer-events: all;" require @else type="text"@endif value="{{($order->orderInvoice)?$order->orderInvoice->initial_price:0}}">
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <div class="col-12">
                        <table class="table table-bordered" id="myTable">
                            <thead>
                                <tr>
                                    <th><b>@lang('admin.team_added_price')</b></th>
                                    <th><b>@lang('admin.team_added_price_desc')</b></th>
                                </tr>
                            </thead>
                                @if($order->orderInvoice)
                                    @foreach ($order->orderInvoice->teamAddedPrice() as $index => $added_price)
                                        <tr>
                                            <th scope="row">{{$added_price.' '.trans('admin.currency')}}</th>
                                            <th scope="row">{{($order->orderInvoice)?$order->orderInvoice->teamAddedPriceDesc()[$index]:''}}</th>
                                        </tr>
                                    @endforeach
                                @endif
                            <tr>
                            @if($order->orderInvoice)
                                <th colspan="100%"><b>{{trans('admin.team_total_added_price'). array_sum($order->orderInvoice->teamAddedPrice()).' '.trans('admin.currency')}}</b></th>
                            @endif
                            </tr>
                        </table>
                    </div>
                </div>

                @if($order->orderInvoice && $order->orderInvoice->coupon_id != null)
                    <div class="form-group m-form__group row">
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.coupon_code')}}</label>
                        <div class="col-11">
                            <input type="text" class="form-control m-input" value="{{$order->orderInvoice->orderCoupon->code}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group row">
                        <label for="name" class="col-1 col-form-label">{{ trans('admin.coupon_discount').' ('.trans('admin.currency').')' }}</label>
                        <div class="col-11">
                            <input type="text" class="form-control m-input" value="{{$order->orderInvoice->coupon_discount}}">
                        </div>
                    </div>
                @endif

                
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.final_price').' ('.trans('admin.currency').')' }}</label>
                    <div class="col-11">
                        <input class="form-control m-input" @if($order->status == 2) type="number" name="final_price" style="pointer-events: all;" require @else type="text"@endif value="{{($order->orderInvoice)?$order->orderInvoice->final_price:0}}">
                    </div>
                </div>

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.team_pay_confirmed') }}</label>
                    <div class="col-11">
                        @if($order->orderInvoice && $order->orderInvoice->team_receive_money == 1)
                        <input type="text" class="form-control m-input" value="{{trans('admin.yes')}}">
                        @else
                        <input type="text" class="form-control m-input" value="{{trans('admin.no')}}">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rate service --}}
            <div class="disable-section">
                <br>
                <h3 class="m-portlet__head-text">
                    {{trans('admin.rate_service') }}
                </h3>

                @if($order->rate_service_value > 0)
                    @php $no_rate = 5-$order->rate_service_value; @endphp
                    @while($order->rate_service_value > 0)
                    <img src="{{asset('public/img/rate.png')}}" width="20" height="20"/>
                    @php  $order->rate_service_value--; @endphp
                    @endwhile

                    @while($no_rate > 0)
                        <img src="{{asset('public/img/no_rate.png')}}" width="20" height="20"/>
                        @php  $no_rate--; @endphp
                    @endwhile
                @else
                    @for($i=0; $i<5; $i++)
                        <img src="{{asset('public/img/no_rate.png')}}" width="20" height="20"/>
                    @endfor
                @endif

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.comment') }}</label>
                    <div class="col-11">
                        <textarea class="form-control m-input" >{{$order->rate_service_comment}}</textarea>
                    </div>
                </div>

                {{-- Rate team --}}
                <br>
                <h3 class="m-portlet__head-text">
                    {{trans('admin.rate_team') }}
                </h3>

                @if($order->rate_team_value > 0)
                    @php $no_rate = 5-$order->rate_team_value; @endphp
                    @while($order->rate_team_value > 0)
                    <img src="{{asset('public/img/rate.png')}}" width="20" height="20"/>
                    @php  $order->rate_team_value--; @endphp
                    @endwhile

                    @while($no_rate > 0)
                        <img src="{{asset('public/img/no_rate.png')}}" width="20" height="20"/>
                        @php  $no_rate--; @endphp
                    @endwhile
                @else
                    @for($i=0; $i<5; $i++)
                        <img src="{{asset('public/img/no_rate.png')}}" width="20" height="20"/>
                    @endfor
                @endif

                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.comment') }}</label>
                    <div class="col-11">
                        <textarea class="form-control m-input" >{{$order->rate_team_comment}}</textarea>
                    </div>
                </div>

                @if($order->visit_date->format('Y-m-d') == Carbon\Carbon::now()->format('Y-m-d'))
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.team_location') }}</label>
                    <div class="form-group m-form__group row">
                        <div id="map-team" style="width: 100%; height: 400px;"></div>
                    </div>
                @endif

            </div>

            {{--  End fields  --}}
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

<script>
    
    var lat = {{$order->lat}};
    var lng = {{$order->lng}};
    var team_lat = {{isset($order->orderTeam->lat)? $order->orderTeam->lat: 0}};
    var team_lng = {{isset($order->orderTeam->lng)? $order->orderTeam->lng: 0}};
    var service_teams_url = "{{route('service-teams')}}";
</script>
@endsection
