/**
 * Print only the preview canvas: hoist the print target to <body> so other
 * layout nodes do not generate blank pages, then restore after printing.
 *
 * @param {ParentNode} [root=document]
 */
export function printPreview(root = document) {
    const printArea = root.querySelector('[data-print-area]');

    if (! printArea) {
        return;
    }

    const parent = printArea.parentNode;
    const nextSibling = printArea.nextSibling;
    let cleanedUp = false;

    const cleanup = () => {
        if (cleanedUp) {
            return;
        }

        cleanedUp = true;

        document.body.classList.remove('merchant-print-active');

        if (parent) {
            parent.insertBefore(printArea, nextSibling);
        }

        window.removeEventListener('afterprint', cleanup);
    };

    document.body.prepend(printArea);
    document.body.classList.add('merchant-print-active');

    window.addEventListener('afterprint', cleanup, { once: true });

    window.requestAnimationFrame(() => {
        window.print();
    });

    window.setTimeout(cleanup, 3000);
}
