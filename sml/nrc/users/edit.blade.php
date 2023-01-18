@extends('layouts.admin')
@section('content')
    <div class="card">
        <div class="card-header">
            {{ trans('global.edit') }} {{ trans('cruds.user.title_singular') }}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', [$user->id]) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="form-group">
                    <label class="required" for="name">{{ trans('cruds.user.fields.name') }}</label>
                    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name"
                        id="name" value="{{ old('name', $user->name) }}" required>
                    @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.name_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="required" for="phone">{{ trans('cruds.user.fields.phone') }}</label>
                    <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" type="text"
                        name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required>
                    @if ($errors->has('phone'))
                        <div class="invalid-feedback">
                            {{ $errors->first('phone') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.phone_helper') }}</span>
                </div>
                {{-- <div class="form-group">
                <label class="required" for="email">{{ trans('cruds.user.fields.email') }}</label>
                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
                @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.user.fields.email_helper') }}</span>
            </div> --}}
                <div class="form-group">
                    <label for="password">{{ trans('cruds.user.fields.password') }}</label>
                    <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password"
                        name="password" id="password">
                    @if ($errors->has('password'))
                        <div class="invalid-feedback">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.password_helper') }}</span>
                </div>
                <div class="form-group">
                    <label class="required" for="role_id">{{ trans('cruds.user.fields.role') }}</label>
                    <select class="form-control {{ $errors->has('role_id') ? 'is-invalid' : '' }}" name="role_id"
                        id="role_id">
                        @foreach ($roles as $id => $entry)
                            <option value="{{ $id }}"
                                {{ old('role_id') == $id || $user->role_id == $id ? 'selected' : '' }}>
                                {{ $entry }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('role_id'))
                        <div class="invalid-feedback">
                            {{ $errors->first('role_id') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.role_helper') }}</span>
                </div>
                <div class="form-group nrc-group"
                    style="display:{{ old('role_id') == config('constants.role_id.patient') ||
                    $user->role_id == config('constants.role_id.patient')
                        ? 'block'
                        : 'none' }};"
                    data-visible="{{ config('constants.role_id.patient') }}">
                    <div class="form-group">
                        <label class="required form-label" for="nrc">{{ trans('cruds.user.fields.nrc') }}</label>
                        <div class="form-row">
                            <div class="col-3">
                                <select class="form-control {{ $errors->has('meta.nrc_state') ? 'is-invalid' : '' }}"
                                    name="meta[nrc_state]" id="nrc_state">
                                    <option value="">--</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <select class="form-control {{ $errors->has('meta.nrc_township') ? 'is-invalid' : '' }}"
                                    name="meta[nrc_township]" id="nrc_township">
                                    <option value="">--</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <select class="form-control {{ $errors->has('meta.nrc_type') ? 'is-invalid' : '' }}"
                                    name="meta[nrc_type]" id="nrc_type">
                                    <option value="">--</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <input class="form-control {{ $errors->has('meta.nrc_no') ? 'is-invalid' : '' }}"
                                    type="text" name="meta[nrc_no]" id="nrc_no" value="{{ old('meta.nrc_no', '') }}">
                                @if ($errors->has('meta.nrc_no'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('meta.nrc_no') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <?php // dd(is_null($user->userUserMeta)); ?>
                    <div class="form-group">
                        <label class="form-label required" for="dob">{{ trans('global.birthday') }}</label>
                        <input class="form-control date {{ $errors->has('meta.dob') ? 'is-invalid' : '' }}" type="text"
                            name="meta[dob]" id="dob"
                            value="{{ old('meta.dob', isset($user->userUserMeta) && $user->userUserMeta->count() > 0 ? \App\Helpers\SiteHelper::getDisplayedDate($user->userUserMeta->first()->dob($user->id)) : '') }}">
                        @error('meta.dob')
                            <div class="invalid-feedback">
                                {{ $errors->first('meta.dob') }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label required" for="address"> {{ trans('global.address') }} </label>
                        <textarea name="meta[address]" id="address"
                            class="form-control {{ $errors->has('meta.address') ? 'is-invalid' : '' }}">{{ old('meta.address', isset($user->userUserMeta) && $user->userUserMeta->count() > 0 ? $user->userUserMeta->first()->address($user->id) : '') }}</textarea>
                        @error('meta.address')
                            <div class="invalid-feedback">
                                {{ $errors->first('meta.address') }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="form-group licence-no"
                    style="display:{{ old('role_id') == config('constants.role_id.professional') ||
                    $user->role_id == config('constants.role_id.professional')
                        ? 'block'
                        : 'none' }};"
                    data-visible="{{ config('constants.role_id.professional') }}">
                    <label class="required" for="licence_no">{{ trans('cruds.user.fields.licence_no') }}</label>
                    <input class="form-control {{ $errors->has('meta.licence_no') ? 'is-invalid' : '' }}" type="text"
                        name="meta[licence_no]" id="licence_no" value="{{ old('meta.licence_no', '') }}">
                    @if ($errors->has('meta.licence_no'))
                        <div class="invalid-feedback">
                            {{ $errors->first('meta.licence_no') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.licence_no_helper') }}</span>
                </div>
                <div class="form-group speciality-group"
                    style="display:{{ old('role_id') == config('constants.role_id.professional') ||
                    $user->role_id == config('constants.role_id.professional')
                        ? 'block'
                        : 'none' }};"
                    data-visible="{{ config('constants.role_id.professional') }}">
                    <label class="required" for="specialities">{{ trans('cruds.user.fields.speciality') }}</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-info btn-xs select-all"
                            style="border-radius: 0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all"
                            style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('specialities') ? 'is-invalid' : '' }}"
                        name="specialities[]" id="specialities" multiple>
                        @foreach ($specialities as $id => $speciality)
                            <option value="{{ $id }}"
                                {{ in_array($id, old('specialities', [])) || $user->specialities->contains($id) ? 'selected' : '' }}>
                                {{ $speciality }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('specialities'))
                        <div class="invalid-feedback">
                            {{ $errors->first('specialities') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.speciality_helper') }}</span>
                </div>
                <div class="form-group business-group"
                    style="display:{{ old('role_id') == config('constants.role_id.clinic') ||
                    old('role_id') == config('constants.role_id.business') ||
                    $user->role_id == config('constants.role_id.clinic') ||
                    $user->role_id == config('constants.role_id.business')
                        ? 'block'
                        : 'none' }};"
                    data-visible="{{ config('constants.role_id.clinic') }}, {{ config('constants.role_id.business') }}">
                    <label class="required" for="businesses">{{ trans('cruds.user.fields.business') }}</label>
                    <div style="padding-bottom: 4px">
                        <span class="btn btn-info btn-xs select-all"
                            style="border-radius: 0">{{ trans('global.select_all') }}</span>
                        <span class="btn btn-info btn-xs deselect-all"
                            style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                    </div>
                    <select class="form-control select2 {{ $errors->has('businesses') ? 'is-invalid' : '' }}"
                        name="businesses[]" id="businesses" multiple>
                        @foreach ($businesses as $id => $business)
                            <option value="{{ $id }}"
                                {{ in_array($id, old('businesses', [])) || $user->businesses->contains($id) ? 'selected' : '' }}>
                                {{ $business }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('businesses'))
                        <div class="invalid-feedback">
                            {{ $errors->first('businesses') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.business_helper') }}</span>
                </div>
                <div class="form-group">
                    <label>{{ trans('cruds.user.fields.is_active') }}</label>
                    <select class="form-control {{ $errors->has('is_active') ? 'is-invalid' : '' }}" name="is_active"
                        id="is_active">
                        <option value disabled {{ old('is_active', null) === null ? 'selected' : '' }}>
                            {{ trans('global.pleaseSelect') }}</option>
                        @foreach (App\Models\User::IS_ACTIVE_SELECT as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('is_active', $user->is_active) === (string) $key ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('is_active'))
                        <div class="invalid-feedback">
                            {{ $errors->first('is_active') }}
                        </div>
                    @endif
                    <span class="help-block">{{ trans('cruds.user.fields.is_active_helper') }}</span>
                </div>
                <div class="form-group">
                    <button class="btn btn-danger" type="submit">
                        {{ trans('global.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    @can('user_edit')
        <script>
            $(function() {
                let nrcTypeList = @json($nrc_types);
                let nrcList = @json($nrcs);
                let user = @json($user);
                let oldJson = @json(session()->getOldInput());
                let meta = {};

                let userMeta = user.user_user_meta ?
                    user.user_user_meta.reduce(
                        (acc, value) => ({
                            ...acc,
                            [value.meta_key]: value.meta_value
                        }),
                        meta
                    ) : null;

                let oldData = !Array.isArray(oldJson) ? oldJson.meta : userMeta;
                let nrcStates = [];
                let nrcTownships = [];
                let nrcTypes = [];
                const handleNrcState = (selectedVal) => {
                    nrcTownships = [];
                    Object.entries(nrcList).map((item) => {
                        if (item[0] === selectedVal) {
                            item[1].map((val) => {
                                nrcTownships.push({
                                    value: val.long_district,
                                    //label: val.short_district_mm, // for mm label
                                    label: val.long_district // for en label
                                });
                            });
                        }
                    });
                    ddFn.populateDropdown(nrcTownships, 'value', '#nrc_township', false, oldData.nrc_township)
                };
                $('#nrc_state').on('change', function(e) {
                    handleNrcState($(this).val());
                })

                $('#role_id').on('change', function(e) {
                    let $nrcGrp = $('.nrc-group');
                    $('.nrc-group, .licence-no, .speciality-group, .business-group').hide();
                    if ($(this).val() == $('.nrc-group').attr('data-visible')) {
                        $('.nrc-group').show();
                    }

                    if ($(this).val() == $('.licence-no').attr('data-visible')) {
                        $('.licence-no').show();
                    }

                    if ($(this).val() == $('.speciality-group').attr('data-visible')) {
                        $('.speciality-group').show();
                    }
                    let visible = $('.business-group').attr('data-visible');
                    visible.split(',').map(item => {
                        if ($(this).val() == item.trim()) {
                            $('.business-group').show();
                        }
                    });

                });

                $('#nrc_no').on('keyup', function(e) {
                    let str = ddFn.limitStrLength($(this).val(), 6);
                    $(this).val(isNaN(str) ? '' : str);
                })
                if (nrcList.length > 0) {
                    nrcList = ddFn.groupBy(nrcList, "state_number_en");
                }
                // get list of nrc states
                for (const [key, value] of Object.entries(nrcList)) {
                    nrcStates.push({
                        value: key,
                        //label: value[0]['state_number_mm'], // mm label
                        label: key // for en label
                    });
                }

                // get list of nrc types
                for (const [key, value] of Object.entries(nrcTypeList)) {
                    nrcTypes.push({
                        value: key,
                        //label: value, // mm label
                        label: key, // en label
                    });
                }

                // populate state dropdown
                ddFn.populateDropdown(nrcStates, 'value', '#nrc_state', false, oldData.nrc_state);

                // if there is state value, re-populate township dropdown
                if (typeof oldData !== "undefined" && typeof oldData.nrc_state !== "undefined") {
                    handleNrcState(oldData.nrc_state);
                    $("#nrc_no").val(oldData.nrc_no);
                }

                if (typeof oldData !== "undefined" && typeof oldData.licence_no !== "undefined") {
                    $("#licence_no").val(oldData.licence_no);
                }
                // populate nrc type dropdown
                ddFn.populateDropdown(nrcTypes, 'value', '#nrc_type', false, oldData.nrc_type);

            });
        </script>
    @endcan
@endsection
