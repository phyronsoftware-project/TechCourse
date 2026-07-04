<?php

namespace App\Services;

use App\Models\TechCategory;
use GdImage;
use Illuminate\Support\Str;

class TechnologyPdfService
{
    protected int $imageWidth = 1240;

    protected int $imageHeight = 1754;

    protected int $marginX = 110;

    protected int $marginY = 110;

    protected int $bodyLineHeight = 28;

    protected int $sectionGap = 46;

    public function downloadCategoryPdf(TechCategory $category)
    {
        // Generate one PDF download that contains all technology details inside the category.
        $pdfBinary = $this->buildPdfBinary($category);
        $fileName = Str::slug($category->name ?: 'technology-category').'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Content-Length' => (string) strlen($pdfBinary),
        ]);
    }

    protected function buildPdfBinary(TechCategory $category): string
    {
        $pages = $this->renderCategoryPages($category);
        $objects = ['', '', ''];
        $pageObjectIds = [];

        foreach ($pages as $index => $jpegBinary) {
            $imageInfo = @getimagesizefromstring($jpegBinary);
            $width = $imageInfo[0] ?? $this->imageWidth;
            $height = $imageInfo[1] ?? $this->imageHeight;

            $imageObjectId = count($objects) + 1;
            $contentObjectId = $imageObjectId + 1;
            $pageObjectId = $contentObjectId + 1;
            $resourceName = 'Im'.($index + 1);

            $objects[] = "<< /Type /XObject /Subtype /Image /Width {$width} /Height {$height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($jpegBinary)." >>\nstream\n".$jpegBinary."\nendstream";

            $contentStream = "q\n595 0 0 842 0 0 cm\n/{$resourceName} Do\nQ";
            $objects[] = "<< /Length ".strlen($contentStream)." >>\nstream\n".$contentStream."\nendstream";

            $objects[] = "<< /Type /Page /Parent 2 0 R /Resources << /XObject << /{$resourceName} {$imageObjectId} 0 R >> >> /MediaBox [0 0 595 842] /Contents {$contentObjectId} 0 R >>";

            $pageObjectIds[] = $pageObjectId;
        }

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $kids = implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageObjectIds));
        $objects[2] = "<< /Type /Pages /Count ".count($pageObjectIds)." /Kids [ {$kids} ] >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            if ($id === 0) {
                continue;
            }

            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 ".count($objects)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id < count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        $pdf .= "trailer << /Size ".count($objects)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    protected function renderCategoryPages(TechCategory $category): array
    {
        $pages = [];
        $image = $this->createCanvas();
        $colors = $this->createPalette($image);
        $font = $this->resolveFontPath();
        $y = $this->marginY;

        $y = $this->drawWrappedText($image, $font, 44, $this->marginX, $y, $category->name, $colors['title'], 880, 58);

        if (filled($category->subtitle)) {
            $y += 16;
            $y = $this->drawWrappedText($image, $font, 22, $this->marginX, $y, (string) $category->subtitle, $colors['muted'], 980, 36);
        }

        foreach ($category->technologies as $index => $technology) {
            $sectionTitle = ($index + 1).'. '.$technology->name;
            $sectionSubtitle = $technology->title ?: null;
            $sectionBody = $technology->detail ?: 'No detailed content has been added for this section yet.';
            $sectionLink = $technology->website_url ?: null;

            $estimatedHeight = 180 + $this->estimateWrappedHeight($font, 20, $sectionBody, 980, 36);

            if ($y + $estimatedHeight > ($this->imageHeight - $this->marginY)) {
                $pages[] = $this->canvasToJpeg($image);
                imagedestroy($image);
                $image = $this->createCanvas();
                $colors = $this->createPalette($image);
                $y = $this->marginY;
            }

            $y += 24;
            $y = $this->drawWrappedText($image, $font, 34, $this->marginX, $y, $sectionTitle, $colors['title'], 980, 48);

            if ($sectionSubtitle) {
                $y += 8;
                $y = $this->drawWrappedText($image, $font, 23, $this->marginX, $y, $sectionSubtitle, $colors['subtitle'], 980, 34);
            }

            $y += 10;
            $y = $this->drawWrappedText($image, $font, 20, $this->marginX, $y, $sectionBody, $colors['body'], 980, 36);

            if ($sectionLink) {
                $y += 12;
                $y = $this->drawWrappedText($image, $font, 18, $this->marginX, $y, 'Website: '.$sectionLink, $colors['link'], 980, 30);
            }

            $y += 18;
            imageline($image, $this->marginX, $y, $this->imageWidth - $this->marginX, $y, $colors['line']);
            $y += $this->sectionGap;
        }

        $pages[] = $this->canvasToJpeg($image);
        imagedestroy($image);

        return $pages;
    }

    protected function createCanvas(): GdImage
    {
        $image = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        return $image;
    }

    protected function createPalette(GdImage $image): array
    {
        return [
            'title' => imagecolorallocate($image, 17, 24, 39),
            'subtitle' => imagecolorallocate($image, 31, 41, 55),
            'body' => imagecolorallocate($image, 75, 85, 99),
            'muted' => imagecolorallocate($image, 100, 116, 139),
            'link' => imagecolorallocate($image, 37, 99, 235),
            'line' => imagecolorallocate($image, 229, 231, 235),
        ];
    }

    protected function drawWrappedText(GdImage $image, string $font, int $size, int $x, int $y, string $text, int $color, int $maxWidth, int $lineHeight): int
    {
        foreach ($this->wrapText($font, $size, $text, $maxWidth) as $line) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $line);
            $y += $lineHeight;
        }

        return $y;
    }

    protected function estimateWrappedHeight(string $font, int $size, string $text, int $maxWidth, int $lineHeight): int
    {
        return count($this->wrapText($font, $size, $text, $maxWidth)) * $lineHeight;
    }

    protected function wrapText(string $font, int $size, string $text, int $maxWidth): array
    {
        $text = trim((string) preg_replace("/\R/u", "\n", $text));

        if ($text === '') {
            return [''];
        }

        $lines = [];

        foreach (preg_split("/\n/u", $text) as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                $lines[] = '';
                continue;
            }

            $parts = preg_split('/\s+/u', $paragraph) ?: [$paragraph];
            $current = '';

            foreach ($parts as $part) {
                $candidate = $current === '' ? $part : $current.' '.$part;

                if ($this->textWidth($font, $size, $candidate) <= $maxWidth) {
                    $current = $candidate;
                    continue;
                }

                if ($current !== '') {
                    $lines[] = $current;
                }

                if ($this->textWidth($font, $size, $part) <= $maxWidth) {
                    $current = $part;
                    continue;
                }

                foreach ($this->wrapLongToken($font, $size, $part, $maxWidth) as $tokenLine) {
                    $lines[] = $tokenLine;
                }

                $current = '';
            }

            if ($current !== '') {
                $lines[] = $current;
            }
        }

        return $lines;
    }

    protected function wrapLongToken(string $font, int $size, string $token, int $maxWidth): array
    {
        $chars = preg_split('//u', $token, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $current = '';

        foreach ($chars as $char) {
            $candidate = $current.$char;

            if ($this->textWidth($font, $size, $candidate) <= $maxWidth) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            $current = $char;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [$token];
    }

    protected function textWidth(string $font, int $size, string $text): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return (int) abs(($box[2] ?? 0) - ($box[0] ?? 0));
    }

    protected function canvasToJpeg(GdImage $image): string
    {
        ob_start();
        imagejpeg($image, null, 92);

        return (string) ob_get_clean();
    }

    protected function resolveFontPath(): string
    {
        $projectFont = resource_path('fonts/NotoSansKhmer-Regular.ttf');

        if (is_file($projectFont)) {
            return $projectFont;
        }

        $fallbacks = [
            '/System/Library/Fonts/Supplemental/Khmer MN.ttc',
            '/System/Library/Fonts/Supplemental/Khmer Sangam MN.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($fallbacks as $fontPath) {
            if (is_file($fontPath)) {
                return $fontPath;
            }
        }

        throw new \RuntimeException('No TTF font file available for technology PDF generation.');
    }
}
