<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Vendor;
use Illuminate\Support\Facades\Storage;

/**
 * Turns an already-generated vendor/employee QR code into a printable poster
 * JPEG — the QR image itself is copied in pixel-for-pixel (never re-encoded,
 * never resampled) and framed with the shop's identity and ApniBaari branding.
 *
 * Deliberately a separate class from QRCodeService: that service owns QR
 * *generation* (the payload, the matrix, the file on disk) and is not touched
 * by anything here. This class only ever reads the file QRCodeService already
 * wrote and draws around it.
 */
class QrPosterService
{
    /** Whitespace either side of the QR card, in pixels. Also the print quiet zone. */
    private const SIDE_PAD = 100;

    private const TOP_PAD = 84;
    private const QR_CARD_INSET = 28;
    private const QR_SHADOW_OFFSET = 10;

    private const INK = [23, 23, 26];         // near-black — the hero name
    private const MUTED = [123, 118, 108];    // warm muted taupe — the subordinate name
    private const HAIRLINE = [231, 226, 216]; // warm light border on the QR card
    private const SHADOW = [216, 210, 198];   // warm flat card shadow
    private const CANVAS_BG = [255, 255, 255];

    // The footer band: the same deep navy the rest of the app already uses
    // for its dark surfaces (approval screens, the vendor panel), so the
    // poster's branding reads as unmistakably ApniBaari.
    private const FOOTER_BG = [10, 15, 44];
    private const FOOTER_LABEL = [154, 168, 196]; // muted cool grey — "POWERED BY"
    private const GOLD = [212, 175, 90];          // accent line + wordmark

    /**
     * Print-safe palette per business category. Intentionally darker/more
     * saturated than the site's own neon theme colours (ThemeService), which
     * are tuned for glow effects on a dark UI, not white-on-colour ink.
     */
    private const CATEGORIES = [
        'health'     => ['label' => 'Healthcare',            'color' => [5, 150, 105]],   // emerald-600 — clean, trustworthy
        'beauty'     => ['label' => 'Beauty & Salon',        'color' => [194, 65, 12]],   // orange-700 — elegant, warm
        'sports'     => ['label' => 'Fitness & Sports',      'color' => [220, 38, 38]],   // red-600 — energetic
        'education'  => ['label' => 'Education & Training',  'color' => [124, 58, 237]],  // violet-600
        'consultant' => ['label' => 'Consulting',            'color' => [37, 99, 235]],   // blue-600 — corporate
    ];

    private const ALIASES = [
        'doctor' => 'health', 'barber' => 'beauty', 'salon' => 'beauty',
        'activity' => 'sports', 'training' => 'education', 'consultancy' => 'consultant',
    ];

    public function forVendor(Vendor $vendor, string $qrStoragePath): string
    {
        return $this->render(
            qrStoragePath: $qrStoragePath,
            title: $vendor->business_name,
            subtitle: null,
            categoryKey: $vendor->vendor_type,
        );
    }

    public function forEmployee(Employee $employee, string $qrStoragePath): string
    {
        $vendor = $employee->vendor;

        return $this->render(
            qrStoragePath: $qrStoragePath,
            title: $employee->name,
            subtitle: $vendor?->business_name,
            categoryKey: $vendor?->vendor_type,
        );
    }

