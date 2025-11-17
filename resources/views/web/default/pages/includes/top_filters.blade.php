<div id="topFilters" class=" js-font-resize shadow-lg border border-gray300 rounded-sm p-10 p-md-20">
    <div class=" js-font-resize row align-items-center">
        <div class=" js-font-resize col-lg-3 d-flex align-items-center">
            <div class=" js-font-resize checkbox-button primary-selected">
                <input type="radio" name="card" id="gridView" value="grid" @if(empty(request()->get('card')) or request()->get('card') == 'grid') checked="checked" @endif>
                <label for="gridView" class=" js-font-resize bg-white border-0 mb-0">
                    <i data-feather="grid" width="25" height="25" class=" js-font-resize @if(empty(request()->get('card')) or request()->get('card') == 'grid') text-primary @endif"></i>
                </label>
            </div>

            <div class=" js-font-resize checkbox-button primary-selected ml-10">
                <input type="radio" name="card" id="listView" value="list" @if(!empty(request()->get('card')) and request()->get('card') == 'list') checked="checked" @endif>
                <label for="listView" class=" js-font-resize bg-white border-0 mb-0">
                    <i data-feather="list" width="25" height="25" class=" js-font-resize {{ (!empty(request()->get('card')) and request()->get('card') == 'list') ? 'text-primary' : '' }}"></i>
                </label>
            </div>
        </div>

        <div class=" js-font-resize col-lg-6 d-block d-md-flex align-items-center justify-content-end my-25 my-lg-0">
            <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="upcoming">{{ trans('panel.upcoming') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="upcoming" class=" js-font-resize custom-control-input" id="upcoming" @if(request()->get('upcoming', null) == 'on') checked="checked" @endif>
                    <label class=" js-font-resize custom-control-label" for="upcoming"></label>
                </div>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">
                <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="free">{{ trans('public.free') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="free" class=" js-font-resize custom-control-input" id="free" @if(request()->get('free', null) == 'on') checked="checked" @endif>
                    <label class=" js-font-resize custom-control-label" for="free"></label>
                </div>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center mx-0 mx-md-20 my-20 my-md-0">
                <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="discount">{{ trans('public.discount') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="discount" class=" js-font-resize custom-control-input" id="discount" @if(request()->get('discount', null) == 'on') checked="checked" @endif>
                    <label class=" js-font-resize custom-control-label" for="discount"></label>
                </div>
            </div>

            <div class=" js-font-resize d-flex align-items-center justify-content-between justify-content-md-center">
                <label class=" js-font-resize mb-0 mr-10 cursor-pointer" for="download">{{ trans('home.download') }}</label>
                <div class=" js-font-resize custom-control custom-switch">
                    <input type="checkbox" name="downloadable" class=" js-font-resize custom-control-input" id="download" @if(request()->get('downloadable', null) == 'on') checked="checked" @endif>
                    <label class=" js-font-resize custom-control-label" for="download"></label>
                </div>
            </div>
        </div>

        <div class=" js-font-resize col-lg-3 d-flex align-items-center">
            <select name="sort" class=" js-font-resize form-control font-14">
                <option disabled selected>{{ trans('public.sort_by') }}</option>
                <option value="">{{ trans('public.all') }}</option>
                <option value="newest" @if(request()->get('sort', null) == 'newest') selected="selected" @endif>{{ trans('public.newest') }}</option>
                <option value="expensive" @if(request()->get('sort', null) == 'expensive') selected="selected" @endif>{{ trans('public.expensive') }}</option>
                <option value="inexpensive" @if(request()->get('sort', null) == 'inexpensive') selected="selected" @endif>{{ trans('public.inexpensive') }}</option>
                <option value="bestsellers" @if(request()->get('sort', null) == 'bestsellers') selected="selected" @endif>{{ trans('public.bestsellers') }}</option>
                <option value="best_rates" @if(request()->get('sort', null) == 'best_rates') selected="selected" @endif>{{ trans('public.best_rates') }}</option>
            </select>
        </div>

    </div>
</div>
