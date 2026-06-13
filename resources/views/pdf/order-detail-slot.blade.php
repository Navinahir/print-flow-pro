<div class="order-slot-wrap order-slot-wrap--{{ $slotRole }}">
    @if ($order !== null)
        @include('pdf.order-detail-slot-content', [
            'order' => $order,
            'sectionTitle' => $sectionTitle,
            'orderNumberLabel' => $orderNumberLabel,
            'packageLabel' => $packageLabel,
            'buyerNoteLabel' => $buyerNoteLabel,
            'columns' => $columns,
        ])
    @endif
</div>