    private function render(string $qrStoragePath, string $title, ?string $subtitle, ?string $categoryKey): string
    {
        $category = $this->category($categoryKey);

        $qr = imagecreatefromstring(Storage::disk('public')->get($qrStoragePath));
        $qrSize = imagesx($qr);

        $width = $qrSize + self::SIDE_PAD * 2;
        $qrCardSize = $qrSize + self::QR_CARD_INSET * 2;
        $qrCardTop = self::TOP_PAD;
        $qrCardLeft = intdiv($width - $qrCardSize, 2);

        $badgeR = 42;
        $badgeGap = 52;
        $titleSize = 50;
        $titleGap = 34;
        $subtitleSize = 24;
        $dividerGap = 48;
        $footerContentH = 232;
        $footerBottomPad = 44;
        $footerLogoSize = 60;
        $footerBrandSize = 36;

        $contentTop = $qrCardTop + $qrCardSize + $badgeGap + $badgeR * 2 + $titleGap;
        $contentTop += $titleSize + 16; // hero name line
        if ($subtitle) {
            $contentTop += 14 + $subtitleSize; // subordinate vendor name, employee cards only
        }
        $height = $contentTop + $dividerGap + $footerContentH;

        $canvas = imagecreatetruecolor($width, (int) $height);
        imageantialias($canvas, true);
        $white = $this->color($canvas, self::CANVAS_BG);
        imagefilledrectangle($canvas, 0, 0, $width, (int) $height, $white);

        // QR card: flat shadow, white card, hairline border, then the
        // existing QR image copied in at native resolution (no resampling).
        // This is the hero element — nothing is drawn above it.
        $this->roundedRect($canvas, $qrCardLeft, $qrCardTop + self::QR_SHADOW_OFFSET, $qrCardLeft + $qrCardSize, $qrCardTop + $qrCardSize + self::QR_SHADOW_OFFSET, 26, $this->color($canvas, self::SHADOW));
        $this->roundedRect($canvas, $qrCardLeft, $qrCardTop, $qrCardLeft + $qrCardSize, $qrCardTop + $qrCardSize, 26, $this->color($canvas, self::HAIRLINE));
        $this->roundedRect($canvas, $qrCardLeft + 2, $qrCardTop + 2, $qrCardLeft + $qrCardSize - 2, $qrCardTop + $qrCardSize - 2, 24, $white);
        imagecopy($canvas, $qr, $qrCardLeft + self::QR_CARD_INSET, $qrCardTop + self::QR_CARD_INSET, 0, 0, $qrSize, $qrSize);
        imagedestroy($qr);

        // Category icon badge with a soft halo, flanked by a small ornamental
        // flourish. This — not a text label — is how the category is
        // communicated: no category name is ever printed on the poster.
        $badgeCx = intdiv($width, 2);
        $badgeCy = $qrCardTop + $qrCardSize + $badgeGap + $badgeR;
        $catColor = $this->color($canvas, $category['color']);
        imagealphablending($canvas, true);
        $this->filledCircleAlpha($canvas, $badgeCx, $badgeCy, $badgeR + 22, $category['color'], 18);
        $this->filledCircleAlpha($canvas, $badgeCx, $badgeCy, $badgeR + 10, $category['color'], 32);
        imagefilledellipse($canvas, $badgeCx, $badgeCy, $badgeR * 2, $badgeR * 2, $catColor);
        $this->drawCategoryIcon($canvas, $category['key'], $badgeCx, $badgeCy, $badgeR, $white);
        $this->drawFlourish($canvas, $badgeCx, $badgeCy, $badgeR, $category['color']);

        $y = $badgeCy + $badgeR + $titleGap;
        $y = $this->centeredText($canvas, $title, $width, $y, $titleSize, self::INK, true, $width - self::SIDE_PAD * 1.3);
        $y += 14;

        if ($subtitle) {
            $this->centeredText($canvas, $subtitle, $width, $y, $subtitleSize, self::MUTED, false, $width - self::SIDE_PAD * 1.3);
        }

        // Footer band first, so its curve can never paint over anything
        // above it; the divider is drawn afterwards, on top.
        $footerTop = $contentTop + $dividerGap;
        $this->drawFooterBand($canvas, $width, (int) $height, $footerTop);

        // A small ornamental divider — never a text label — separates the
        // identity block from the branded footer.
        $dividerY = $contentTop + intdiv($dividerGap, 2);
        $this->drawOrnamentalDivider($canvas, $width, $dividerY, $category['color']);

        // The branding block is anchored to the BOTTOM of the footer band —
        // not clustered up near the curved seam — with the "apnibaari.in"
        // wordmark sized like a heading so it reads clearly even as a small
        // thumbnail, not just on a full-size print.
        $logoTop = $height - $footerBottomPad - $footerLogoSize;
        $this->centeredText($canvas, 'POWERED BY', $width, $logoTop - 34, 16, self::FOOTER_LABEL, true, $width, 3);
        $this->drawFooterBrand($canvas, $width, $logoTop, $footerLogoSize, $footerBrandSize);

        ob_start();
        imagejpeg($canvas, null, 92);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);

