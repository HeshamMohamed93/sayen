@extends('admin.layout.body',['title' => 'المدن' ])

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

<!--begin::Portlet-->
<div class="m-portlet">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-name">
                <span class="m-portlet__head-icon m--hide">
                    <i class="la la-gear"></i>
                </span>
                <h3 class="m-portlet__head-text">
                   {{ __('admin.add') }} | {{ __('admin.city') }}
                </h3>
            </div>
        </div>
    </div>

    <!--begin::Form-->
    <form class="m-form" action="{{ config('app.admin_url') }}/cities" method="post" enctype="multipart/form-data">

            {{ csrf_field() }}
        <div class="m-portlet__body">

        @if($errors->any())
            <div class="m-alert m-alert--icon alert alert-danger" role="alert" id="m_form_1_msg">
                <div class="m-alert__icon">
                    <i class="la la-warning"></i>
                </div>
                <div class="m-alert__text">
                    {{ __('admin.fix_errors') }}
                </div>
                <div class="m-alert__close">
                    <button type="button" class="close" data-close="alert" aria-label="Close">
                    </button>
                </div>
            </div>
        @endif

         {{--<div class="form-group m-form__group row {{ $errors->has('country_id') ? 'has-danger' : ''}}">
                        <label for="country_id" class="col-1 col-form-label">{{ __('admin.country_name') }}</label>
                        <div class="col-9">

                                  <select name="country_id" id="category_id"  class="form-control" >
                                    <option value="">اختر الدولة</option>
                                        @if($countries)
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" {{ ($country->id ==  $country->country_id)? "selected" : "" }}>
                                                    {{ $country->name_ar }}</option>
                                            @endforeach
                                        @endif
                                </select>
                            {!! $errors->first('country_id', '<span class="form-control-feedback">:message</span>') !!}
                        </div>
          </div>--}}

            <div class="form-group m-form__group row {{ $errors->has('name') ? 'has-danger' : ''}}">
                <label for="name" class="col-1 col-form-label">{{ __('admin.name_en') }}</label>
                <div class="col-9">
                    <input type="text" name="name" class="form-control m-input"
                            placeholder="{{ __('admin.name') }}" value="{{ old('name') }}">
                    {!! $errors->first('name', '<span class="form-control-feedback">:message</span>') !!}
                </div>
            </div>

            <div class="form-group m-form__group row {{ $errors->has('name_ar') ? 'has-danger' : ''}}">
                <label for="name_ar" class="col-1 col-form-label">{{ __('admin.name') }}</label>
                <div class="col-9">
                    <input type="text" name="name_ar" class="form-control m-input"
                            placeholder="{{ __('admin.name_ar') }}" value="{{ old('name_ar') }}">
                    {!! $errors->first('name_ar', '<span class="form-control-feedback">:message</span>') !!}
                </div>
            </div>

            <div class="form-group m-form__group row">

            <div class="font-red-thunderbird">
                <i class="fa fa-exclamation-circle"></i>
            يمكنك تحريك العلامة يدوياً</div>

            <br>
            <div class="input-group">
                <input type="hidden" name="lat" id="latitude" value="24.621427">
                <input type="hidden" name="lng" id="longitude" value="46.658936">
             <input type="text" placeholder="ابحث في الخريطة"  class="form-control" id="search_box" />
             <span class="input-group-btn">
                <button class="btn blue" id="gmap_geocoding_btn">
                    <i class="fa fa-search"></i>
                </span>
            </div><br>

            <div id="map" class="gmaps form-control" style="height: 500px;"> </div>
        </div>






        </div>
        <div class="m-portlet__foot m-portlet__foot--fit">
            <div class="m-form__actions m-form__actions--solid">
                <div class="row">
                    <div class="col-3">
                    </div>
                    <div class="col-9">
                        <button type="submit" class="btn btn-brand">{{ __('admin.save') }}</button>
                        <a type="reset" href="{{ config('app.admin_url') }}/cities" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!--end::Form-->
</div>

<!--end::Portlet-->


<script>


var map;
var marker;
var searchBox;


function initMap() {

    var riyadh = {lat: 24.621427, lng: 46.658936};
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 24.621427, lng: 46.658936},
        zoom: 8
  });

    marker = new google.maps.Marker({
        position: riyadh,
        map: map,
        draggable: true
  });


    var input = document.getElementById('search_box');
    var searchBox = new google.maps.places.SearchBox(input);
    google.maps.event.addListener(searchBox ,'places_changed' ,function(){

      var places = searchBox.getPlaces();
      var bounds = new google.maps.LatLngBounds();
      var i , place;
      for ( i=0 ; place=places[i] ; i++) {
        bounds.extend(place.geometry.location);
        marker.setPosition(place.geometry.location);
  }
  map.fitBounds(bounds);
  map.setZoom(8);

});
    google.maps.event.addListener(marker ,'position_changed' ,function(){

      var lat = marker.getPosition().lat();
      var lng = marker.getPosition().lng();

      $('#latitude').val(lat);
      $('#longitude').val(lng);

});
}
</script>
 <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD0lvyEMmVw-jCqobmghYJaopzaks9M83A&libraries=places&callback=initMap"
      async defer>
</script>

@endsection
