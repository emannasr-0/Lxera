@extends('admin.layouts.app')

@push('styles_top')
@endpush

@php
    $item = $group->bundle ?? $group->webinar;
    $itemName = $group->bundle_id ? 'bundle_id' : 'webinar_id';
    // dd($item);


@endphp

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ !empty($group) ? trans('admin/main.edit') : '' }} {{( $group->bundle_id) ? 'مجموعة برنامج ' :  'مجموعة دورة' }}
        </h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{( $group->bundle_id) ? 'مجموعة برنامج ' :  'مجموعة دورة' }}</div>
            </div>
        </div>


        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="tab-content" id="myTabContent2">

                                <form action="{{ getAdminPanelUrl() }}/courses/groups/{{ $group->id . '/update' }}"
                                    method="Post">
                                    @csrf()
                                    @method('PUT')
                                    {{-- name --}}
                                    <div class="form-group">
                                        <label>اسم المجموعة</label>
                                        <input type="text" name="name" readonly
                                            class="form-control  @error('name') is-invalid @enderror"
                                            value="{{ !empty($group) ? $group->name : old('name') }}" />
                                        @error('name')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- item --}}
                                    <div class="form-group">
                                        <label>
                                            {{( $group->bundle_id) ? 'اسم البرنامج ' :  'اسم الدورة' }}
                                            </label>
                                        <input type="text" name="{{ $itemName }}" hidden
                                            class="form-control  @error("$itemName") is-invalid @enderror"
                                            value="{{ !empty($group) ? $item->id : old("$itemName") }}" />

                                        <input type="text" name="item_name" readonly
                                            class="form-control  @error('item_name') is-invalid @enderror"
                                            value="{{ !empty($group) ?$item->title : old('item_name') }}" />

                                        @error('{{ $itemName }}')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- student capacity --}}
                                    <div class="form-group">
                                        <label>سعةالمجموعة</label>
                                        <input type="number" min="0" name="capacity"
                                            class="form-control  @error('capacity') is-invalid @enderror"
                                            value="{{ !empty($group) ? $group->capacity : old('capacity') }}" />
                                        @error('capacity')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- start date --}}
                                    <div class="form-group">
                                        <label>تاريخ البدأ</label>
                                        <input type="date" name="start_date"
                                            class="form-control  @error('start_date') is-invalid @enderror"
                                            value="{{ !empty($group) ? $group->start_date : old('start_date') }}" />
                                        @error('start_date')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>



                                    <div class="form-group">
                                        <label>تاريخ الانتهاء</label>
                                        <input type="date" name="end_date"
                                            class="form-control  @error('start_date') is-invalid @enderror"
                                            value="{{ !empty($group) ? $group->end_date : old('end_date') }}" />
                                        @error('end_date')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- status --}}
                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="status" value="inactive">
                                            <input type="checkbox" name="status" id="preloadingSwitch" value="active"
                                                {{ (!empty($group) and $group->status == 'active') ? 'checked="checked"' : '' }}
                                                class="custom-switch-input" />
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer"
                                                for="preloadingSwitch">{{ trans('admin/main.active') }}</label>
                                        </label>
                                    </div>

                                    {{-- submit --}}
                                    <div class=" mt-4">
                                        <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                    </div>
                                </form>
                                {{-- @include('admin.users.groups.tabs.general')

                                @if (!empty($group))
                                    @can('admin_update_group_registration_package')
                                        @include('admin.users.groups.tabs.registration_package')
                                    @endcan
                                @endif --}}
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
