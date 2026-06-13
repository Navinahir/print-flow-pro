<?php

declare(strict_types=1);

namespace App\Services\Merchant\Pdf\Support;

use setasign\Fpdi\Fpdi;

/**
 * FPDI helper for thermal label normalization (portrait output + landscape rotation).
 */
class ThermalLabelFpdi extends Fpdi
{
    /**
     * Place an imported page rotated 90° clockwise.
     */
    public function useImportedPageRotated90Clockwise(
        string $pageId,
        float $xMm,
        float $yMm,
        float $sourceWidthMm,
        float $sourceHeightMm,
        float $scale = 1.0,
    ): void {
        if (! isset($this->importedPages[$pageId])) {
            throw new \InvalidArgumentException('Imported page does not exist.');
        }

        $importedPage = $this->importedPages[$pageId];
        $scaleFactor = $scale * $this->k;
        $placedHeightMm = $sourceWidthMm * $scale;
        $xPt = $xMm * $this->k;
        $yPt = $yMm * $this->k;
        $placedHeightPt = $placedHeightMm * $this->k;

        $this->_out(sprintf(
            'q 0 J 1 w 0 j 0 G 0 g 0 %.4F -%.4F 0 %.4F %.4F cm /%s Do Q',
            $scaleFactor,
            $scaleFactor,
            $xPt + $placedHeightPt,
            $this->hPt - $yPt,
            $importedPage['id'],
        ));
    }
}
