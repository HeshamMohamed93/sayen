@if($services)
    <div class="form-group m-form__group row">
        <label for="parent" class="col-1 col-form-label">{{ trans('admin.service') }}<br> ( {{ trans('admin.sub_service') }} )</label>
        <div class="col-11">
            <select class="form-control changeService" name="service_id" require>
                <option value="0" >{{ trans('admin.service') }}</option>
                @foreach($services as $key => $servi)
                    <option value="{{ $key }}" >{{ $servi }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif