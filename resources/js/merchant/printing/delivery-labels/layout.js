import {
    MIN_FONT_SIZE_PX,
    resolveFontSizePx,
} from './font-shrink.js';

/**
 * @param {HTMLElement} root
 * @param {number} initialFontSizePx
 * @returns {number}
 */
export function fitAddressFontSize(root, initialFontSizePx) {
    const addressBlock = root.querySelector('[data-delivery-label-address]');
    const remarksBlock = root.querySelector('[data-delivery-label-remarks]');

    if (! addressBlock) {
        return initialFontSizePx;
    }

    const previewRoot = root.closest('[data-preview-surface]') ?? root.parentElement;

    if (! previewRoot) {
        return initialFontSizePx;
    }

    const availableHeight = measureAvailableHeight(root, previewRoot, remarksBlock);
    let fontSize = initialFontSizePx;

    applyFontSize(addressBlock, fontSize);

    while (fontSize > MIN_FONT_SIZE_PX && addressBlock.scrollHeight > availableHeight) {
        fontSize -= 1;
        applyFontSize(addressBlock, fontSize);
    }

    return fontSize;
}

/**
 * @param {HTMLElement} root
 * @param {HTMLElement} previewRoot
 * @param {HTMLElement|null} remarksBlock
 */
function measureAvailableHeight(root, previewRoot, remarksBlock) {
    const previewHeight = previewRoot.clientHeight;
    const header = root.querySelector('.delivery-label-preview__header');
    const headerHeight = header?.offsetHeight ?? 0;
    const remarksHeight = remarksBlock && remarksBlock.offsetParent !== null
        ? remarksBlock.offsetHeight
        : 0;
    const verticalPadding = 16;

    return Math.max(48, previewHeight - headerHeight - remarksHeight - verticalPadding);
}

/**
 * @param {HTMLElement} addressBlock
 * @param {number} fontSizePx
 */
function applyFontSize(addressBlock, fontSizePx) {
    addressBlock.querySelectorAll('[data-delivery-label-address-line]').forEach((line) => {
        line.style.fontSize = `${fontSizePx}px`;
    });
}

/**
 * @param {HTMLElement} root
 * @param {() => void} callback
 * @returns {() => void}
 */
export function observeDeliveryLabelLayout(root, callback) {
    const previewRoot = root.closest('[data-preview-surface]');

    if (! previewRoot || typeof ResizeObserver === 'undefined') {
        return () => {};
    }

    const observer = new ResizeObserver(() => {
        callback();
    });

    observer.observe(previewRoot);
    observer.observe(root);

    return () => observer.disconnect();
}
