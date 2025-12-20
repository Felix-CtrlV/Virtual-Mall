<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion E-commerce</title>
    <link rel="stylesheet" href="../templates/<?= basename(__DIR__) ?>/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>    
    <header class="header">
        <nav class="navbar">
            <div class="fashionlogo">FASHION</div>
            <ul class="nav-links">
                <li><a href="pages/home.php" class="active">Home</a></li>
                <li><a href="pages/products.php">Shop</a></li>
                <li><a href="pages/about.php">About Us</a></li>
                <li><a href="pages/collection.php">Collection</a></li>
            </ul>
            <div class="search-bar">
                <input type="text" placeholder="Search.........">
                <i class="fas fa-search"></i>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <span class="title">NEW FASHION</span>
            <h1>New collection for 2026</h1>
            <p>Discover Your Favorite Style: All the Fashion You Need Awaits Here!</p>
            <button class="shop-now-btn">SHOP NOW</button>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Fashion Model">
        </div>
    </section>

    <section class="products">
        <h2>Our Products</h2>
        <div class="product-grid">
            <?php
            $products = [
                ['name' => 'BLACK JUMPSUIT', 'price' => '$500', 'image' => 'https://images.unsplash.com/photo-1551232864-3f0890e580d9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'],
                ['name' => 'WIDE-LEG JEANS', 'price' => '$400', 'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'],
                ['name' => 'CREAM RIBBED TOP', 'price' => '$300', 'image' => 'https://images.unsplash.com/photo-1607346256334-db8ed0b4e1d5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'],
                ['name' => 'WRAP SHIRT', 'price' => '$500', 'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'],
                ['name' => 'GRAPHITE SUIT', 'price' => '$600', 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aacb86c39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80'],
                ['name' => 'LONG BLACK DRESS', 'price' => '$400', 'image' => 'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80']
            ];

            foreach ($products as $product) {
                echo '<div class="product-card">';
                echo '<img src="' . $product['image'] . '" alt="' . $product['name'] . '">';
                echo '<h3>' . $product['name'] . '</h3>';
                echo '<p class="price">' . $product['price'] . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
</body>
</html>