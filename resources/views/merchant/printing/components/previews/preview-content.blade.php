<div class="printing-preview-content merchant-preview-surface__inset" data-printing-preview-content>
    <template x-if="selectedPreview()?.type === 'order_details'">
        <div class="printing-preview-order-details">
            <div class="printing-preview-order-details__header">
                <p class="printing-preview-order-details__number" x-text="selectedPreview()?.order_number"></p>
                <span class="printing-preview-order-details__status" x-text="selectedPreview()?.status"></span>
            </div>
            <dl class="printing-preview-order-details__meta">
                <div>
                    <dt>{{ __('merchant.printing.preview.order_details.fields.customer') }}</dt>
                    <dd x-text="selectedPreview()?.customer_name"></dd>
                </div>
                <div>
                    <dt>{{ __('merchant.printing.preview.order_details.fields.date') }}</dt>
                    <dd x-text="selectedPreview()?.order_date"></dd>
                </div>
            </dl>
            <table class="printing-preview-order-details__table">
                <thead>
                    <tr>
                        <th>{{ __('merchant.printing.preview.order_details.fields.sku') }}</th>
                        <th>{{ __('merchant.printing.preview.order_details.fields.item') }}</th>
                        <th>{{ __('merchant.printing.preview.order_details.fields.qty') }}</th>
                        <th>{{ __('merchant.printing.preview.order_details.fields.price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(line, index) in selectedPreview()?.line_items ?? []" :key="index">
                        <tr>
                            <td x-text="line.sku"></td>
                            <td x-text="line.name"></td>
                            <td x-text="line.qty"></td>
                            <td x-text="line.price"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="printing-preview-order-details__summary">
                <div><span>{{ __('merchant.printing.preview.order_details.fields.subtotal') }}</span><span x-text="selectedPreview()?.summary?.subtotal"></span></div>
                <div><span>{{ __('merchant.printing.preview.order_details.fields.shipping') }}</span><span x-text="selectedPreview()?.summary?.shipping"></span></div>
                <div class="printing-preview-order-details__total"><span>{{ __('merchant.printing.preview.order_details.fields.total') }}</span><span x-text="selectedPreview()?.summary?.total"></span></div>
            </div>
            <p class="printing-preview-order-details__notes" x-show="Boolean(selectedPreview()?.notes)" x-text="selectedPreview()?.notes"></p>
        </div>
    </template>

    <template x-if="selectedPreview()?.type === 'logistics_labels'">
        <div class="printing-preview-logistics">
            <div class="printing-preview-logistics__barcode" aria-hidden="true">
                <span class="printing-preview-logistics__barcode-bars"></span>
                <span class="printing-preview-logistics__barcode-text" x-text="selectedPreview()?.tracking_number"></span>
            </div>
            <dl class="printing-preview-logistics__meta">
                <div>
                    <dt>{{ __('merchant.printing.preview.logistics_labels.fields.carrier') }}</dt>
                    <dd x-text="selectedPreview()?.carrier"></dd>
                </div>
                <div>
                    <dt>{{ __('merchant.printing.preview.logistics_labels.fields.tracking') }}</dt>
                    <dd x-text="selectedPreview()?.tracking_number"></dd>
                </div>
                <div>
                    <dt>{{ __('merchant.printing.preview.logistics_labels.fields.shipment_date') }}</dt>
                    <dd x-text="selectedPreview()?.shipment_date"></dd>
                </div>
                <div x-show="Boolean(selectedPreview()?.service_level)">
                    <dt>{{ __('merchant.printing.preview.logistics_labels.fields.service_level') }}</dt>
                    <dd x-text="selectedPreview()?.service_level"></dd>
                </div>
            </dl>
            <div class="printing-preview-logistics__recipient">
                <p class="printing-preview-logistics__recipient-name" x-text="selectedPreview()?.recipient_name"></p>
                <p class="printing-preview-logistics__recipient-address" x-text="selectedPreview()?.recipient_address"></p>
            </div>
            <p
                class="printing-preview-logistics__download"
                x-show="Boolean(selectedPreview()?.download_url)"
                x-cloak
            >
                <a
                    class="merchant-btn merchant-btn--secondary merchant-btn--sm"
                    :href="selectedPreview()?.download_url"
                    x-text="@js(__('merchant.print_jobs.actions.download'))"
                ></a>
            </p>
        </div>
    </template>

    <template x-if="selectedPreview()?.type === 'picking_list'">
        <div class="printing-preview-picking-list">
            <div class="printing-preview-picking-list__header">
                <p class="printing-preview-picking-list__reference" x-text="selectedPreview()?.list_reference"></p>
                <p class="printing-preview-picking-list__warehouse" x-text="selectedPreview()?.warehouse"></p>
                <p class="printing-preview-picking-list__date" x-text="selectedPreview()?.pick_date"></p>
            </div>
            <table class="printing-preview-picking-list__table">
                <thead>
                    <tr>
                        <th>{{ __('merchant.printing.preview.picking_list.fields.line') }}</th>
                        <th>{{ __('merchant.printing.preview.picking_list.fields.product_name') }}</th>
                        <th>{{ __('merchant.printing.preview.picking_list.fields.variant_name') }}</th>
                        <th>{{ __('merchant.printing.preview.picking_list.fields.order_sn') }}</th>
                        <th>{{ __('merchant.printing.preview.picking_list.fields.qty') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in selectedPreview()?.rows ?? []" :key="index">
                        <tr>
                            <td x-text="row.line_number"></td>
                            <td x-text="row.product_name"></td>
                            <td x-text="row.variant_name"></td>
                            <td x-text="row.order_sn"></td>
                            <td x-text="row.quantity"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="printing-preview-picking-list__total">
                {{ __('merchant.printing.preview.picking_list.fields.total_units') }}:
                <span x-text="selectedPreview()?.total_units"></span>
            </p>
        </div>
    </template>
</div>
