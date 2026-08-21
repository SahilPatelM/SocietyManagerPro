<?php

$dir = __DIR__ . '/../public/icons';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function makeIcon(string $path, int $size): void
{
    $img = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($img, 99, 102, 241);
    imagefill($img, 0, 0, $bg);

    $white = imagecolorallocate($img, 255, 255, 255);
    $font = 5;
    $text = 'SMP';
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    $scale = max(1, (int) round($size / 128));

    for ($y = 0; $y < $scale; $y++) {
        for ($x = 0; $x < $scale; $x++) {
            imagestring(
                $img,
                $font,
                (int) (($size - $tw * $scale) / 2) + $x,
                (int) (($size - $th * $scale) / 2) + $y,
                $text,
                $white
            );
        }
    }

    imagepng($img, $path);
    imagedestroy($img);
}

makeIcon($dir . '/icon-192.png', 192);
makeIcon($dir . '/icon-512.png', 512);
makeIcon($dir . '/apple-touch-icon.png', 180);

echo "PWA icons created in public/icons/\n";
