<div class=" js-font-resize col-12 col-md-3 mt-3">
    <div class=" js-font-resize course-statistic-cards-shadow pt-2 px-2 pb-3 rounded-sm bg-white">
        <span class=" js-font-resize d-block font-16 font-weight-bold text-dark">{{ $cardTitle }}</span>
        <div class=" js-font-resize mt-3 statistic-pie-charts">
            <canvas id="{{ $cardId }}" height="197"></canvas>
        </div>

        <div class=" js-font-resize mt-3">
            <div class=" js-font-resize d-flex align-items-center">
                <span class=" js-font-resize cart-label-color rounded-circle bg-primary mr-2"></span>
                <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ $cardPrimaryLabel }}</span>
            </div>
            <div class=" js-font-resize d-flex align-items-center">
                <span class=" js-font-resize cart-label-color rounded-circle bg-secondary mr-2"></span>
                <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ $cardSecondaryLabel }}</span>
            </div>
            <div class=" js-font-resize d-flex align-items-center">
                <span class=" js-font-resize cart-label-color rounded-circle bg-warning mr-2"></span>
                <span class=" js-font-resize font-14 font-weight-500 text-gray">{{ $cardWarningLabel }}</span>
            </div>
        </div>
    </div>
</div>
