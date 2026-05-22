<?php
$file = __DIR__ . '/resources/views/dashboard/index.blade.php';
$raw  = file_get_contents($file);

// The file bytes contain literal UTF-8 bytes representing the mojibake chars.
// We map these exact double-encoded byte sequences to clean ASCII/HTML strings.
$fixes = [
    // Circular arrow (â†º)
    "\xc3\xa2\xe2\x80\xa0\xc2\xba" => "&#8635;",

    // Right arrow (â†’)
    "\xc3\xa2\xe2\x80\xa0\xe2\x80\x99" => " &rarr; ",

    // Box drawing bar / Em dash (â”€)
    "\xc3\xa2\xe2\x80\x9d\xe2\x82\xac" => "-",

    // Ellipsis (â€¦)
    "\xc3\xa2\xe2\x82\xac\xc2\xa6" => "...",

    // Em dash (â€”)
    "\xc3\xa2\xe2\x82\xac\xe2\x80\x9d" => " - ",

    // Middle dot (Â·)
    "\xc3\x82\xc2\xb7" => " &middot; ",

    // Dash (Â-)
    "\xc3\x82\x2d" => " &middot; ",

    // Checkmark ✅ (âœ…) - We use plain text / unicode checkmark to abide by "Strictly No Emojis"
    "\xc3\xa2\xc5\x93\xe2\x80\xa6" => "✓",
];

foreach ($fixes as $from => $to) {
    $raw = str_replace($from, $to, $raw);
}

file_put_contents($file, $raw);
echo "Fixed. Characters replaced.\n";
