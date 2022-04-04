@extends("layouts.main")

@push("scripts")
    <script type="module" src="js/BarcodeDetailsWidget.js"></script>
@endpush

@section("main-content")
    <barcode-details-widget placeholder="Enter the data to encode..." barcode-types="code11 code39 code128 itf"></barcode-details-widget>
{{--    <div class="barcode-entry-form">--}}
{{--        <select name="type">--}}
{{--            <option value="code11">Code 11</option>--}}
{{--            <option value="code39">Code 39</option>--}}
{{--            <option value="code128">Code 128</option>--}}
{{--            <option value="itf">ITF</option>--}}
{{--        </select>--}}
{{--        <input type="text" name="data" placeholder="Enter the data to encode..." />--}}
{{--        <button value="generate">Generate</button>--}}
{{--    </div>--}}
    <div class="barcode-image">
        <img class="barcode-image" src="images/barcode-placeholder.png" alt="Barcode" />
    </div>
@endsection
