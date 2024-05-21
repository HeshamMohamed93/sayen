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
                <label for="name" class="col-2 col-form-label">{{ trans('admin.user_app_android_url') }}</label>
                <div class="col-10">
                    <input type="text" name="user_app_android_url" class="form-control m-input" placeholder="{{ trans('admin.user_app_android_url') }}" value="{{ isset($setting->user_app_android_url) ? $setting->user_app_android_url: '' }}">
                </div>
            </div>
            
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.user_app_ios_url') }}</label>
                <div class="col-10">
                    <input type="text" name="user_app_ios_url" class="form-control m-input" placeholder="{{ trans('admin.user_app_ios_url') }}" value="{{ isset($setting->user_app_ios_url) ? $setting->user_app_ios_url: '' }}">
                </div>
            </div> 

            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.team_app_android_url') }}</label>
                <div class="col-10">
                    <input type="text" name="team_app_android_url" class="form-control m-input" placeholder="{{ trans('admin.team_app_android_url') }}" value="{{ isset($setting->team_app_android_url) ? $setting->team_app_android_url: '' }}">
                </div>
            </div>    
            
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.team_app_ios_url') }}</label>
                <div class="col-10">
                    <input type="text" name="team_app_ios_url" class="form-control m-input" placeholder="{{ trans('admin.team_app_ios_url') }}" value="{{ isset($setting->team_app_ios_url) ? $setting->team_app_ios_url: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row" style="display:none">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.user_app_android_version') }}</label>
                <div class="col-10">
                    <input type="text" name="user_app_android_version" class="form-control m-input" placeholder="{{ trans('admin.user_app_android_version') }}" value="{{ isset($setting->user_app_android_version) ? $setting->user_app_android_version: '' }}">
                </div>
            </div>
            
            <div class="form-group m-form__group row" style="display:none">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.user_app_ios_version') }}</label>
                <div class="col-10">
                    <input type="text" name="user_app_ios_version" class="form-control m-input" placeholder="{{ trans('admin.user_app_ios_version') }}" value="{{ isset($setting->user_app_ios_version) ? $setting->user_app_ios_version: '' }}">
                </div>
            </div> 

            <div class="form-group m-form__group row" style="display:none">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.team_app_android_version') }}</label>
                <div class="col-10">
                    <input type="text" name="team_app_android_version" class="form-control m-input" placeholder="{{ trans('admin.team_app_android_version') }}" value="{{ isset($setting->team_app_android_version) ? $setting->team_app_android_version: '' }}">
                </div>
            </div>    
            
            <div class="form-group m-form__group row" style="display:none">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.team_app_ios_version') }}</label>
                <div class="col-10">
                    <input type="text" name="team_app_ios_version" class="form-control m-input" placeholder="{{ trans('admin.team_app_ios_version') }}" value="{{ isset($setting->team_app_ios_version) ? $setting->team_app_ios_version: '' }}">
                </div>
            </div>


            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.about_sayen_shortcut') }}</label>
                <div class="col-10">
                    <textarea name="about_sayen_shortcut" class="form-control m-input" placeholder="{{ trans('admin.about_sayen_shortcut') }}">{{ isset($setting->about_sayen_shortcut) ? $setting->about_sayen_shortcut: '' }}</textarea>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.value_added') }}</label>
                <div class="col-10">
                    <input type="text" name="value_added" class="form-control m-input" placeholder="{{ trans('admin.value_added') }}" value="{{ isset($setting->value_added) ? $setting->value_added: '' }}">
                </div>
            </div> 
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.text_emergency') }}</label>
                <div class="col-10">
                    <textarea name="text_emergency" class="form-control m-input" placeholder="{{ trans('admin.text_emergency') }}">{{ isset($setting->text_emergency) ? $setting->text_emergency: '' }}</textarea>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.text_emergency_en') }}</label>
                <div class="col-10">
                    <textarea name="text_emergency_en" class="form-control m-input" placeholder="{{ trans('admin.text_emergency_en') }}">{{ isset($setting->text_emergency_en) ? $setting->text_emergency_en: '' }}</textarea>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-2 col-form-label">{{ trans('admin.images_maintenanance_report') }}</label>
                <div class="col-10">
                    <input type="file" class="form-control m-input" name="images[]" multiple >
                </div>
                @if($images)
                    @foreach($images as $image)
                        <div class="col-3">
                            <button type="button" class="close delete_one_image" data-delete-url = "{{ url('admin-panel/delete-one-image') }}/{{ $image->id }}" data-id="{{ $image->id }}" >&times;</button>
                            <img src="{{ url('public/uploads') }}/{{ $image->image }}" class="preview" width="200" height="200"/>
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


@endsection
