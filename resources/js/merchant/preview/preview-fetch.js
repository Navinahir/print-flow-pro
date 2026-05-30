/**
 * @param {string} previewUrl
 * @param {string} module
 * @param {string} itemId
 * @returns {Promise<object|null>}
 */
export async function fetchPrintingPreview(previewUrl, module, itemId) {
    const client = window.MerchantAjax?.client;

    if (! client || previewUrl === '') {
        return null;
    }

    const response = await client.post(previewUrl, {
        module,
        item_id: itemId,
    });

    return response.data?.preview ?? null;
}
