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
                <label for="name" class="col-1 col-form-label">{{ trans('admin.desc_problem') }}</label>
                <div class="col-11">
                    <input type="text" name="desc_problem" class="form-control m-input" placeholder="{{ trans('admin.desc_problem') }}" value="{{ isset($report_problem->problem) ? $report_problem->problem: '' }}">
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.desc_problem_en') }}</label>
                <div class="col-11">
                    <input type="text" name="desc_problem_en" class="form-control m-input" placeholder="{{ trans('admin.desc_problem_en') }}" value="{{ isset($report_problem->problem_en) ? $report_problem->problem_en: '' }}">
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
