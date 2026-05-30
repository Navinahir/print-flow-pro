export const PREVIEW_WIDTH_MM = 150;

export const PREVIEW_HEIGHT_MM = 100;

export const PREVIEW_ASPECT_RATIO = PREVIEW_WIDTH_MM / PREVIEW_HEIGHT_MM;

/** 96 CSS dpi — logical pixel size of the 150×100 mm canvas before scaling */
export const PREVIEW_BASE_WIDTH_PX = (PREVIEW_WIDTH_MM * 96) / 25.4;

export const PREVIEW_BASE_HEIGHT_PX = (PREVIEW_HEIGHT_MM * 96) / 25.4;

export const PREVIEW_ROOT_SELECTOR = '[data-merchant-preview-root]';

export const PREVIEW_CONTAINER_SELECTOR = '[data-preview-container]';

export const PREVIEW_STAGE_SELECTOR = '[data-preview-stage]';

export const PREVIEW_CANVAS_SELECTOR = '[data-preview-canvas]';

export const PREVIEW_CANVAS_WRAP_SELECTOR = '[data-preview-canvas-wrap]';

export const PREVIEW_SURFACE_SELECTOR = '[data-preview-surface]';

export const PREVIEW_SAFE_ZONE_INSET_MM = 5;

export const PREVIEW_SAFE_ZONE_SELECTOR = '[data-preview-safe-zone]';

export const PREVIEW_ASPECT_WARNING_SELECTOR = '[data-preview-aspect-warning]';

export const DEFAULT_ASPECT_TOLERANCE_PERCENT = 10;
