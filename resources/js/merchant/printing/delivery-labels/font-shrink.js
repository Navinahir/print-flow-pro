export const DEFAULT_FONT_SIZE_PX = 18;
export const MIN_FONT_SIZE_PX = 14;
export const SHRINK_THRESHOLD_CHARS = 35;
export const MAX_SINGLE_LINE_CHARS = 42;

/**
 * @param {string} address
 * @returns {number}
 */
export function resolveFontSizePx(address) {
    const length = address.trim().length;

    if (length <= SHRINK_THRESHOLD_CHARS) {
        return DEFAULT_FONT_SIZE_PX;
    }

    const scaled = Math.floor(DEFAULT_FONT_SIZE_PX * SHRINK_THRESHOLD_CHARS / length);

    return Math.max(MIN_FONT_SIZE_PX, Math.min(DEFAULT_FONT_SIZE_PX, scaled));
}

/**
 * @param {number} fontSizePx
 * @returns {number}
 */
export function lineLengthForFontSize(fontSizePx) {
    const ratio = fontSizePx / DEFAULT_FONT_SIZE_PX;

    return Math.max(24, Math.floor(MAX_SINGLE_LINE_CHARS / Math.max(ratio, 0.75)));
}

/**
 * @param {string} address
 * @param {number} [maxLineLength]
 * @returns {string[]}
 */
export function wrapAddressLines(address, maxLineLength) {
    const trimmed = address.trim();

    if (trimmed === '') {
        return [];
    }

    if (trimmed.includes('\n') || trimmed.includes('\r')) {
        return trimmed
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter(Boolean);
    }

    const normalized = trimmed.replace(/\s+/g, ' ');

    const lineLength = maxLineLength ?? lineLengthForFontSize(resolveFontSizePx(normalized));

    return wrapByWords(normalized, lineLength);
}

/**
 * @param {string} text
 * @param {number} maxLineLength
 * @returns {string[]}
 */
function wrapByWords(text, maxLineLength) {
    if (text.length <= maxLineLength) {
        return [text];
    }

    const words = text.split(/\s+/);
    const lines = [];
    let current = '';

    for (const word of words) {
        const candidate = current === '' ? word : `${current} ${word}`;

        if (candidate.length <= maxLineLength) {
            current = candidate;
            continue;
        }

        if (current !== '') {
            lines.push(current);
        }

        if (word.length > maxLineLength) {
            for (let offset = 0; offset < word.length; offset += maxLineLength) {
                lines.push(word.slice(offset, offset + maxLineLength));
            }
            current = '';
            continue;
        }

        current = word;
    }

    if (current !== '') {
        lines.push(current);
    }

    return lines;
}

/**
 * @param {string[]} headers
 * @returns {{ recipient: string|null, address: string|null, remarks: string|null }}
 */
export function detectCsvColumns(headers) {
    const normalize = (header) => header
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, '_');

    const normalized = headers.map((header) => ({
        original: header,
        key: normalize(header),
    }));

    const find = (candidates) => {
        const match = normalized.find((header) => candidates.includes(header.key));

        return match?.original ?? null;
    };

    return {
        recipient: find(['recipient_name', 'recipient', 'consignee', 'customer_name', 'name']),
        address: find(['courier_address', 'delivery_address', 'shipping_address', 'recipient_address', 'address', 'address_line_1', 'address1']),
        remarks: find(['remarks', 'notes', 'note', 'delivery_notes', 'special_instructions']),
    };
}
