@extends('admin.layout.body')
@section('content')

<div class="m-portlet">
    {{--  Header  --}}
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-name">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text"> #{{$log['old']['order_number']}}</h3>
            </div>
        </div>
    </div>
    {{--  End header  --}}


        <div class="m-portlet__body">

            {{--  Fields  --}}

            {{-- Order --}}
            <br>
            <h3 class="m-portlet__head-text">
                {{trans('admin.old_data') }}
            </h3>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <span>{{ (\App\Service::find($log['old']['service_id']))?\App\Service::find($log['old']['service_id'])->name:'' }}</span>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_status') }}</label>
                <div class="col-11">
                    @if($log['old']['status'] == 1)
                        <span>{{ trans('admin.new_order') }}</span>
                    @elseif($log['old']['status'] == 3)
                        <span>{{ trans('admin.done_order') }}</span>
                    @elseif($log['old']['status'] == 5)
                        <span>{{ trans('admin.assign_order_to_team') }}</span>
                    @elseif($log['old']['status'] == 6)
                        <span>{{ trans('admin.go_work') }}</span>
                    @elseif($log['old']['status'] == 2)
                        <span>{{ trans('admin.current_order') }}</span>
                    @elseif($log['old']['status'] == 4)
                        <span>{{ trans('admin.cancel_order') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_team') }}</label>
                <div class="col-11">
                    <span>{{ ($log['old']['team_id'] != null)?\App\Team::find($log['old']['team_id'])->name:'' }}</span>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.visit_date') }}</label>
                <div class="col-11" >
                    @if($oneLog->subject_type == 'App\Order')
                        <input type="text" class="form-control m-input visit-date datetimepicker" value="{{ $log['old']['visit_date']}}">
                    @else
                        <input type="text" class="form-control m-input visit-date datetimepicker" value="{{ $log['old']['created_at']}}">
                    @endif
                </div>
            </div>
            @if($oneLog->subject_type == 'App\Order')
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.order_notes') }}</label>
                    <div class="col-11">
                        <span>{{ $log['old']['notes'] }}</span>
                    </div>
                </div>
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.hand_work').' ('.trans('admin.currency').')' }}</label>
                    <div class="col-11">
                    <span>{{ $log['old']['hand_works'] }}</span>
                    </div>
                </div>
            @else
                <div class="form-group m-form__group row">
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.admin_note') }}</label>
                    <div class="col-11">
                        <span>{{ $log['old']['admin_note'] }}</span>
                    </div>
                </div>
            @endif
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.cancel_reason') }}</label>
                <div class="col-11">
                    <span>{{ (\App\ReportProblem::find($log['old']['cancel_reason']))?\App\ReportProblem::find($log['old']['cancel_reason'])->problem:'' }}</span>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.cancelled_at') }}</label>
                <div class="col-11">
                    <span>{{ $log['old']['cancelled_at'] }}</span>
                </div>
            </div>
            {{--  End fields  --}}
        </div>
        <hr>
        <div class="m-portlet__body">

            {{--  Fields  --}}

            {{-- Order --}}
            <br>
            <h3 class="m-portlet__head-text">
                {{trans('admin.new_data') }}
            </h3>
            <div class="form-group m-form__group row" @if(array_key_exists("service_id",$diff)) style="color:red" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <span>{{ (\App\Service::find($log['attributes']['service_id']))?\App\Service::find($log['attributes']['service_id'])->name:'' }}</span>
                </div>
            </div>
            <div class="form-group m-form__group row" @if(array_key_exists("status",$diff)) style="color:red" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_status') }}</label>
                <div class="col-11">
                    @if($log['attributes']['status'] == 1)
                        <span>{{ trans('admin.new_order') }}</span>
                    @elseif($log['attributes']['status'] == 3)
                        <span>{{ trans('admin.done_order') }}</span>
                    @elseif($log['attributes']['status'] == 5)
                        <span>{{ trans('admin.assign_order_to_team') }}</span>
                    @elseif($log['attributes']['status'] == 6)
                        <span>{{ trans('admin.go_work') }}</span>
                    @elseif($log['attributes']['status'] == 2)
                        <span>{{ trans('admin.current_order') }}</span>
                    @elseif($log['attributes']['status'] == 4)
                        <span>{{ trans('admin.cancel_order') }}</span>
                    @endif
                </div>
            </div>

            <div class="form-group m-form__group row" @if(array_key_exists("team_id",$diff)) style="color:red" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_team') }}</label>
                <div class="col-11">
                    <span>{{ ($log['attributes']['team_id'] != null)?\App\Team::find($log['attributes']['team_id'])->name:'' }}</span>
                </div>
            </div>
            @if($oneLog->subject_type == 'App\Order')
                <div class="form-group m-form__group row" @if(array_key_exists("visit_date",$diff)) style="color:red" @endif>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.visit_date') }}</label>
                    <div class="col-11" >
                        <input type="text" class="form-control m-input visit-date datetimepicker" value="{{ $log['attributes']['visit_date']}}">
                    </div>
                </div>
                <div class="form-group m-form__group row" @if(array_key_exists("notes",$diff)) style="color:red" @endif>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.order_notes') }}</label>
                    <div class="col-11">
                        <span>{{ $log['attributes']['notes'] }}</span>
                    </div>
                </div>
                <div class="form-group m-form__group row" @if(array_key_exists("hand_works",$diff)) style="color:red" @endif>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.hand_work').' ('.trans('admin.currency').')' }}</label>
                    <div class="col-11">
                    <span>{{ $log['attributes']['hand_works'] }}</span>
                    </div>
                </div>
            @else
                <div class="form-group m-form__group row" @if(array_key_exists("created_at",$diff)) style="color:red" @endif>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.visit_date') }}</label>
                    <div class="col-11" >
                        <input type="text" class="form-control m-input visit-date datetimepicker" value="{{ $log['attributes']['created_at']}}">
                    </div>
                </div>
                <div class="form-group m-form__group row" @if(array_key_exists("admin_note",$diff)) style="color:red" @endif>
                    <label for="name" class="col-1 col-form-label">{{ trans('admin.admin_note') }}</label>
                    <div class="col-11">
                        <span>{{ $log['attributes']['admin_note'] }}</span>
                    </div>
                </div>
            @endif    
            
            <div class="form-group m-form__group row" @if(array_key_exists("cancel_reason",$diff)) style="color:red" @endif >
                <label for="name" class="col-1 col-form-label">{{ trans('admin.cancel_reason') }}</label>
                <div class="col-11">
                    <span>{{ (\App\ReportProblem::find($log['attributes']['cancel_reason']))?\App\ReportProblem::find($log['attributes']['cancel_reason'])->problem:'' }}</span>
                </div>
            </div>
            <div class="form-group m-form__group row" @if(array_key_exists("cancelled_at",$diff)) style="color:red" @endif>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.cancelled_at') }}</label>
                <div class="col-11">
                    <span>{{ $log['attributes']['cancelled_at'] }}</span>
                </div>
            </div>

            {{--  End fields  --}}
            </div>
            {{--  Submit form  --}}
            <div class="m-portlet__foot m-portlet__foot--fit">
                <div class="m-form__actions m-form__actions--solid">
                    <div class="row">
                        <div class="col-9">
                            <a type="button" href="{{ url('admin-panel/logs') }}" class="btn btn-brand">{{ trans('admin.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            {{--  End submit  --}}
</div>
@endsection
