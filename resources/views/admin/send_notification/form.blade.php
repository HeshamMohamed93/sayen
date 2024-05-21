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
    @if(session()->has('notification_message_error'))
        <div class="alert alert-danger" role="alert">
            {{ session()->get('notification_message_error') }}
        </div>
    @elseif(session()->has('notification_message'))    
        <div class="alert alert-success" role="alert">
            {{ session()->get('notification_message') }}
        </div>
    @endif
    {{--  Form  --}}
    <form class="m-form" action="{{route('sendNotification')}}" method="{{$method}}">
        <div class="m-portlet__body">
            {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.users') }}</label>
                <div class="col-12">
                    <select name="users[]" id="users" class="form-control m-input user_id_notification" multiple>
                        @foreach($users as $user)
                            <option value="{{$user->id}}" >{{$user->name}} - ( {{ $user->phone }} )</option>
                        @endforeach
                    </select>
                    <div class="form-group select-group">
                        <input type="checkbox" name="selectAll" id="checkbox" >Select All
                    </div>
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.notification_text') }}</label>
                <div class="col-12">
                    <input type="text" required name="text" class="form-control m-input" placeholder="{{ trans('admin.notification_text') }}" >
                </div>
            </div>


            {{--  End fields  --}}

        </div>

        {{--  Submit form  --}}
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ trans('admin.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
        {{--  End submit  --}}

    </form>
    {{--  End form  --}}
    
</div>

@endsection
