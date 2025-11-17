@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class=" js-font-resize section">
        <div class=" js-font-resize section-header">
            <h1>{{!empty($user) ?trans('/admin/main.edit'): trans('admin/main.new') }} {{ trans('admin/main.user') }}</h1>
            <div class=" js-font-resize section-header-breadcrumb">
                <div class=" js-font-resize breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item"><a>{{ trans('admin/main.users') }}</a>
                </div>
                <div class=" js-font-resize breadcrumb-item">{{!empty($user) ?trans('/admin/main.edit'): trans('admin/main.new') }}</div>
            </div>
        </div>

        <div class=" js-font-resize section-body">
            <div class=" js-font-resize row">
                <div class=" js-font-resize col-12 ">
                    <div class=" js-font-resize card">
                        <div class=" js-font-resize card-body">
                            <div class=" js-font-resize row">
                                <div class=" js-font-resize col-12 col-md-6 col-lg-6">
                                    <form action="{{ getAdminPanelUrl() }}/users/store" method="Post">
                                        {{ csrf_field() }}

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('/admin/main.full_name') }}</label>
                                            <input type="text" name="full_name"
                                                   class=" js-font-resize form-control  @error('full_name') is-invalid @enderror"
                                                   value="{{ old('full_name') }}"
                                                   placeholder="{{ trans('admin/main.create_field_full_name_placeholder') }}"/>
                                            @error('full_name')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label for="username">{{ trans('auth.email_or_mobile') }}:</label>
                                            <input name="username" type="text" class=" js-font-resize form-control @error('email') is-invalid @enderror @error('mobile') is-invalid @enderror" id="username" value="{{ old('email') }}" aria-describedby="emailHelp">
                                            @error('email')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                            @error('mobile')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label class=" js-font-resize input-label">{{ trans('admin/main.password') }}</label>
                                            <div class=" js-font-resize input-group">
                                                <div class=" js-font-resize input-group-prepend">
                                                    <span type="button" class=" js-font-resize input-group-text">
                                                        <i class=" js-font-resize fa fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" name="password"
                                                       class=" js-font-resize form-control @error('password') is-invalid @enderror"/>
                                                @error('password')
                                                <div class=" js-font-resize invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('/admin/main.role_name') }}</label>
                                            <select class=" js-font-resize form-control select2 @error('role_id') is-invalid @enderror" id="roleId" name="role_id">
                                                <option disabled selected>{{ trans('admin/main.select_role') }}</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" {{ old('role_id') === $role->id ? 'selected' :''}}>{{ $role->name }} - {{ $role->caption }}</option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize form-group" id="groupSelect">
                                            <label class=" js-font-resize input-label d-block">{{ trans('admin/main.group') }}</label>
                                            <select name="group_id" class=" js-font-resize form-control select2 @error('group_id') is-invalid @enderror">
                                                <option value="" selected disabled></option>

                                                @foreach($userGroups as $userGroup)
                                                    <option value="{{ $userGroup->id }}" @if(!empty($notification) and !empty($notification->group) and $notification->group->id == $userGroup->id) selected @endif>{{ $userGroup->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class=" js-font-resize invalid-feedback">@error('group_id') {{ $message }} @enderror</div>
                                        </div>

                                        <div class=" js-font-resize form-group">
                                            <label>{{ trans('/admin/main.status') }}</label>
                                            <select class=" js-font-resize form-control @error('status') is-invalid @enderror" id="status" name="status">
                                                <option disabled selected>{{ trans('admin/main.select_status') }}</option>
                                                @foreach (\App\User::$statuses as $status)
                                                    <option
                                                        value="{{ $status }}" {{ old('status') === $status ? 'selected' :''}}>{{  $status }}</option>
                                                @endforeach
                                            </select>
                                            @error('status')
                                            <div class=" js-font-resize invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class=" js-font-resize text-right mt-4">
                                            <button class=" js-font-resize btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush

