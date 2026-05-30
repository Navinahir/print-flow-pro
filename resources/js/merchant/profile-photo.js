import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import Alpine from 'alpinejs';

const MAX_CROP_SOURCE_DIMENSION = 2048;

/**
 * Downscale large images before cropping so Cropper.js stays responsive.
 *
 * @param {string} dataUrl
 * @returns {Promise<string>}
 */
function normalizeCropSource(dataUrl) {
    return new Promise((resolve) => {
        const image = new Image();

        image.onload = () => {
            const largestSide = Math.max(image.width, image.height);

            if (largestSide <= MAX_CROP_SOURCE_DIMENSION) {
                resolve(dataUrl);

                return;
            }

            const scale = MAX_CROP_SOURCE_DIMENSION / largestSide;
            const width = Math.round(image.width * scale);
            const height = Math.round(image.height * scale);
            const canvas = document.createElement('canvas');

            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d');

            if (! context) {
                resolve(dataUrl);

                return;
            }

            context.drawImage(image, 0, 0, width, height);
            resolve(canvas.toDataURL('image/jpeg', 0.92));
        };

        image.onerror = () => resolve(dataUrl);
        image.src = dataUrl;
    });
}

export function registerProfilePhotoUpload() {
    Alpine.data('profilePhotoUpload', () => ({
        cropperOpen: false,
        cropper: null,
        uploading: false,
        resizeHandler: null,

        init() {
            this.$watch('cropperOpen', (open) => {
                document.body.classList.toggle('merchant-crop-modal-open', open);
            });
        },

        handleFileSelect(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = async (loadEvent) => {
                const rawSource = loadEvent.target?.result ?? '';
                const source = await normalizeCropSource(String(rawSource));

                this.cropperOpen = true;

                await this.$nextTick();

                const image = this.$refs.cropImage;

                if (! image) {
                    return;
                }

                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                image.src = source;

                image.onload = async () => {
                    await this.$nextTick();
                    requestAnimationFrame(() => {
                        this.fitImageToStage();
                        this.initCropper();
                    });
                };
            };

            reader.readAsDataURL(file);
            event.target.value = '';
        },

        fitImageToStage() {
            const image = this.$refs.cropImage;
            const stage = image?.parentElement;

            if (! image || ! stage || ! image.naturalWidth) {
                return;
            }

            const maxWidth = stage.clientWidth;
            const maxHeight = stage.clientHeight;
            const scale = Math.min(
                maxWidth / image.naturalWidth,
                maxHeight / image.naturalHeight,
                1,
            );

            image.style.width = `${Math.round(image.naturalWidth * scale)}px`;
            image.style.height = `${Math.round(image.naturalHeight * scale)}px`;
        },

        initCropper() {
            if (! this.$refs.cropImage) {
                return;
            }

            if (this.cropper) {
                this.cropper.destroy();
            }

            this.cropper = new Cropper(this.$refs.cropImage, {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 0.9,
                responsive: true,
                restore: false,
                checkOrientation: true,
                background: false,
                modal: true,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                ready: () => {
                    this.cropper?.resize();
                },
            });

            if (this.resizeHandler) {
                window.removeEventListener('resize', this.resizeHandler);
            }

            this.resizeHandler = () => {
                this.cropper?.resize();
            };

            window.addEventListener('resize', this.resizeHandler);
        },

        cancelCrop() {
            this.closeCropper();
        },

        closeCropper() {
            this.cropperOpen = false;

            if (this.resizeHandler) {
                window.removeEventListener('resize', this.resizeHandler);
                this.resizeHandler = null;
            }

            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }

            if (this.$refs.cropImage) {
                this.$refs.cropImage.onload = null;
                this.$refs.cropImage.removeAttribute('style');
                this.$refs.cropImage.src = '';
            }
        },

        saveCrop() {
            if (! this.cropper || this.uploading) {
                return;
            }

            this.uploading = true;

            const canvas = this.cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (! canvas) {
                this.uploading = false;

                return;
            }

            canvas.toBlob((blob) => {
                if (! blob || ! this.$refs.uploadForm || ! this.$refs.photoInput) {
                    this.uploading = false;
                    this.closeCropper();

                    return;
                }

                const file = new File([blob], 'profile.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                this.$refs.photoInput.files = transfer.files;
                this.$refs.uploadForm.submit();
            }, 'image/jpeg', 0.92);
        },

        removePhoto() {
            if (this.uploading || ! this.$refs.removeForm) {
                return;
            }

            this.uploading = true;
            this.$refs.removeForm.submit();
        },
    }));
}
