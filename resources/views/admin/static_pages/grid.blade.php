@extends('admin.layout.body')

@section('content')

{{--  Search   --}}
<div class="row">
    <form class="m-form" action="{{route('static-pages.index')}}" method="get" id="search_form">
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
            <button class="btn btn-success m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air" onclick="window.location.href='{{route('static-pages.index')}}'">
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
                                <i class="fa fa-book"></i>
                            </span>
                            <h3 class="m-portlet__head-text">{{$page_title}}</h3>
                        </div>
                    </div>
                    {{--  End grid title  --}}
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
                                        <th><b>@lang('admin.title')</b></th>
                                        <th><b>@lang('admin.content')</b></th>
                                        <th><b>@lang('admin.lang')</b></th>
                                        <th><b>@lang('admin.control')</b></th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @if(count($pages) == 0)
                                        <tr>
                                            <th scope="row" colspan="100%">
                                                <h2 style="text-align:center">
                                                    {{trans('admin.no_results')}}
                                                </th>
                                        </tr>
                                    @else 
                                        @foreach($pages as $index => $page)
                                            <tr>
                                                <th scope="row">{{$index + 1}}</th>
                                                <th scope="row">{{$page->title}}</th>
                                                <th scope="row">{{$page->content}}</th>
                                                <th scope="row">{{trans("admin.$page->lang")}}</th>
                                                <th scope="row">
                                                    @if(Auth::user()->permissions->can_show)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.show_details')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('static-pages.show', $page->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-eye m--font-primary"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(Auth::user()->permissions->can_edit)
                                                        <div data-toggle="tooltip" data-placement="bottom" title="{{trans('admin.edit')}}" class="btn-group mr-2" role="group" aria-label="First group">
                                                            <a type="button" href="{{ route('static-pages.edit', $page->id) }}" class="m-btn m-btn m-btn--square btn btn-secondary">
                                                                <i class="fa fa-edit m--font-info"></i>
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
                                    {!! $pages->appends(request()->query())->links() !!}
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
