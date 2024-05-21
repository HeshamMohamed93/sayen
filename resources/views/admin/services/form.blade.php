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
                <img src="{{ isset($service->image) ? $service->image_path: asset('public/img/default_service.png') }}" class="preview" width="200" height="200"/>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                <div class="col-11">
                    <input type="text" name="name" class="form-control m-input" placeholder="{{ trans('admin.name') }}" value="{{ isset($service->name) ? $service->name: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name_en" class="col-1 col-form-label">{{ trans('admin.name_en') }}</label>
                <div class="col-11">
                    <input type="text" name="name_en" class="form-control m-input" placeholder="{{ trans('admin.name_en') }}" value="{{ isset($service->name_en) ? $service->name_en: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="parent" class="col-1 col-form-label">{{ trans('admin.parent') }}</label>
                <div class="col-11">
                    <select class="form-control" name="parent_id">
                        <option value="0">{{ trans('admin.no_parent') }}</option>
                        @foreach($services as $key => $servi)
                            <option value="{{ $key }}" @if(isset($service) && $service->parent_id == $key) selected @endif >{{ $servi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <h2 class="col-6">{{ trans("admin.count_hour") }}</h2>
            <div class="row">
                @for($i = 1; $i<= 24; $i++)
                    
                    <div class="form-group col-md-1">
                        <label for="name" class="col-1 col-form-label">{{ trans("admin.hour_$i") }}</label>
                        <input type="text" min=0 step=1 name="count_hour[{{$i}}]" class="form-control m-input" value="@if(isset($countHours) && count($countHours) > 0 && isset($countHours[$i])) {{ $countHours[$i] }} @else 0 @endif">
                    </div>
                    
                @endfor
            </div>excellence_client
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.initial_price').' ('.trans('admin.currency').')' }}</label>
                <div class="col-11">
                    <input type="text" name="initial_price" class="form-control m-input" placeholder="{{ trans('admin.initial_price') }}" value="{{ isset($service->initial_price) ? $service->initial_price: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.initial_price').' ('.trans('admin.excellence_client').')'.' ('.trans('admin.currency').')' }}</label>
                <div class="col-11">
                    <input type="text" name="initial_price_excellence_client" class="form-control m-input" placeholder="{{ trans('admin.initial_price') }}" value="{{ isset($service->initial_price_excellence_client) ? $service->initial_price_excellence_client: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.status') }}</label>
                <div class="col-11">
                    <select class="form-control" name="active">
                        <option value="1" @if(isset($service) && $service->active == 1) selected @endif >{{ trans('admin.active') }}</option>
                        <option value="0" @if(isset($service) && $service->active == 0) selected @endif >{{ trans('admin.not_dwactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.text') }}</label>
                <div class="col-11">
                    <input type="text" name="text" class="form-control m-input" placeholder="{{ trans('admin.text') }}" value="{{ isset($service->text) ? $service->text: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="text_en" class="col-1 col-form-label">{{ trans('admin.text_en') }}</label>
                <div class="col-11">
                    <input type="text" name="text_en" class="form-control m-input" placeholder="{{ trans('admin.text_en') }}" value="{{ isset($service->text_en) ? $service->text_en: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.warranty').' ('.trans('admin.days').')' }}</label>
                <div class="col-11">
                    <input type="number" min=0 step=1 name="warranty" class="form-control m-input" placeholder="{{ trans('admin.warranty') }}" value="{{ isset($service->warranty) ? $service->warranty: 0 }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.number_admin') }}</label>
                <div class="col-11">
                    <input type="text" name="number_admin" class="form-control m-input" placeholder="{{ trans('admin.number_admin') }}" value="{{ isset($service->number_admin) ? $service->number_admin: 0 }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.number_user') }}</label>
                <div class="col-11">
                    <input type="text" name="number_user" class="form-control m-input" placeholder="{{ trans('admin.number_user') }}" value="{{ isset($service->number_user) ? $service->number_user: 0 }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.device_number') }}</label>
                <div class="col-11">
                    <select class="form-control device_number" name="device_number">
                        <option value="1" @if(isset($service) && $service->device_number == 1) selected @endif >{{ trans('admin.yes') }}</option>
                        <option value="0" @if(isset($service) && $service->device_number == 0) selected @endif >{{ trans('admin.no') }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group m-form__group row numbersDiv" @if(isset($service) && $service->device_number == 0) style="display:none" @elseif(isset($service) && $service->device_number == 1) @else style="display:none" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.numbers') }}</label>
                <div class="col-11">
                    <input type="number" name="numbers" min=1 step=1 class="form-control m-input" placeholder="{{ trans('admin.numbers') }}" value="{{ isset($service->numbers) ? $service->numbers: 1 }}">
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
