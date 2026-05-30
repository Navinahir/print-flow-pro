@props([
    'workspace',
])

@php
    /** @var \App\DTOs\Merchant\Printing\PrintingWorkspaceViewData $workspace */
    $module = $workspace->module;
    $listItems = $workspace->listItems;
@endphp

<div
    id="merchant-printing-workspace"
    class="merchant-printing-workspace"
    x-data="deliveryLabelsWorkspace({
        module: @js($module->value),
        listUrl: @js(route($module->routeName())),
        validateUrl: @js(route('printing.aspect_ratio.validate')),
        previewUrl: @js(route('printing.preview.show')),
        csvUploadUrl: @js(route('printing.delivery_labels.csv.store')),
        items: @js($workspace->listItemsAsArrays()),
        selectedId: @js($workspace->selectedItemId),
        labels: {
            listEmpty: @js(__('merchant.printing.workspace.list_empty')),
            previewPlaceholder: @js(__('merchant.preview.empty.title')),
            previewEmptyTitle: @js(__('merchant.preview.empty.selected_fallback')),
            loading: @js(__('merchant.components.loading_state.default_message')),
            aspectValid: @js(__('merchant.preview.aspect_ratio.valid')),
            aspectInvalid: @js(__('merchant.preview.aspect_ratio.invalid', ['deviation' => ':deviation', 'tolerance' => ':tolerance'])),
            sweetalertTitle: @js(__('merchant.preview.aspect_ratio.sweetalert_title')),
            sweetalertMessage: @js(__('merchant.preview.aspect_ratio.sweetalert_message')),
            remarksHeading: @js(__('merchant.delivery_labels.preview.remarks_heading')),
            shrunkHint: @js(__('merchant.delivery_labels.preview.shrunk_hint')),
            csvConfirmTitle: @js(__('merchant.delivery_labels.csv.confirm_title')),
            csvConfirmMessage: @js(__('merchant.delivery_labels.csv.confirm_message')),
        },
        typography: {
            defaultFontSizePx: @js(\App\Services\Merchant\Printing\DeliveryLabels\CourierAddressTypographyService::DEFAULT_FONT_SIZE_PX),
            minFontSizePx: @js(\App\Services\Merchant\Printing\DeliveryLabels\CourierAddressTypographyService::MIN_FONT_SIZE_PX),
            shrinkThresholdChars: @js(\App\Services\Merchant\Printing\DeliveryLabels\CourierAddressTypographyService::SHRINK_THRESHOLD_CHARS),
        },
    })"
>
    <div class="merchant-printing-workspace__panel">
        <div class="merchant-printing-workspace__list">
            @include('merchant.printing.delivery-labels.components.list-pane', [
                'workspace' => $workspace,
            ])
        </div>

        <div class="merchant-printing-workspace__preview">
            @include('merchant.printing.delivery-labels.components.preview-pane')
        </div>
    </div>
</div>
