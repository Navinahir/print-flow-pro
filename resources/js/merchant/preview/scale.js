import {
    PREVIEW_CANVAS_SELECTOR,
    PREVIEW_CANVAS_WRAP_SELECTOR,
    PREVIEW_STAGE_SELECTOR,
} from './constants.js';

/**
 * Read logical canvas pixel dimensions from container data attributes.
 *
 * @param {Element} container
 * @returns {{ widthPx: number, heightPx: number }}
 */
export function getPreviewBaseDimensions(container) {
    const widthMm = parseFloat(container.dataset.previewWidthMm || '100');
    const heightMm = parseFloat(container.dataset.previewHeightMm || '150');

    return {
        widthPx: (widthMm * 96) / 25.4,
        heightPx: (heightMm * 96) / 25.4,
    };
}

/**
 * Scale the preview canvas to fit its stage while preserving aspect ratio.
 */
export function attachPreviewScaling(container) {
    const stage = container.querySelector(PREVIEW_STAGE_SELECTOR);
    const canvasWrap = container.querySelector(PREVIEW_CANVAS_WRAP_SELECTOR);
    const canvas = container.querySelector(PREVIEW_CANVAS_SELECTOR);

    if (! stage || ! canvasWrap || ! canvas) {
        return () => {};
    }

    const updateScale = () => {
        const stageWidth = stage.clientWidth;
        const stageHeight = stage.clientHeight;

        if (stageWidth <= 0 || stageHeight <= 0) {
            return;
        }

        const { widthPx, heightPx } = getPreviewBaseDimensions(container);
        const sizeHint = container.querySelector('.merchant-preview-container__footer');
        const sizeHintReserve = (sizeHint?.offsetHeight ?? 56) + 16;
        const availableHeight = Math.max(stageHeight - sizeHintReserve, heightPx * 0.25);
        const scaleByWidth = stageWidth / widthPx;
        const scaleByHeight = availableHeight / heightPx;
        const maxZoom = parseFloat(container.dataset.previewMaxZoom || '1');
        const scale = Math.min(scaleByWidth, scaleByHeight, maxZoom);
        const scaledWidth = widthPx * scale;
        const scaledHeight = heightPx * scale;

        canvasWrap.style.width = `${scaledWidth}px`;
        canvasWrap.style.height = `${scaledHeight}px`;
        canvas.style.setProperty('--preview-scale', scale.toFixed(4));
    };

    const observer = new ResizeObserver(updateScale);
    observer.observe(stage);
    updateScale();

    return () => observer.disconnect();
}

/**
 * @param {ParentNode} [root=document]
 */
export function initPreviewScaling(root = document) {
    const containers = root.querySelectorAll('[data-preview-container]');
    const disconnectors = [];

    containers.forEach((container) => {
        disconnectors.push(attachPreviewScaling(container));
    });

    return () => {
        disconnectors.forEach((disconnect) => {
            if (typeof disconnect === 'function') {
                disconnect();
            }
        });
    };
}

export { PREVIEW_ASPECT_RATIO } from './constants.js';
