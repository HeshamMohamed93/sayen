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
                <h3 class="m-portlet__head-text">{{ trans('admin.order_up') }}</h3>
            </div>
        </div>
    </div>
    {{--  End header  --}}
    
    {{--  Form  --}}
    <form class="m-form form" id="form-report" enctype="multipart/form-data">
            @csrf() 

        <div class="m-portlet__body">
           
            {{--  Fields  --}}

            <!-- {{--  Fields  --}}
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.image') }}</label>
                <input type="file" class="image" name="image" >
                @if(isset($manitenanceReport) && $manitenanceReport->image)
                    <img src="{{ url('public/uploads') }}/{{ $manitenanceReport->image }}" class="preview" width="200" height="200"/>
                @endif
            </div> -->
            <div class="form-group m-form__group row">
            <label for="name" class="col-1 col-form-label">{{ trans('admin.image') }}</label>
                @foreach($images as $key => $image)
                    <div @if( isset($manitenanceReport) && $image->id == $manitenanceReport->image) class="col-3 selected-form" @else class="col-3" @endif>
                        <label for="image{{ $image->id }}">
                            <input type="radio" style="display:none" @if( isset($manitenanceReport) && $image->id == $manitenanceReport->image) class="radio-check selected-form" checked @else class="radio-check" @endif value="{{ $image->id }}" name="image_id" id="image{{ $image->id }}"  />
                            <img class="preview" width="200" height="200" src="{{ url('public/uploads') }}/{{ $image->image }}" />
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.client') }}</label>
                <div class="col-11">
                    <select class="form-control m-input filter-status user_id" name="user_id">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" >{{ $user->name }} - {{ $user->phone }}</option> 
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.building') }}</label>
                <div class="col-11">
                    <input name="building" type="text" class="form-control m-input building" placeholder="{{ trans('admin.building') }}" ></input>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.flat') }}</label>
                <div class="col-11">
                    <input name="flat" type="text" class="form-control m-input flat" placeholder="{{ trans('admin.flat') }}" ></input>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.service') }}</label>
                <div class="col-11">
                    <select class="form-control m-input filter-status service_id" multiple name="service_id[]">
                        @foreach($services as $Kservice => $service)
                            <option value="{{ $Kservice }}">{{ $service }}</option> 
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.order_team') }}</label>
                <div class="col-11">
                    <select class="form-control m-input service-teams team_id" name="team_id">
                        @foreach($teams as $team)
                            <option value="{{$team->id}}" selected>{{$team->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.visit_date') }}</label>
                <div class="col-11" >
                    <input type="text" class="form-control m-input visit-date datetimepicker" value="{{ date('Y-m-d h:i:s') }}" name="visit_date" >
                </div>
            </div>
            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.work_details') }}</label>
                <div class="col-11">
                    <textarea name="work_details" class="form-control m-input work_details" placeholder="{{ trans('admin.work_details') }}" ></textarea>
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.hand_work') }}</label>
                <div class="col-5">
                    <textarea name="hand_work" class="form-control m-input hand_work" placeholder="{{ trans('admin.hand_work') }}" >{{ (isset($manitenanceReport))? $manitenanceReport->hand_work: '' }}</textarea>
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.hand_work_price') }}</label>
                <div class="col-5">
                    <input name="hand_work_price" min=0 step=1 type="number" value="{{ (isset($manitenanceReport))? $manitenanceReport->hand_work_price: 0 }}" class="form-control m-input hand_work_price" placeholder="{{ trans('admin.hand_work_price') }}" ></input>
                </div>
            </div>

            <div class="form-group m-form__group row">
                <label for="name" class="col-1 col-form-label">{{ trans('admin.materials_used') }}</label>
                <div class="col-5">
                    <textarea name="materials_used" class="form-control m-input materials_used" placeholder="{{ trans('admin.materials_used') }}" ></textarea>
                </div>
                <label for="name" class="col-1 col-form-label">{{ trans('admin.materials_used_price') }}</label>
                <div class="col-5">
                    <input name="materials_used_price" min=0 step=1 type="number" value="{{ (isset($manitenanceReport))? $manitenanceReport->materials_used_price: 0 }}" class="form-control m-input materials_used_price" placeholder="{{ trans('admin.materials_used_price') }}" ></input>
                </div>
            </div>

            {{--  End fields  --}}

        </div>
    </form>
    <div class="m-portlet__foot m-portlet__foot--fit">
        <div class="m-form__actions m-form__actions--solid">
            <div class="row">
                <div class="col-6">
                    <button class="btn btn-brand saveEditOrderUp" data-saveEditOrderUp-url="{{url('admin-panel/save-edit-order-up')}}">{{ trans('admin.save') }}</button>
                </div>
                <!-- <div class="col-6" style="text-align: left;">
                   <button  class="btn btn-brand printOnePageOrderUp" data-printOrderUp-url="{{url('admin-panel/print-onepage-order')}}">{{ trans('admin.print') }}</button>
                   <a  download class="btn btn-brand printOrderUp" data-printOrderUp-url="{{url('admin-panel/print-order-up')}}">{{ trans('admin.pdf') }}</a>
                </div> -->
                <div class="printOnePage"></div>
            </div>
        </div>
    </div>
    {{--  End form  --}}
</div>


@endsection
