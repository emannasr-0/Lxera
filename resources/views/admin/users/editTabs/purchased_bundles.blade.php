<div class=" js-font-resize tab-pane mt-3 fade" id="purchased_bundles" role="tabpanel" aria-labelledby="purchased_bundles-tab">
    <div class=" js-font-resize row">

        @can('admin_enrollment_add_student_to_items')
            <div class=" js-font-resize col-12 col-md-6">
                <h5 class=" js-font-resize section-title after-line">{{ trans('update.add_student_to_bundle') }}</h5>

                <form action="{{ getAdminPanelUrl() }}/enrollments/store" method="Post">

                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                    <div class=" js-font-resize form-group">
                        <label class=" js-font-resize input-label">{{trans('update.bundle')}}</label>
                        <select name="bundle_id" class=" js-font-resize form-control search-bundle-select2"
                                data-placeholder="{{ trans('update.search_bundle') }}">

                        </select>
                        <div class=" js-font-resize invalid-feedback"></div>
                    </div>

                    <div class=" js-font-resize  mt-4">
                        <button type="button" class=" js-font-resize js-save-manual-add btn btn-primary">{{ trans('admin/main.submit') }}</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class=" js-font-resize col-12">
            <div class=" js-font-resize mt-5">
                <h5 class=" js-font-resize section-title after-line">{{ trans('update.manual_added_bundles') }}</h5>

                <div class=" js-font-resize table-responsive mt-3">
                    <table class=" js-font-resize table table-striped table-md">
                        <tr>
                            <th>{{trans('update.bundle')}}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('update.added_date') }}</th>
                            <th class=" js-font-resize text-right">{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($manualAddedBundles))
                            @foreach($manualAddedBundles as $manualAddedBundle)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($manualAddedBundle->bundle) ? $manualAddedBundle->bundle->getUrl() : '#1' }}" target="_blank" class=" js-font-resize ">{{ !empty($manualAddedBundle->bundle) ? $manualAddedBundle->bundle->title : trans('update.deleted_item') }}</a>
                                    </td>

                                    <td>
                                        @if(!empty($manualAddedBundle->bundle))
                                            {{ trans('admin/main.'.$manualAddedBundle->bundle->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($manualAddedBundle->bundle))
                                            {{ !empty($manualAddedBundle->bundle->price) ? handlePrice($manualAddedBundle->bundle->price) : '-' }}
                                        @else
                                            {{ !empty($manualAddedBundle->amount) ? handlePrice($manualAddedBundle->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($manualAddedBundle->bundle))
                                            <p>{{ $manualAddedBundle->bundle->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $manualAddedBundle->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class=" js-font-resize text-center">{{ dateTimeFormat($manualAddedBundle->created_at,'j M Y | H:i') }}</td>
                                    <td class=" js-font-resize text-right">
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $manualAddedBundle->id .'/block-access',
                                                    'tooltip' => trans('update.block_access'),
                                                    'btnIcon' => 'fa-times-circle'
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class=" js-font-resize font-12 text-gray mt-1 mb-0">{{ trans('update.manual_add_hint') }}</p>
                </div>
            </div>
        </div>


        <div class=" js-font-resize col-12">
            <div class=" js-font-resize mt-5">
                <h5 class=" js-font-resize section-title after-line">{{ trans('update.manual_disabled_bundles') }}</h5>

                <div class=" js-font-resize table-responsive mt-3">
                    <table class=" js-font-resize table table-striped table-md">
                        <tr>
                            <th>{{trans('update.bundle')}}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class=" js-font-resize text-right">{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($manualDisabledBundles))
                            @foreach($manualDisabledBundles as $manualDisabledBundle)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($manualDisabledBundle->bundle) ? $manualDisabledBundle->bundle->getUrl() : '#1' }}" target="_blank" class=" js-font-resize ">{{ !empty($manualDisabledBundle->bundle) ? $manualDisabledBundle->bundle->title : trans('update.deleted_item') }}</a>
                                    </td>

                                    <td>
                                        @if(!empty($manualDisabledBundle->bundle))
                                            {{ trans('admin/main.'.$manualDisabledBundle->bundle->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($manualDisabledBundle->bundle))
                                            {{ !empty($manualDisabledBundle->bundle->price) ? handlePrice($manualDisabledBundle->bundle->price) : '-' }}
                                        @else
                                            {{ !empty($manualDisabledBundle->amount) ? handlePrice($manualDisabledBundle->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($manualDisabledBundle->bundle))
                                            <p>{{ $manualDisabledBundle->bundle->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $manualDisabledBundle->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class=" js-font-resize text-right">
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $manualDisabledBundle->id .'/enable-access',
                                                    'tooltip' => trans('update.enable-student-access'),
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class=" js-font-resize font-12 text-gray mt-1 mb-0">{{ trans('update.manual_remove_hint') }}</p>
                </div>
            </div>
        </div>


        <div class=" js-font-resize col-12">
            <div class=" js-font-resize mt-5">
                <h5 class=" js-font-resize section-title after-line">{{ trans('panel.purchased') }}</h5>

                <div class=" js-font-resize table-responsive mt-3">
                    <table class=" js-font-resize table table-striped table-md">
                        <tr>
                            <th>{{trans('update.bundle')}}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class=" js-font-resize text-center">{{ trans('panel.purchase_date') }}</th>
                            <th>{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($purchasedBundles))
                            @foreach($purchasedBundles as $purchasedBundle)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($purchasedBundle->bundle) ? $purchasedBundle->bundle->getUrl() : '#1' }}" target="_blank" class=" js-font-resize ">{{ !empty($purchasedBundle->bundle) ? $purchasedBundle->bundle->title : trans('update.deleted_item') }}</a>
                                    </td>

                                    <td>
                                        @if(!empty($purchasedBundle->bundle))
                                            {{ trans('admin/main.'.$purchasedBundle->bundle->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($purchasedBundle->bundle))
                                            {{ !empty($purchasedBundle->bundle->price) ? handlePrice($purchasedBundle->bundle->price) : '-' }}
                                        @else
                                            {{ !empty($purchasedBundle->amount) ? handlePrice($purchasedBundle->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($purchasedBundle->bundle))
                                            <p>{{ $purchasedBundle->bundle->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $purchasedBundle->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class=" js-font-resize text-center">{{ dateTimeFormat($purchasedBundle->created_at,'j M Y | H:i') }}</td>
                                    <td class=" js-font-resize text-right">
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $purchasedBundle->id .'/block-access',
                                                    'tooltip' => trans('update.block_access'),
                                                    'btnIcon' => 'fa-times-circle'
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class=" js-font-resize font-12 text-gray mt-1 mb-0">{{ trans('update.purchased_hint') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
