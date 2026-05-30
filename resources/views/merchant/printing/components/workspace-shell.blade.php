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
    x-data="printingWorkspace({
        module: @js($module->value),
        listUrl: @js(route($module->routeName())),
        validateUrl: @js(route('printing.aspect_ratio.validate')),
        previewUrl: @js(route('printing.preview.show')),
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
        },
    })"
>
    <div class="merchant-printing-workspace__panel">
        <div class="merchant-printing-workspace__list">
            @include('merchant.printing.components.list-pane', [
                'workspace' => $workspace,
            ])
        </div>

        <div class="merchant-printing-workspace__preview">
            @include('merchant.printing.components.preview-pane', [
                'workspace' => $workspace,
            ])
        </div>
    </div>
</div>
