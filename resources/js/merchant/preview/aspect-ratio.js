import { PREVIEW_ASPECT_RATIO } from './constants.js';

export const DEFAULT_ASPECT_TOLERANCE_PERCENT = 10;

/**
 * @param {number} actualRatio
 * @param {number} [targetRatio=PREVIEW_ASPECT_RATIO]
 * @returns {number}
 */
export function calculateDeviationPercent(actualRatio, targetRatio = PREVIEW_ASPECT_RATIO) {
    if (actualRatio <= 0 || targetRatio <= 0) {
        return 100;
    }

    return Math.abs(actualRatio - targetRatio) / targetRatio * 100;
}

/**
 * @param {number} width
 * @param {number} height
 * @param {number} [tolerancePercent=DEFAULT_ASPECT_TOLERANCE_PERCENT]
 * @returns {{ valid: boolean, deviation_percent: number, actual_ratio: number, width: number, height: number, target_ratio: number, tolerance_percent: number }}
 */
export function validateDimensionsLocally(width, height, tolerancePercent = DEFAULT_ASPECT_TOLERANCE_PERCENT) {
    const actualRatio = width / height;
    const deviationPercent = calculateDeviationPercent(actualRatio);
    const valid = deviationPercent <= tolerancePercent;

    return {
        valid,
        deviation_percent: Math.round(deviationPercent * 100) / 100,
        actual_ratio: Math.round(actualRatio * 10000) / 10000,
        width,
        height,
        target_ratio: PREVIEW_ASPECT_RATIO,
        tolerance_percent: tolerancePercent,
    };
}

/**
 * @param {File} file
 * @returns {Promise<{ width: number, height: number }>}
 */
export function readImageDimensions(file) {
    return new Promise((resolve, reject) => {
        if (! file.type.startsWith('image/')) {
            reject(new Error('unsupported'));
            return;
        }

        const url = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            URL.revokeObjectURL(url);
            resolve({
                width: image.naturalWidth,
                height: image.naturalHeight,
            });
        };

        image.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('load_failed'));
        };

        image.src = url;
    });
}

/**
 * @param {object} labels
 * @param {object} validation
 */
export function showAspectRatioSweetAlert(labels, validation) {
    if (! window.MerchantAlert?.alert) {
        return Promise.resolve();
    }

    return window.MerchantAlert.alert({
        icon: 'warning',
        title: labels.sweetalertTitle,
        text: validation.message ?? labels.sweetalertMessage,
        confirmButtonColor: '#d97706',
    });
}

/**
 * @param {string} url
 * @param {{ width?: number, height?: number, file?: File }} payload
 * @returns {Promise<object>}
 */
export async function validateAspectRatioRemote(url, payload) {
    const client = window.MerchantAjax?.client;

    if (! client) {
        throw new Error('ajax_unavailable');
    }

    if (payload.file) {
        const formData = new FormData();
        formData.append('file', payload.file);

        const response = await client.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        return response.data;
    }

    const response = await client.post(url, {
        width: payload.width,
        height: payload.height,
    });

    return response.data;
}
