<?php
// -------------------------
// Configuration
// -------------------------
$base_url = "https://dgsconsulting.solutions";

// Static pages
$static_pages = [
    "/" => ["priority" => "1.00", "changefreq" => "weekly"],
    "/Services/index.html" => ["priority" => "0.85", "changefreq" => "monthly"],
    "/Time-Drain-Detector/index.html" => ["priority" => "0.70", "changefreq" => "monthly"],
    "/system-check.html" => ["priority" => "0.60", "changefreq" => "monthly"],
    "/thank-you.html" => ["priority" => "0.40", "changefreq" => "never"],
];

// Directories to scan recursively
$directories_to_scan = [
    "/case-studies" => ["priority" => "0.65", "changefreq" => "monthly"],
    "/Resources"    => ["priority" => "0.60", "changefreq" => "monthly"],
];

// -------------------------
// Functions
// -------------------------
function scan_directory_recursive($dir) {
    $all_files = [];
    $full_dir = getcwd() . $dir;
    if (!is_dir($full_dir)) return $all_files;

    $items = scandir($full_dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        $full_path = $full_dir . '/' . $item;

        if (is_dir($full_path)) {
            $all_files = array_merge($all_files, scan_directory_recursive($path));
        } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'html') {
            $all_files[] = $path;
        }
    }
    return $all_files;
}

function add_url_xml($path, $base_url, $priority, $changefreq) {
    $file_path = getcwd() . $path;
    $lastmod = file_exists($file_path) ? date('c', filemtime($file_path)) : date('c');
    return "  <url>\n" .
           "    <loc>{$base_url}{$path}</loc>\n" .
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
    $output .= add_url_xml($path, $base_url, $info['priority'], $info['changefreq']);
}

// Add pages from dynamic directories
foreach ($directories_to_scan as $dir => $info) {
    $files = scan_directory_recursive($dir);
    foreach ($files as $file) {
        $output .= add_url_xml($file, $base_url, $info['priority'], $info['changefreq']);
    }
}

$output .= '</urlset>' . PHP_EOL;

// Save sitemap.xml
file_put_contents('sitemap.xml', $output);
echo "sitemap.xml generated successfully!\n";

// -------------------------
// Commit & Push to GitHub
// -------------------------
exec('git add sitemap.xml');
exec('git commit -m "Update sitemap.xml"');
exec('git push');
echo "sitemap.xml committed and pushed to GitHub successfully!\n";

