<table class="order-heading-table">
    <tr>
        <td class="order-heading-title" style="width: 28%;">{{ $sectionTitle }}</td>
        <td class="order-heading-number" style="width: 72%;">
            {{ $orderNumberLabel }}: {{ $order->orderSn }}
            {{ $packageLabel }} {{ $order->packageNumber }}
        </td>
    </tr>
</table>

<p class="buyer-note-label">{{ $buyerNoteLabel }}:</p>
@if ($order->buyerNote !== '')
    <p class="buyer-note-text">{{ $order->buyerNote }}</p>
@endif

<table class="order-items-table">
    <thead>
        <tr>
            <th class="center" style="width: 4%;">#</th>
            <th style="width: 11%;">{{ $columns['main_sku'] }}</th>
            <th style="width: 34%;">{{ $columns['product_name'] }}</th>
            <th style="width: 11%;">{{ $columns['variant_sku'] }}</th>
            <th style="width: 17%;">{{ $columns['variant_name'] }}</th>
            <th class="center" style="width: 8%;">{{ $columns['quantity'] }}</th>
            <th class="center" style="width: 14%;">{{ $columns['total'] }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->lineItems as $item)
            <tr>
                <td class="center">{{ $item->lineNumber }}</td>
                <td>{{ $item->mainSku }}</td>
                <td class="product-name-cell">{{ $item->productName }}</td>
                <td>{{ $item->variantSku }}</td>
                <td>{{ $item->variantName }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="qty-total">{{ $item->lineTotal > 0 ? $item->lineTotal : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
