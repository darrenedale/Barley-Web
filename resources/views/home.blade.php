@extends("layouts.main")

@push("scripts")
    <script type="module" src="js/HomePage.js"></script>
    <script type="module" src="js/BarcodeDetailsWidget.js"></script>
@endpush

@section("main-content")
    <div class="barcode-details">
        <barcode-details-widget
            class="home-page-barcode-details"
            barcode-types="{{ isset($barcodeTypes) ? implode(" ", $barcodeTypes) : "code128 itf code11 code39" }}"
            barcode-type="{{ $barcodeType ?? "code128" }}"
            placeholder="{{ $barcodeDataPlaceholder ?? "Enter the data to encode..." }}"
        ></barcode-details-widget>
        <button class="generate-barcode">{{ $generateButtonLabel ?? "Generate Barcode" }}</button>
    </div>
    <div class="barcode-image-container">
        <img class="barcode-image" src="images/barcode-placeholder.svg" alt="{{ $barcodeImageAlt ?? "Barcode" }}" />
    </div>
@endsection
