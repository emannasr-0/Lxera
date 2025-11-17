<div class=" js-font-resize product-show-files-tab mt-20">
    @if(!empty($product->files) and count($product->files) and $product->checkUserHasBought())
        @foreach($product->files as $productFile)
            <div class=" js-font-resize d-flex align-items-center justify-content-between p-15 p-lg-20 bg-white rounded-sm border border-gray200 {{ ($loop->iteration > 1) ? 'mt-15' : '' }}">
                <div class=" js-font-resize ">
                    <span class=" js-font-resize d-block font-16 font-weight-bold text-dark-blue">{{ $productFile->title }}</span>
                    <span class=" js-font-resize d-block font-12 text-gray">{{ $productFile->description }}</span>
                </div>

                <div class=" js-font-resize d-flex align-items-center ml-20">

                    @if($productFile->online_viewer)
                        <button type="button" data-href="{{ $productFile->getOnlineViewUrl() }}"  class=" js-font-resize js-online-show product-file-download-btn d-flex align-items-center justify-content-center text-white border-0 rounded-circle mr-15">
                            <i data-feather="eye" width="20" height="20" class=" js-font-resize "></i>
                        </button>
                    @endif

                    <a href="{{ $productFile->getDownloadUrl() }}" target="_blank" class=" js-font-resize product-file-download-btn d-flex align-items-center justify-content-center text-white rounded-circle">
                        <i data-feather="download" width="20" height="20" class=" js-font-resize "></i>
                    </a>
                </div>
            </div>
        @endforeach
    @endif
</div>
