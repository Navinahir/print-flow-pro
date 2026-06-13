<style>

    .order-slot-wrap {

        font-family: sans-serif;

        font-size: {{ $fontSize }}pt;

        color: #111;

        line-height: 1.35;

    }



    .order-slot-wrap--top {

        height: {{ $slotHeightMm }}mm;

        max-height: {{ $slotHeightMm }}mm;

        overflow: hidden;

        padding-top: {{ $slotPaddingMm }}mm;

        box-sizing: border-box;

    }



    .order-slot-wrap--full {

        padding-top: {{ $slotPaddingMm }}mm;

    }



    .order-slot-wrap--bottom {

        height: {{ $slotHeightMm }}mm;

        max-height: {{ $slotHeightMm }}mm;

        overflow: hidden;

        padding-top: {{ $slotPaddingMm }}mm;

        box-sizing: border-box;

    }



    .order-heading-table {

        width: 100%;

        border-collapse: collapse;

        margin: 0 0 3px;

    }



    .order-heading-title {

        font-size: {{ $headingFontSize }}pt;

        font-weight: bold;

        line-height: 1.25;

    }



    .order-heading-number {

        font-size: {{ $headingFontSize }}pt;

        font-weight: bold;

        line-height: 1.25;

        text-align: right;

    }



    .buyer-note-label {

        margin: 0 0 2px;

        font-size: {{ $fontSize }}pt;

        line-height: 1.35;

    }



    .buyer-note-text {

        margin: 0 0 5px;

        font-size: {{ $fontSize }}pt;

        line-height: 1.35;

        word-break: break-word;

    }



    .order-items-table {

        width: 100%;

        border-collapse: collapse;

        margin-top: 3px;

        font-size: {{ $tableFontSize }}pt;

        table-layout: fixed;

    }



    .order-items-table th,

    .order-items-table td {

        border: 1px solid #222;

        padding: 4px 3px;

        vertical-align: top;

        word-break: break-word;

        line-height: 1.3;

    }



    .order-items-table th {

        background: #f0f0f0;

        font-weight: bold;

        text-align: left;

    }



    .order-items-table .center {

        text-align: center;

    }



    .order-items-table .qty-total {

        white-space: nowrap;

        text-align: right;

    }



    .order-items-table .product-name-cell {

        font-size: {{ max(9, $tableFontSize - 1) }}pt;

    }

</style>

