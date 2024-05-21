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
        @if(isset($offer))
            <div class="m-portlet__head-tools">
                <a class="btn btn-brand m-btn m-btn--icon m-btn--wide m-btn--md m--margin-right-10 offerSendNotification" data-id="{{ $offer->id }}">
                    <span>
                        <i class="flaticon-alert-2"></i>
                        <span>@lang('admin.send_notification')</span>
                    </span>
                </a>
            </div>
        @endif
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
            <div class="form-group m-form__group row" style="display:none">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.image') }}</label>
                <input type="file" class="image" name="image" value="">
                @if(isset($offer->image)) <img src="{{ isset($offer->image) ? $offer->image_path: asset('public/img/default_offer.png') }}" class="preview" width="200" height="200"/> @endif
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.title') }}</label>
                <div class="col-11">
                    <input type="text" name="title" class="form-control m-input" placeholder="{{ trans('admin.title') }}" value="{{ isset($offer->title) ? $offer->title: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name_en" class="col-1 col-form-label">{{ trans('admin.title_en') }}</label>
                <div class="col-11">
                    <input type="text" name="title_en" class="form-control m-input" placeholder="{{ trans('admin.title_en') }}" value="{{ isset($offer->title_en) ? $offer->title_en: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.count_from') }}</label>
                <div class="col-11">
                    <input type="number"  name="from" class="form-control m-input" placeholder="{{ trans('admin.count_from') }}" value="{{ isset($offer->from) ? $offer->from: '1' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="to" class="col-1 col-form-label">{{ trans('admin.count_to') }}</label>
                <div class="col-11">
                    <input type="number"  name="to" class="form-control m-input" placeholder="{{ trans('admin.count_to') }}" value="{{ isset($offer->to) ? $offer->to: '1' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="parent" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <select class="form-control changeService" name="service_id" require>
                        <option value="0" >{{ trans('admin.service') }}</option>
                        @foreach($services as $key => $servi)
                            <option value="{{ $key }}" @if(isset($offer) && $offer->service_id == $key) selected @endif >{{ $servi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="subServiceDiv">

            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.price').' ('.trans('admin.currency').')' }}</label>
                <div class="col-11">
                    <input type="text" name="price" class="form-control m-input" placeholder="{{ trans('admin.price') }}" value="{{ isset($offer->price) ? $offer->price: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.status') }}</label>
                <div class="col-11">
                    <select class="form-control" name="active">
                        <option value="1" @if(isset($offer) && $offer->status == 1) selected @endif >{{ trans('admin.active') }}</option>
                        <option value="0" @if(isset($offer) && $offer->status == 0) selected @endif >{{ trans('admin.not_dwactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.show_home') }}</label>
                <div class="col-11">
                    <select class="form-control" name="show">
                        <option value="1" @if(isset($offer) && $offer->show == 1) selected @endif >{{ trans('admin.active') }}</option>
                        <option value="0" @if(isset($offer) && $offer->show == 0) selected @endif >{{ trans('admin.not_dwactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.notification_text') }}</label>
                <div class="col-11">
                    <input type="text" name="text" class="form-control m-input" placeholder="{{ trans('admin.notification_text') }}" value="{{ isset($offer->text) ? $offer->text: '' }}">
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
<script>
  
    var serviceChange = "{{url('admin-panel/changeService')}}";
    var offerSendNotification = "{{url('admin-panel/offerSendNotification')}}";
</script>
@endsection
