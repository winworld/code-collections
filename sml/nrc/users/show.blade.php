@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.user.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.id') }}
                        </th>
                        <td>
                            {{ $user->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.name') }}
                        </th>
                        <td>
                            {{ $user->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.phone') }}
                        </th>
                        <td>
                            {{ $user->phone }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.phone_verified_at') }}
                        </th>
                        <td>
                            {{ $user->phone_verified_at }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.otp') }}
                        </th>
                        <td>
                            {{ $user->otp }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.email') }}
                        </th>
                        <td>
                            {{ $user->email }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.email_verified_at') }}
                        </th>
                        <td>
                            {{ $user->email_verified_at }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.role') }}
                        </th>
                        <td>
                            {{ $user->role->title ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.speciality') }}
                        </th>
                        <td>
                            @foreach($user->specialities as $key => $speciality)
                                <span class="label label-info">{{ $speciality->title }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.business') }}
                        </th>
                        <td>
                            @foreach($user->businesses as $key => $business)
                                <span class="label label-info">{{ $business->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.user.fields.is_active') }}
                        </th>
                        <td>
                            {{ App\Models\User::IS_ACTIVE_SELECT[$user->is_active] ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.users.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('global.relatedData') }}
    </div>
    <ul class="nav nav-tabs" role="tablist" id="relationship-tabs">
        <li class="nav-item">
            <a class="nav-link" href="#patient_bookings" role="tab" data-toggle="tab">
                {{ trans('cruds.booking.title') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#user_user_meta" role="tab" data-toggle="tab">
                {{ trans('cruds.userMeta.title') }}
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" role="tabpanel" id="patient_bookings">
            @includeIf('admin.users.relationships.patientBookings', ['bookings' => $user->patientBookings])
        </div>
        <div class="tab-pane" role="tabpanel" id="user_user_meta">
            @includeIf('admin.users.relationships.userUserMeta', ['userMeta' => $user->userUserMeta])
        </div>
    </div>
</div>

@endsection