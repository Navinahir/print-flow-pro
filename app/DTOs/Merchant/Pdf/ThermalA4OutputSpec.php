<?php



declare(strict_types=1);



namespace App\DTOs\Merchant\Pdf;



/**

 * Layout specification for thermal label output on A4 sheets.

 */

final readonly class ThermalA4OutputSpec

{

    public function __construct(

        public float $pageWidthMm,

        public float $pageHeightMm,

        public float $labelWidthMm,

        public float $labelHeightMm,

        public float $labelSafeZoneInsetMm,

        public float $singlePaddingLeftMm,

        public float $singlePaddingTopMm,

        public float $multiPaddingLeftMm,

        public float $multiPaddingTopMm,

        public int $multiColumns,

        public int $multiRows,

        public int $labelsPerPage,

        public bool $multiCenterGridOnPage,

    ) {}



    public static function fromConfig(): self

    {

        $a4 = config('pdf.a4_output', []);

        $label = $a4['label'] ?? [];

        $single = $a4['single'] ?? [];

        $multi = $a4['multi'] ?? [];



        return new self(

            pageWidthMm: (float) ($a4['page_width_mm'] ?? 210.0),

            pageHeightMm: (float) ($a4['page_height_mm'] ?? 297.0),

            labelWidthMm: (float) ($label['width_mm'] ?? 105.0),

            labelHeightMm: (float) ($label['height_mm'] ?? 148.0),

            labelSafeZoneInsetMm: (float) ($label['safe_zone_inset_mm'] ?? 0.0),

            singlePaddingLeftMm: (float) ($single['padding_left_mm'] ?? 10.0),

            singlePaddingTopMm: (float) ($single['padding_top_mm'] ?? 10.0),

            multiPaddingLeftMm: (float) ($multi['padding_left_mm'] ?? 0.0),

            multiPaddingTopMm: (float) ($multi['padding_top_mm'] ?? 0.0),

            multiColumns: (int) ($multi['columns'] ?? 2),

            multiRows: (int) ($multi['rows'] ?? 2),

            labelsPerPage: (int) ($multi['labels_per_page'] ?? 4),

            multiCenterGridOnPage: (bool) ($multi['center_grid_on_page'] ?? true),

        );

    }



    /**

     * @return array{x_mm: float, y_mm: float}

     */

    public function multiLabelOriginMm(float $labelWidthMm, float $labelHeightMm, int $column, int $row): array

    {

        [$gridOriginX, $gridOriginY] = $this->multiGridOriginMm($labelWidthMm, $labelHeightMm);



        return [

            'x_mm' => $gridOriginX + ($column * $labelWidthMm),

            'y_mm' => $gridOriginY + ($row * $labelHeightMm),

        ];

    }



    /**

     * @return array{0: float, 1: float}

     */

    public function multiGridOriginMm(float $labelWidthMm, float $labelHeightMm): array

    {

        $gridWidth = $this->multiColumns * $labelWidthMm;

        $gridHeight = $this->multiRows * $labelHeightMm;



        $originX = $this->multiPaddingLeftMm;

        $originY = $this->multiPaddingTopMm;



        if ($this->multiCenterGridOnPage) {

            if ($gridWidth < $this->pageWidthMm) {

                $originX += ($this->pageWidthMm - $gridWidth) / 2;

            }



            if ($gridHeight < $this->pageHeightMm) {

                $originY += ($this->pageHeightMm - $gridHeight) / 2;

            }

        }



        return [$originX, $originY];

    }



    /**

     * @return array<string, float|int|bool>

     */

    public function toArray(): array

    {

        return [

            'page_width_mm' => $this->pageWidthMm,

            'page_height_mm' => $this->pageHeightMm,

            'label_width_mm' => $this->labelWidthMm,

            'label_height_mm' => $this->labelHeightMm,

            'single_padding_left_mm' => $this->singlePaddingLeftMm,

            'single_padding_top_mm' => $this->singlePaddingTopMm,

            'labels_per_page' => $this->labelsPerPage,

        ];

    }

}


