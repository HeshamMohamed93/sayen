@extends('admin.layout.body')
@section('style')
<style>
    .select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>
@endsection
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
                <img src="{{ isset($admin->image) ?$admin->image_path: asset('public/img/default_user.png') }}" class="preview" width="200" height="200"/>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.name') }}</label>
                <div class="col-11">
                    <input type="text" name="name" class="form-control m-input" placeholder="{{ trans('admin.name') }}" value="{{ isset($admin->name) ? $admin->name: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.email') }}</label>
                <div class="col-11">
                    <input type="text" name="email" class="form-control m-input" placeholder="{{ trans('admin.email') }}" value="{{ isset($admin->email) ? $admin->email: '' }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.password') }}</label>
                <div class="col-11">
                    <input type="password" name="password" class="form-control m-input" placeholder="{{ trans('admin.password') }}" value="{{ old('password') }}">
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="type" class="col-1 col-form-label">{{ trans('admin.type') }}</label>
                <div class="col-6">
                <select class="form-control m-input type" name="type">
                        <option @if(isset($admin) && $admin->type == 'admin')selected @endif value="admin">{{ trans('admin.all_service') }}</option> 
                        <option @if(isset($admin) && $admin->type == 'service')selected @endif value="service">{{ trans('admin.service') }}</option> 
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="type" class="col-1 col-form-label">{{ trans('admin.show_order_deleted') }}</label>
                <div class="col-6">
                <select class="form-control m-input type" name="show_order_deleted">
                        <option @if(isset($admin) && $admin->show_order_deleted == 1)selected @endif value="1">{{ trans('admin.yes') }}</option> 
                        <option @if(isset($admin) && $admin->show_order_deleted == 0)selected @endif value="0">{{ trans('admin.no') }}</option> 
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="type" class="col-1 col-form-label">{{ trans('admin.show_client_deleted') }}</label>
                <div class="col-6">
                <select class="form-control m-input type" name="show_client_deleted">
                        <option @if(isset($admin) && $admin->show_client_deleted == 1)selected @endif value="1">{{ trans('admin.yes') }}</option> 
                        <option @if(isset($admin) && $admin->show_client_deleted == 0)selected @endif value="0">{{ trans('admin.no') }}</option> 
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row serviceAdmin" @if(isset($admin) && $admin->type == 'service') style="display:block" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.specialist') }}</label>
                <div class="col-6">
                    <select class="form-control m-input service_id" multiple name="service_id[]">
                        <option></option>
                        @foreach ($services as $service)
                            <option value="{{$service->id}}"
                                @if(isset($adminServices) && in_array($service->id,$adminServices)) 
                                    selected
                                @endif>
                                {{$service->name}}
                            </option> 
                        @endforeach
                    </select>
                </div>
            </div>

            <h3 class="m-portlet__head-text">
                {{trans('admin.permissions') }}
            </h3>

            <table class="table table-bordered" id="myTable">
                <thead>
                    <tr>
                        <th><b>@lang('admin.page')</b></th>
                        <th><b>@lang('admin.show')</b></th>
                        <th><b>@lang('admin.create')</b></th>
                        <th><b>@lang('admin.update')</b></th>
                        <th><b>@lang('admin.delete')</b></th>
                    </tr>
                </thead>

                @foreach($moduless as $module)
                    @php
                        $can_show = 0;
                        $can_create = 0;
                        $can_edit = 0;
                        $can_delete = 0;
                    @endphp
                    <tr>
                        <th scope="row">{{$module->name}}</th>
                        @if(isset($admin_permissions))
                            @foreach($admin_permissions as $admin_permission)
                                @if($admin_permission->module_id == $module->id && $admin_permission->can_show == 1)
                                    @php $can_show = 1; @endphp
                                @endif
                                @if($admin_permission->module_id == $module->id && $admin_permission->can_create == 1)
                                    @php $can_create = 1; @endphp
                                @endif
                                @if($admin_permission->module_id == $module->id && $admin_permission->can_edit == 1)
                                    @php $can_edit = 1; @endphp
                                @endif
                                @if($admin_permission->module_id == $module->id && $admin_permission->can_delete == 1)
                                    @php $can_delete = 1; @endphp
                                @endif
                            @endforeach
                        @endif

                        <th scope="row">
                            @if($module->prefix != 'sales-report' && $module->prefix != 'sales-report' && $module->prefix != 'bankup-report' && $module->prefix != 'maintenance-report' && $module->prefix != 'team-report')
                                <input type="checkbox" name="can_show[]" class="form-control m-input" value="{{$module->id}}" @if($can_show == 1) checked @endif>
                            @endif
                        </th>
                        <th scope="row">
                            @if($module->prefix != 'orders' && $module->prefix != 'static-pages' && $module->prefix != 'contact-us' && $module->prefix != 'settings' )
                                <input type="checkbox" name="can_create[]" class="form-control m-input" value="{{$module->id}}" @if($can_create == 1) checked @endif>
                            @endif
                        </th>
                        <th scope="row">
                            @if($module->prefix != 'contact-us' && $module->prefix != 'sales-report' && $module->prefix != 'bankup-report' && $module->prefix != 'maintenance-report' && $module->prefix != 'team-report')
                                <input type="checkbox" name="can_edit[]" class="form-control m-input" value="{{$module->id}}" @if($can_edit == 1) checked @endif>
                            @endif
                        </th>
                        <th scope="row">
                            @if($module->prefix != 'orders' && $module->prefix != 'static-pages' && $module->prefix != 'contact-us' && $module->prefix != 'settings' && $module->prefix != 'sales-report' && $module->prefix != 'bankup-report' && $module->prefix != 'maintenance-report' && $module->prefix != 'team-report')
                                <input type="checkbox" name="can_delete[]" class="form-control m-input" value="{{$module->id}}" @if($can_delete == 1) checked @endif>
                            @endif
                        </th>
                    </tr>
                @endforeach
            </table>

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
