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
                <img src="{{ isset($team->image) ? $team->image_path: asset('public/img/default_user.png') }}" class="preview" width="200" height="200"/>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                <div class="col-11">
                    <input type="text" name="name" class="form-control m-input" placeholder="{{ trans('admin.name') }}" value="{{ isset($team->name) ? $team->name: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.email') }}</label>
                <div class="col-11">
                    <input type="text" name="email" class="form-control m-input" placeholder="{{ trans('admin.email') }}" value="{{ isset($team->email) ? $team->email: '' }}">
                </div>
            </div>


            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.phone') }}</label>
                <div class="col-11">
                    <input type="text" name="phone" class="form-control m-input" placeholder="{{ trans('admin.phone') }}" value="{{ isset($team->phone) ? $team->phone: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.password') }}</label>
                <div class="col-11">
                    <input type="password" name="password" class="form-control m-input" placeholder="{{ trans('admin.password') }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.specialist') }}</label>
                <div class="col-11">
                    <select class="form-control m-input service_id" multiple name="service_id[]">
                        <option></option>
                        @foreach ($services as $service)
                            <option value="{{$service->id}}"
                                @if(isset($teamServices) && in_array($service->id,$teamServices)) 
                                    selected
                                @endif>
                                {{$service->name}}
                            </option> 
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!isset($submit_action))
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.current_location') }}</label>
                </div>
        
                <div class="form-group m-form__group row">
                    <div id="map" style="width: 100%; height: 400px;"></div>
                </div>
            @endif

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
    var lat = {{isset($team->lat) ? $team->lat : 0}};
    var lng = {{isset($team->lng) ? $team->lng : 0}};
</script>    

@endsection
