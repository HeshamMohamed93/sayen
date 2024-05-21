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
                <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                <div class="col-11">
                    <input type="text" name="title" class="form-control m-input" placeholder="{{ trans('admin.name') }}" value="{{ isset($service->title) ? $service->title: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name_en" class="col-1 col-form-label">{{ trans('admin.name_en') }}</label>
                <div class="col-11">
                    <input type="text" name="title_en" class="form-control m-input" placeholder="{{ trans('admin.name_en') }}" value="{{ isset($service->title_en) ? $service->title_en: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.status') }}</label>
                <div class="col-11">
                    <select class="form-control" name="status">
                        <option value="1" @if(isset($service) && $service->status == 1) selected @endif >{{ trans('admin.active') }}</option>
                        <option value="0" @if(isset($service) && $service->status == 0) selected @endif >{{ trans('admin.not_dwactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="m-portlet__head">
                <h3 class="m-portlet__head-text col-6">{{ trans('admin.reasons') }}</h3>
                <div class="m-portlet__head-tools">
                    <a  class="btn btn-brand m-btn m-btn--icon m-btn--wide m-btn--md m--margin-right-10 addNewReason" data-type="reason" data-url="{{ url('admin-panel/emergency-services')}}">
                        <span>
                            <i class="la la-plus"></i>
                            <span>@lang('admin.add_new')</span>
                        </span>
                    </a>
                </div>
            </div>
            <div class="col-md-12 addNew" style="border-right: 1px solid black;">
                @if(isset($reasons))
                    @foreach($reasons as $key => $reason)
                        <div class="row reason_div">
                            <div class="col-12 form-group m-form__group">
                                    <label for="name_en" class="col-form-label">{{ trans('admin.reason') }}</label>
                                    <textarea name="reason[]" class="form-control m-input" placeholder="{{ trans('admin.reason') }}">{{ isset($reason->reason) ? $reason->reason: '' }}</textarea>
                            </div>
                            <div class="col-12 form-group m-form__group">
                                    <label for="name_en" class="col-form-label">{{ trans('admin.reason_en') }}</label>
                                    <textarea name="reason_en[]" class="form-control m-input" placeholder="{{ trans('admin.reason_en') }}" >{{ isset($reason->reason_en) ? $reason->reason_en: '' }}</textarea>
                            </div>
                            <div class="col-12 form-group m-form__group">
                                <label for="name" class="col-form-label">{{ trans('admin.status') }}</label>
                                <select class="form-control" name="status_reason[]">
                                    <option value="1" @if($reason->status == 1) selected @endif >{{ trans('admin.active') }}</option>
                                    <option value="0" @if($reason->status == 0) selected @endif >{{ trans('admin.not_dwactive') }}</option>
                                </select>
                            </div>
                            <div class="m-portlet__head">
                                <div class="m-portlet__head-tools">
                                    <a  class="btn btn-brand m-btn m-btn--icon m-btn--wide m-btn--md m--margin-right-10 removeReason" data-type="reason" data-id="{{ $service->id }}">
                                        <span>
                                            <i class="la la-minus"></i>
                                            <span>@lang('admin.remove')</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
        <div class="clonDIv" style="display:none">
            <div class="row reason_div">
                <div class="col-12 form-group m-form__group">
                        <label for="name_en" class="col-form-label">{{ trans('admin.reason') }}</label>
                        <textarea name="reason[]" class="form-control m-input" placeholder="{{ trans('admin.reason') }}"></textarea>
                </div>
                <div class="col-12 form-group m-form__group">
                        <label for="name_en" class="col-form-label">{{ trans('admin.reason_en') }}</label>
                        <textarea name="reason_en[]" class="form-control m-input" placeholder="{{ trans('admin.reason_en') }}" ></textarea>
                </div>
                <div class="col-12 form-group m-form__group">
                    <label for="name" class="col-form-label">{{ trans('admin.status') }}</label>
                    <select class="form-control" name="status_reason[]">
                        <option value="1" >{{ trans('admin.active') }}</option>
                        <option value="0" >{{ trans('admin.not_dwactive') }}</option>
                    </select>
                </div>
                <div class="m-portlet__head">
                    <div class="m-portlet__head-tools">
                        <a  class="btn btn-brand m-btn m-btn--icon m-btn--wide m-btn--md m--margin-right-10 removeReason" data-type="reason" >
                            <span>
                                <i class="la la-minus"></i>
                                <span>@lang('admin.remove')</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
@endsection
