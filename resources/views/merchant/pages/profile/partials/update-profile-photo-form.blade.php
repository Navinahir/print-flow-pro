<section
    id="merchant-profile-photo-root"
    class="max-w-xl space-y-6"
    x-data="profilePhotoUpload"
>
    <header>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.profile.photo.title') }}</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('merchant.profile.photo.description') }}</p>
    </header>

    <div class="merchant-profile-photo">
        <x-merchant.user-avatar :user="$user" size="xl" />

        <div class="space-y-3">
            <div class="merchant-profile-photo__actions">
                <label class="merchant-btn-secondary cursor-pointer">
                    <span>{{ __('merchant.profile.photo.upload') }}</span>
                    <input
                        type="file"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/webp"
                        x-on:change="handleFileSelect($event)"
                        x-bind:disabled="uploading"
                    />
                </label>

                @if (\App\Support\UserAvatar::hasPhoto($user))
                    <button
                        type="button"
                        class="merchant-btn-secondary text-red-600 hover:text-red-700 dark:text-red-400"
                        x-on:click="removePhoto()"
                        x-bind:disabled="uploading"
                    >
                        {{ __('merchant.profile.photo.remove') }}
                    </button>
                @endif
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ __('merchant.profile.photo.hint') }}
            </p>

            @if (session('status') === 'profile-photo-updated')
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">{{ __('merchant.profile.photo.updated') }}</p>
            @endif

            @if (session('status') === 'profile-photo-removed')
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">{{ __('merchant.profile.photo.removed') }}</p>
            @endif
        </div>
    </div>

    <form
        x-ref="uploadForm"
        method="POST"
        action="{{ route('profile.photo.update') }}"
        enctype="multipart/form-data"
        class="hidden"
    >
        @csrf
        <input type="file" name="photo" x-ref="photoInput" accept="image/jpeg,image/png,image/webp">
    </form>

    <form
        x-ref="removeForm"
        method="POST"
        action="{{ route('profile.photo.destroy') }}"
        class="hidden"
    >
        @csrf
        @method('delete')
    </form>

    <div
        class="merchant-profile-photo-cropper"
        x-show="cropperOpen"
        x-cloak
        x-on:keydown.escape.window="cancelCrop()"
        x-on:click.self="cancelCrop()"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('merchant.profile.photo.crop_title') }}"
    >
        <div class="merchant-profile-photo-cropper__dialog" x-on:click.stop>
            <div class="merchant-profile-photo-cropper__header">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('merchant.profile.photo.crop_title') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('merchant.profile.photo.crop_description') }}</p>
            </div>

            <div class="merchant-profile-photo-cropper__body">
                <div class="merchant-profile-photo-cropper__stage">
                    <img x-ref="cropImage" alt="" />
                </div>
            </div>

            <div class="merchant-profile-photo-cropper__footer">
                <button type="button" class="merchant-btn-secondary" x-on:click="cancelCrop()" x-bind:disabled="uploading">
                    {{ __('merchant.profile.photo.cancel') }}
                </button>
                <button type="button" class="merchant-btn-primary" x-on:click="saveCrop()" x-bind:disabled="uploading">
                    <span x-show="! uploading">{{ __('merchant.profile.photo.save') }}</span>
                    <span x-show="uploading" x-cloak>{{ __('merchant.profile.photo.saving') }}</span>
                </button>
            </div>
        </div>
    </div>
</section>