        return $jpeg;
    }

    private function category(?string $key): array
    {
        $key = strtolower(trim((string) $key));
        $key = self::ALIASES[$key] ?? $key;
        $cat = self::CATEGORIES[$key] ?? self::CATEGORIES['consultant'];
        $cat['key'] = self::CATEGORIES[$key] ?? false ? $key : 'consultant';

        return $cat;
    }

    private function color($im, array $rgb)
    {
        return imagecolorallocate($im, ...$rgb);
    }

    /** Fills an axis-aligned rounded rectangle: two rects plus four corner discs. */
    private function roundedRect($im, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        imagefilledrectangle($im, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($im, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledellipse($im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }

    /**
     * Draws $text centered on $canvasWidth at top-offset $y, auto-shrinking
     * the font until it fits within $maxWidth. Returns the y just below the
     * text so callers can stack the next line.
     */
    private function centeredText($im, string $text, int $canvasWidth, float $y, int $size, array $rgb, bool $bold, float $maxWidth, float $tracking = 0): float
    {
        $font = resource_path('fonts/' . ($bold ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf'));
        $color = $this->color($im, $rgb);

        $tracked = $tracking > 0 ? implode(str_repeat(' ', 1), $this->trackChars($text, $tracking)) : $text;

        while ($size > 12 && $this->textWidth($font, $size, $tracked, $tracking) > $maxWidth) {
            $size -= 1;
        }

        $width = $this->textWidth($font, $size, $tracked, $tracking);
        $x = ($canvasWidth - $width) / 2;

        $bbox = imagettfbbox($size, 0, $font, $tracked);
        $baseline = $y + abs($bbox[7]);

        if ($tracking > 0) {
            $cursor = $x;
            foreach (mb_str_split($text) as $ch) {
                imagettftext($im, $size, 0, (int) $cursor, (int) $baseline, $color, $font, $ch);
                $b = imagettfbbox($size, 0, $font, $ch);
                $cursor += ($b[2] - $b[0]) + $tracking;
            }
        } else {
            imagettftext($im, $size, 0, (int) $x, (int) $baseline, $color, $font, $text);
        }

        return $baseline + abs($bbox[7]) * 0.35;
    }

    private function trackChars(string $text, float $tracking): array
    {
        return mb_str_split($text);
    }

    private function textWidth(string $font, int $size, string $text, float $tracking = 0): float
    {
        if ($tracking > 0) {
            $w = 0;
            foreach (mb_str_split($text) as $ch) {
                $b = imagettfbbox($size, 0, $font, $ch);
                $w += ($b[2] - $b[0]) + $tracking;
            }

            return max(0, $w - $tracking);
        }

        $b = imagettfbbox($size, 0, $font, $text);

        return $b[2] - $b[0];
    }

    private function drawFooterBrand($im, int $canvasWidth, float $topY, int $logoSize = 40, int $fontSize = 22): void
    {
        $logoPath = public_path('iconlogo.png');
        $gap = 16;
        $text = 'apnibaari.in';
        $font = resource_path('fonts/DejaVuSans-Bold.ttf');
        $textWidth = $this->textWidth($font, $fontSize, $text);

        $totalWidth = $logoSize + $gap + $textWidth;
        $x = ($canvasWidth - $totalWidth) / 2;

        if (is_file($logoPath)) {
            $logo = imagecreatefrompng($logoPath);
            imagealphablending($logo, true);
            imagesavealpha($logo, true);
            $resized = imagecreatetruecolor($logoSize, $logoSize);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $logoSize, $logoSize, $transparent);
            imagecopyresampled($resized, $logo, 0, 0, 0, 0, $logoSize, $logoSize, imagesx($logo), imagesy($logo));
            imagealphablending($resized, true);
            imagecopy($im, $resized, (int) $x, (int) $topY, 0, 0, $logoSize, $logoSize);
            imagedestroy($logo);
            imagedestroy($resized);
        }

        $bbox = imagettfbbox($fontSize, 0, $font, $text);
        $textBaseline = $topY + $logoSize / 2 + abs($bbox[7]) / 2 - 2;
        imagettftext($im, $fontSize, 0, (int) ($x + $logoSize + $gap), (int) $textBaseline, $this->color($im, self::GOLD), $font, $text);
    }

    /** A soft, low-opacity halo disc behind the category badge. */
    private function filledCircleAlpha($im, int $cx, int $cy, int $r, array $rgb, int $opacityPercent): void
    {
        $gdAlpha = (int) round((100 - $opacityPercent) / 100 * 127);
        $color = imagecolorallocatealpha($im, $rgb[0], $rgb[1], $rgb[2], $gdAlpha);
        imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $color);
    }

    /**
     * Two short lines tipped with a diamond, flanking the category badge.
     * Purely decorative — this and the icon inside the badge are how the
     * category is communicated; no category name is ever printed.
     */
    private function drawFlourish($im, int $cx, int $cy, int $badgeR, array $rgb): void
    {
        $color = $this->color($im, $rgb);
        $lineLen = 34;
        $gap = $badgeR + 24;

        imagesetthickness($im, 2);
        imageline($im, $cx - $gap, $cy, $cx - $gap - $lineLen, $cy, $color);
        imageline($im, $cx + $gap, $cy, $cx + $gap + $lineLen, $cy, $color);
        imagesetthickness($im, 1);

        $this->diamond($im, $cx - $gap - $lineLen - 9, $cy, 5, $color);
        $this->diamond($im, $cx + $gap + $lineLen + 9, $cy, 5, $color);
    }

    /** A thin line — small diamond — thin line, centered. No text. */
    private function drawOrnamentalDivider($im, int $width, int $y, array $accentRgb): void
    {
        $line = $this->color($im, self::HAIRLINE);
        $accent = $this->color($im, $accentRgb);
        $lineLen = 60;
        $cx = intdiv($width, 2);

        imageline($im, $cx - $lineLen - 10, $y, $cx - 8, $y, $line);
        imageline($im, $cx + 8, $y, $cx + $lineLen + 10, $y, $line);
        $this->diamond($im, $cx, $y, 5, $accent);
    }

    private function diamond($im, int $cx, int $cy, int $r, $color): void
    {
        imagefilledpolygon($im, [$cx, $cy - $r, $cx + $r, $cy, $cx, $cy + $r, $cx - $r, $cy], $color);
    }

    /**
     * The premium branded footer band: deep navy, bleeding to every edge of
     * the poster, with a soft concave curve carved into its top seam and a
     * thin gold accent marking the curve's lowest point.
     */
    private function drawFooterBand($im, int $width, int $height, int $footerTop): void
    {
        $navy = $this->color($im, self::FOOTER_BG);
        imagefilledrectangle($im, 0, $footerTop, $width, $height, $navy);

        // A shallow concave curve right at the seam — NOT a tall dome. Its
        // full vertical extent is only 2*$dip, centered on $footerTop, so it
        // can never reach the content sitting above the footer.
        $dip = 20;
        $ellipseW = (int) ($width * 1.3);
        $ellipseH = $dip * 2;
        imagefilledellipse($im, intdiv($width, 2), $footerTop, $ellipseW, $ellipseH, $this->color($im, self::CANVAS_BG));

        $gold = $this->color($im, self::GOLD);
        $accentW = 72;
        $accentY = $footerTop + $dip + 10;
        imagefilledrectangle($im, intdiv($width, 2) - intdiv($accentW, 2), $accentY, intdiv($width, 2) + intdiv($accentW, 2), $accentY + 3, $gold);
    }

    /** Simple, dependency-free glyphs — no emoji font support needed for print. */
    private function drawCategoryIcon($im, string $key, int $cx, int $cy, int $r, $white): void
    {
        imagesetthickness($im, 5);

        switch ($key) {
            case 'health':
                $arm = (int) ($r * 0.55);
                $thick = (int) ($r * 0.32);
                imagefilledrectangle($im, $cx - $thick, $cy - $arm, $cx + $thick, $cy + $arm, $white);
                imagefilledrectangle($im, $cx - $arm, $cy - $thick, $cx + $arm, $cy + $thick, $white);
                break;

            case 'sports':
                $pts = [
                    $cx - 4, $cy - $r * 0.8,
                    $cx + $r * 0.35, $cy - $r * 0.8,
                    $cx - $r * 0.05, $cy - $r * 0.05,
                    $cx + $r * 0.4, $cy - $r * 0.05,
                    $cx - $r * 0.5, $cy + $r * 0.85,
                    $cx + $r * 0.05, $cy + $r * 0.05,
                    $cx - $r * 0.35, $cy + $r * 0.05,
                ];
                imagefilledpolygon($im, $pts, $white);
                break;

            case 'education':
                $pts = [
                    $cx, $cy - $r * 0.55,
                    $cx + $r * 0.85, $cy - $r * 0.05,
                    $cx, $cy + $r * 0.45,
                    $cx - $r * 0.85, $cy - $r * 0.05,
                ];
                imagefilledpolygon($im, $pts, $white);
                imagefilledrectangle($im, $cx - (int) ($r * 0.32), $cy + (int) ($r * 0.1), $cx + (int) ($r * 0.32), $cy + (int) ($r * 0.42), $white);
                imageline($im, (int) ($cx + $r * 0.85), (int) ($cy - $r * 0.05), (int) ($cx + $r * 0.85), (int) ($cy + $r * 0.55), $white);
                imagefilledellipse($im, (int) ($cx + $r * 0.85), (int) ($cy + $r * 0.6), 8, 8, $white);
                break;

            case 'consultant':
                $bw = (int) ($r * 1.15);
                $bh = (int) ($r * 0.85);
                imagefilledrectangle($im, $cx - intdiv($bw, 2), $cy - intdiv($bh, 3), $cx + intdiv($bw, 2), $cy + intdiv($bh, 2), $white);
                imagefilledrectangle($im, $cx - $bw, $cy - intdiv($bh, 3), $cx - intdiv($bw, 2), $cy - intdiv($bh, 3), $white);
                imagesetthickness($im, 4);
                imagearc($im, $cx, (int) ($cy - $bh * 0.55), (int) ($r * 0.7), (int) ($r * 0.6), 200, 340, $white);
                break;

            case 'beauty':
            default:
                // A four-point sparkle — the same glyph ThemeService already
                // uses for the beauty category (✨), drawn without a font so
                // it renders identically on every server.
                $outer = $r * 0.85;
                $inner = $r * 0.28;
                $pts = [];
                for ($i = 0; $i < 8; $i++) {
                    $angle = deg2rad($i * 45 - 90);
                    $radius = $i % 2 === 0 ? $outer : $inner;
                    $pts[] = $cx + $radius * cos($angle);
                    $pts[] = $cy + $radius * sin($angle);
                }
                imagefilledpolygon($im, $pts, $white);
                $smallOuter = $r * 0.32;
                $smallInner = $r * 0.1;
                $spts = [];
                for ($i = 0; $i < 8; $i++) {
                    $angle = deg2rad($i * 45 - 90);
                    $radius = $i % 2 === 0 ? $smallOuter : $smallInner;
                    $spts[] = $cx + $r * 0.55 + $radius * cos($angle);
                    $spts[] = $cy - $r * 0.5 + $radius * sin($angle);
                }
                imagefilledpolygon($im, $spts, $white);
                break;
        }

        imagesetthickness($im, 1);
    }
}
