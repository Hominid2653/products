<?php
// products-proxy.php on your test.sianflowers.co.ke server
$shop_url = 'https://demo.sianflowers.co.ke/shop';
$html = file_get_contents($shop_url);

// Use DOMDocument to extract just the product section
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Remove the shop banner header
$banner = $xpath->query('//header[contains(@class, "woocommerce-products-header")] | //div[contains(@class, "page-header")] | //div[contains(@class, "shop-header")] | //div[contains(@class, "entry-header")]');
if ($banner->length > 0) {
    foreach ($banner as $element) {
        $element->parentNode->removeChild($element);
    }
}

// Also remove any general banners or hero sections
$heroSections = $xpath->query('//div[contains(@class, "banner")] | //div[contains(@class, "hero")] | //section[contains(@class, "banner")] | //section[contains(@class, "hero")]');
if ($heroSections->length > 0) {
    foreach ($heroSections as $element) {
        $element->parentNode->removeChild($element);
    }
}

// Extract the products section (without the banner)
$productsSection = $xpath->query('//div[contains(@class, "content-area")] | //main[contains(@class, "site-main")]')->item(0);
$sidebar = $xpath->query('//div[contains(@class, "shop-filter-sidebar")]')->item(0);

if ($productsSection) {
    $productsSectionHtml = $dom->saveHTML($productsSection);
    
    // Create a minimal HTML document with just what we need
    $output = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Products</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }
            
            .products-container {
                display: flex;
                width: 100%;
            }
            
            .sidebar {
                width: 250px;
                padding: 20px;
                background: #f5f5f5;
            }
            
            .products {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                padding: 20px;
                flex: 1;
            }
            
            /* Hide any remaining banners or headers */
            .woocommerce-products-header,
            .entry-header,
            .page-header,
            .shop-header,
            .banner,
            .hero {
                display: none !important;
            }

            
        </style>';
    
    // Extract and include original CSS
    $styles = $xpath->query('//link[@rel="stylesheet"]');
    foreach ($styles as $style) {
        $href = $style->getAttribute('href');
        if (strpos($href, 'http') !== 0) {
            $href = 'https://demo.sianflowers.co.ke' . $href;
        }
        $output .= '<link rel="stylesheet" href="' . $href . '">';
    }
    
    $output .= '</head><body>
        <div class="products-container">';
    
    // Add sidebar if found
    if ($sidebar) {
        $output .= '<div class="sidebar">' . $dom->saveHTML($sidebar) . '</div>';
    }
    
    // Add products section (now without the banner)
    $output .= $productsSectionHtml;
    
    $output .= '</div></body></html>';
    
    // Fix relative URLs
    $output = preg_replace('/(href|src|action)="\/([^"]*)"/', '$1="https://demo.sianflowers.co.ke/$2"', $output);
    
    echo $output;
} else {
    echo "Products section not found on the page.";
}
?>