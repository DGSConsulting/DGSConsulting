<?php
// Base URL of your site
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

// Function to recursively scan directories
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

// Start building XML
$output = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// Add static pages
foreach ($static_pages as $path => $info) {
    $lastmod = file_exists(getcwd() . $path) ? date('c', filemtime(getcwd() . $path)) : date('c');
    $output .= "  <url>\n";
    $output .= "    <loc>{$base_url}{$path}</loc>\n";
    $output .= "    <lastmod>{$lastmod}</lastmod>\n";
    $output .= "    <changefreq>{$info['changefreq']}</changefreq>\n";
    $output .= "    <priority>{$info['priority']}</priority>\n";
    $output .= "  </url>\n";
}

// Add pages from scanned directories
foreach ($directories_to_scan as $dir => $info) {
    $files = scan_directory_recursive($dir);
    foreach ($files as $file) {
        $lastmod = file_exists(getcwd() . $file) ? date('c', filemtime(getcwd() . $file)) : date('c');
        $output .= "  <url>\n";
        $output .= "    <loc>{$base_url}{$file}</loc>\n";
        $output .= "    <lastmod>{$lastmod}</lastmod>\n";
        $output .= "    <changefreq>{$info['changefreq']}</changefreq>\n";
        $output .= "    <priority>{$info['priority']}</priority>\n";
        $output .= "  </url>\n";
    }
}

$output .= '</urlset>' . PHP_EOL;

// Save sitemap.xml locally
file_put_contents('sitemap.xml', $output);
echo "sitemap.xml generated successfully!\n";
