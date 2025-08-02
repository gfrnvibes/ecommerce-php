<?php
require_once __DIR__ . '/../config/config.php';

// Data 10 produk untuk diinsert
$products_to_insert = [
    [
        'name' => 'Kemeja Pria Casual',
        'description' => 'Kemeja katun lengan panjang dengan desain casual, cocok untuk sehari-hari.',
        'price' => 150000.00,
        'stock' => 50,
        'image_url' => 'kemeja_pria_casual.jpg',
    ],
    [
        'name' => 'Celana Jeans Slim Fit',
        'description' => 'Celana jeans denim berkualitas tinggi, potongan slim fit.',
        'price' => 250000.00,
        'stock' => 30,
        'image_url' => 'celana_jeans_slim_fit.jpg',
    ],
    [
        'name' => 'Sepatu Sneakers Sporty',
        'description' => 'Sepatu sneakers ringan dan nyaman untuk aktivitas olahraga maupun casual.',
        'price' => 300000.00,
        'stock' => 40,
        'image_url' => 'sepatu_sneakers_sporty.jpg',
    ],
    [
        'name' => 'Tas Ransel Laptop',
        'description' => 'Tas ransel multifungsi dengan kompartemen laptop, tahan air.',
        'price' => 180000.00,
        'stock' => 25,
        'image_url' => 'tas_ransel_laptop.jpg',
    ],
    [
        'name' => 'Jam Tangan Digital',
        'description' => 'Jam tangan digital modern dengan fitur stopwatch dan alarm.',
        'price' => 95000.00,
        'stock' => 60,
        'image_url' => 'jam_tangan_digital.jpg',
    ],
    [
        'name' => 'Headphone Bluetooth',
        'description' => 'Headphone over-ear dengan konektivitas bluetooth, suara jernih.',
        'price' => 220000.00,
        'stock' => 35,
        'image_url' => 'headphone_bluetooth.jpg',
    ],
    [
        'name' => 'Buku Fiksi Petualangan',
        'description' => 'Novel fiksi petualangan yang mendebarkan, cocok untuk mengisi waktu luang.',
        'price' => 75000.00,
        'stock' => 80,
        'image_url' => 'buku_fiksi_petualangan.jpg',
    ],
    [
        'name' => 'Kacamata Hitam Stylish',
        'description' => 'Kacamata hitam dengan desain modern, perlindungan UV400.',
        'price' => 120000.00,
        'stock' => 45,
        'image_url' => 'kacamata_hitam_stylish.jpg',
    ],
    [
        'name' => 'Power Bank 10000mAh',
        'description' => 'Power bank kapasitas besar, pengisian cepat, kompatibel dengan berbagai perangkat.',
        'price' => 160000.00,
        'stock' => 55,
        'image_url' => 'power_bank_10000mah.jpg',
    ],
    [
        'name' => 'Mouse Gaming RGB',
        'description' => 'Mouse gaming ergonomis dengan pencahayaan RGB, DPI dapat diatur.',
        'price' => 110000.00,
        'stock' => 20,
        'image_url' => 'mouse_gaming_rgb.jpg',
    ]
];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)");

    foreach ($products_to_insert as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['stock'],
            $product['image_url'],
        ]);
    }

    $pdo->commit();
    echo "10 produk berhasil diinsert ke database.\n";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Error saat menginsert produk: " . $e->getMessage() . "\n";
    exit(1);
}

?>