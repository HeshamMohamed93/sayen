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
                <label for="name" class="col-1 col-form-label">{{ trans('admin.code') }}</label>
                <div class="col-9">
                    <input type="text" name="code" class="form-control m-input code" placeholder="{{ trans('admin.code') }}" value="{{ isset($coupon->code) ? $coupon->code: '' }}" >
                </div>
                @if(isset($submit_action))
                    <div class="col-2">
                        <button type="button" class="btn btn-brand random-code" onclick="generateCode()">{{ trans('admin.generate_random_code') }}</button>
                    </div>
                @endif
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.discount_value') }}</label>
                <div class="col-5">
                    <input type="text" name="discount" class="form-control m-input" placeholder="{{ trans('admin.discount_value') }}" value="{{ isset($coupon->discount) ? $coupon->discount: '' }}">
                </div>
                <div class="col-6">
                    <select class="form-control" name="discount_type">
                        <option value="1" 
                            @if(isset($coupon->discount_type))
                                @if($coupon->discount_type == 1)
                                    selected
                                @endif 
                            @endif>%</option>

                        <option value="2" 
                            @if(isset($coupon->discount_type)) 
                                @if($coupon->discount_type == 2)
                                    selected
                                @endif 
                            @endif>{{trans('admin.currency')}}</option>
                    </select>
                </div>
            </div>            
            
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.num_of_users') }}</label>
                <div class="col-5">
                    <input type="text" class="form-control m-input" name="num_of_users" value="{{isset($coupon->num_of_users) ? $coupon->num_of_users:  ''}}">
                </div>

                <label for="name" class="col-2 col-form-label">{{ trans('admin.num_of_usage_per_user') }}</label>
                <div class="col-4">
                    <input type="text" class="form-control m-input" name="num_of_usage_per_user" value="{{isset($coupon->num_of_usage_per_user) ? $coupon->num_of_usage_per_user:  ''}}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.date_from') }}</label>
                <div class="col-5">
                    <input type="date" class="form-control m-input" name="date_from" value="{{isset($coupon->date_from) ? $coupon->date_from->format('Y-m-d'):  ''}}">
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.date_to') }}</label>
                <div class="col-5">
                    <input type="date" class="form-control m-input" name="date_to" value="{{isset($coupon->date_to) ? $coupon->date_to->format('Y-m-d'):  ''}}">
                </div>
            </div>

            <!-- <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.expired_at') }}</label>
                <div class="col-11">
                    <input type="date" class="form-control m-input" name="expired_at" value="{{isset($coupon->expired_at) ? $coupon->expired_at->format('Y-m-d'):  ''}}">
                </div>
            </div> -->

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <select class="form-control m-input" name="service_id">
                        <option value="0" @if(isset($coupon->service_id)) @if($coupon->service_id == 0) 'selected' @endif @endif>{{ trans('admin.all') }}</option>
                        @foreach ($services as $service)
                            <option value="{{$service->id}}"
                                @if(isset($coupon->service_id)) 
                                    @if($coupon->service_id == $service->id)
                                        @if($service->deleted_at != null)
                                            selected disabled
                                        @else 
                                            selected
                                        @endif 
                                    @endif 
                                @endif>
                                {{$service->name}}
                            </option> 
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
