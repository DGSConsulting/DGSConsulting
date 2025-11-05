<?php
// -------------------------
// Configuration
// -------------------------
$base_url = "https://dgsconsulting.solutions";

// Static pages (optional, usually home or main landing pages)
$static_pages = [
    "/" => ["priority" => "1.00", "changefreq" => "weekly"],
];

// -------------------------
// Functions
// -------------------------

// Recursive scan for .html files anywhere in the repo
function scan_html_recursive($dir) {
    $all_files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full_path = $dir . '/' . $item;
        if (is_dir($full_path)) {
            $all_files = array_merge($all_files, scan_html_recursive($full_path));
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'html') {
            $all_files[] = $full_path;
        }
    }
    return $all_files;
}

// Convert full system path to relative path for URLs
function relative_path($full_path) {
    return str_replace(getcwd(), '', $full_path);
}

// Generate XML for a single URL
function add_url_xml($path, $base_url, $priority = "0.50", $changefreq = "monthly") {
    $lastmod = file_exists($path) ? date('c', filemtime($path)) : date('c');
    $url = $base_url . str_replace('\\', '/', $path);
    return "  <url>\n" .
           "    <loc>{$url}</loc>\n" .
           "    <lastmod>{$lastmod}</lastmod>\n" .
           "    <changefreq>{$changefreq}</changefreq>\n" .
           "    <priority>{$priority}</priority>\n" .
           "  </url>\n";
}

// -------------------------
// Generate sitemap.xml
// -------------------------

$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// Add static pages
foreach ($static_pages as $path => $info) {
    $local_path = getcwd() . $path;
    $output .= add_url_xml($local_path, $base_url, $info['priority'], $info['changefreq']);
}

// Scan repo recursively for all HTML files
$html_files = scan_html_recursive(getcwd());
foreach ($html_files as $file) {
    $relative = relative_path($file);
    $output .= add_url_xml($relative, $base_url);
}

$output .= '</urlset>' . PHP_EOL;

// Save sitemap.xml
file_put_contents('sitemap.xml', $output);
echo "sitemap.xml generated successfully!\n";

// -------------------------
// Commit & Push
// -------------------------
exec('git add sitemap.xml');
exec('git commit -m "Update sitemap.xml"');
exec('git push');
echo "sitemap.xml committed and pushed to GitHub successfully!\n";
