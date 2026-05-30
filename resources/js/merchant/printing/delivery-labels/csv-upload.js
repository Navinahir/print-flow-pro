import { showToast } from '../../toast.js';
import { confirmDialog } from '../../sweetalert.js';

/**
 * @param {string} csvUploadUrl
 * @param {FormData} formData
 * @returns {Promise<object>}
 */
export async function uploadDeliveryLabelCsv(csvUploadUrl, formData) {
    const client = window.MerchantAjax?.client;

    if (! client) {
        throw new Error('ajax_unavailable');
    }

    const response = await client.post(csvUploadUrl, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });

    return response.data;
}

/**
 * @param {object} labels
 * @param {string} message
 */
export function showCsvImportSuccess(labels, message) {
    showToast(message, 'success');
}

/**
 * @param {object} labels
 */
export async function confirmCsvUpload(labels) {
    if (! window.MerchantAlert?.confirm) {
        return { isConfirmed: true };
    }

    return confirmDialog({
        icon: 'question',
        title: labels.csvConfirmTitle,
        text: labels.csvConfirmMessage,
        confirmButtonColor: '#d97706',
    });
}
