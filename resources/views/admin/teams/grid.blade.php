@extends('admin.layout.body')

@section('content')

{{--  Search   --}}
<div class="row">
    <form class="m-form" action="{{route('teams.index')}}" method="get" id="search_form">
        <div class="form-group m-form__group row ">
            <label class="col-1 col-form-label"></label>
            <div class="col-6">
                <input type="text" name="search" class="form-control m-input" value="{{request()->search}}" >
            </div>
            <div class="col-2">
                <button type="submit" class="btn btn-secondary m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
                    <span>
                        <i class="la la-search"></i>
                        <span>@lang('admin.search')</span>
                    </span>
                </button>
            </div>
        </div>
    </form>
    @if(request()->search)
        <div class="col-2">
            <button class="btn btn-success m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air" onclick="window.location.href='{{route('teams.index')}}'">
                <span >
                    <i class="la la-times-circle"></i>
                    <span>@lang('admin.remove_search')</span>
                </span>
            </button>
        </div>
    @endif
</div>
{{--  End search  --}}

<br>

<div class="row">
    <div class="col-lg-12">
        <div class="m-portlet m-portlet--last m-portlet--head-lg m-portlet--responsive-mobile" id="main_portlet">
            <div class="m-portlet__head">
                <div class="m-portlet__head-wrapper">
                    
                    {{--  Grid title  --}}
                    <div class="m-portlet__head-caption">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="fa fa-users"></i>
                            </span>
                            <h3 class="m-portlet__head-text">{{$page_title}}</h3>
                        </div>
                    </div>
                    {{--  End grid title  --}}

                    {{--  Filter  --}}
                    <div class="m-portlet__head-caption status-filter">
                        <div class="m-portlet__head-title">
                            <span class="m-portlet__head-icon">
                                <i class="fa fa-filter"></i>
                            </span>
                            <label>{{trans('admin.status')}}</label>
                            <div class="col-12">
                                <select class="form-control m-input filter-status" data-search_parameter="status" data-current-url="{{route('teams.index')}}">
                                    <option value="-1">{{ trans('admin.all') }}</option> 
                                    <option value="1" {{request()->status === '1'? 'selected' : ''}}>{{ trans('admin.active') }}</option> 
                                    <option value="0" {{request()->status === '0'? 'selected' : ''}}>{{ trans('admin.not_active') }}</option> 
                                </select>
                            </div>
                        </div>
                    </div>
                    {{--  End filter  --}}


                    {{--  Add new  --}}
                    @if(Auth::user()->permissions->can_create)
                        <div class="m-portlet__head-tools">
                            <a href="{{ route('teams.create') }}" class="btn btn-brand m-btn m-btn--icon m-btn--wide m-btn--md m--margin-right-10">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>@lang('admin.add_new')</span>
                                </span>
                            </a>
                        </div>
                    @endif
                    {{--  End add new  --}}
                </div>
            </div>
            
            {{--  Table section  --}}
            <div class="m-portlet__body">
                <div class="m-section">
                    <div class="m-section__content">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="myTable">
                                <thead>
                                    <tr>
                                        <th><b>#</b></th>
                                        <th><b>@lang('admin.name')</b></th>
                                        <th><b>@lang('admin.phone')</b></th>
                                        <th><b>@lang('admin.assigned_orders')</b></th>
                                        <th><b>@lang('admin.working_hours')</b></th>
                                        <th><b>@lang('admin.status')</b></th>
                                        <th><b>@lang('admin.image')</b></th>
                                        <th><b>@lang('admin.control')</b></th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @if(count($teams) == 0)
                                        <tr>
                                            <th scope="row" colspan="100%">
                                                <h2 style="text-align:center">
                                                    {{trans('admin.no_results')}}
                                                </th>
                                        </tr>
                                    @else 
                                        @foreach($teams as $index => $team)
                                            <tr>
                                                <th scope="row">{{$index + 1}}</th>
                                                <th scope="row">{{$team->name}}</th>
                                                <th scope="row">{{$team->phone}}</th>
                                                <th scope="row">{{$team->teamOrders()}}</th>
                                                <th scope="row">{{$team->workingHours()}}</th>
                                                <th scope="row">{{$team->teamStatus()['text']}}</th>
                                                <th scope="row"><img src="{{$team->image_path}}" width="70" height="70"/></th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_show)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.show_details')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('teams.show', $team->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-eye m--font-primary"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(Auth::user()->permissions->can_edit)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.edit')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('teams.edit', $team->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-edit m--font-info"></i>
                                                            </a>
                                                        </div>
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{$team->teamStatus()['tooltip']}}" class="btn-group mr-2 change-status" data-status="{{$team->active}}" data-change-status-url="{{ route('team-change-status', [$team->id, $team->teamStatus()['to_update_value']]) }}" role="group" aria-label="First group">
                                                            <a type="button" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                {!! $team->teamStatus()['icon'] !!}
                                                            </a>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(Auth::user()->permissions->can_delete)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.delete')}}" class="btn-group mr-2 delete-btn" data-delete-url="{{route('teams.destroy',$team->id)}}" role="group" aria-label="First group">
                                                            <a type="button" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="flaticon-delete-1 m--font-danger"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </th>
                                            </tr>
                                        @endforeach
                                    @endif 
                                </tbody>
                            </table>

                            {{--  Pagination  --}}
                            <div class="container">
                                <div class="text-center">
                                    {!! $teams->appends(request()->query())->links() !!}
                                </div>
                            </div>
                            {{--  End pagination  --}}
                        </div>
                    </div>
                </div>
            </div>
            {{--  End table section --}}

        </div>
    </div>
</div>

@endsection
