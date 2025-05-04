<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $row['hotel_name']; ?></title>
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #1a1a2e;
            color: #fff;
            font-size: 16px;
        }
        
        header {
            background-color: #16213e;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: #e94560;
        }
        
        .tagline {
            font-style: italic;
            color: #a8a8c1;
            font-size: 0.9rem;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .menu-section {
            width: 100%;
            padding-right: 0;
            margin-bottom: 20px;
        }
        
        .cart-section {
            width: 100%;
            background-color: #16213e;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            position: static;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
            justify-content: center;
        }
        
        .filter-btn {
            background-color: #0f3460;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.8rem;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background-color: #e94560;
        }
        
        .menu-category {
            margin-bottom: 25px;
        }
        
        .category-title {
            font-size: 1.5rem;
            color: #e94560;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e94560;
        }
        
        .menu-items {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .menu-item {
            background-color: #16213e;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        
        .menu-item:hover {
            transform: translateY(-5px);
        }
        
        .item-image {
            height: 150px;
            background-size: cover;
            background-position: center;
        }
        
        .item-details {
            padding: 12px;
        }
        
        .item-name {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #e94560;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .item-desc {
            color: #a8a8c1;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }
        
        .add-to-cart {
            background-color: #e94560;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            font-size: 0.9rem;
        }
        
        .cart-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #e94560;
        }
        
        .cart-items {
            margin-bottom: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #0f3460;
            font-size: 0.9rem;
        }
        
        .cart-item-name {
            flex: 2;
            font-size: 0.9rem;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quantity-btn {
            background-color: #0f3460;
            color: white;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .cart-item-price {
            flex: 1;
            text-align: right;
            font-size: 0.9rem;
        }
        
        .remove-item {
            color: #e94560;
            cursor: pointer;
            margin-left: 8px;
            font-size: 1rem;
        }
        
        .cart-total {
            font-size: 1.1rem;
            font-weight: bold;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .checkout-btn {
            background-color: #e94560;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s;
        }
        
        .empty-cart {
            text-align: center;
            color: #a8a8c1;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        /* Tablet View */
        @media (min-width: 600px) {
            .menu-items {
                grid-template-columns: repeat(2, 1fr);
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .container {
                padding: 20px;
            }
        }

        /* Desktop View */
        @media (min-width: 900px) {
            .container {
                flex-direction: row;
                padding: 20px;
            }
            
            .menu-section {
                flex: 2;
                padding-right: 20px;
                margin-bottom: 0;
            }
            
            .cart-section {
                flex: 1;
                position: sticky;
                top: 20px;
                margin-bottom: 0;
            }
            
            .menu-items {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
            
            h1 {
                font-size: 2.5rem;
            }
            
            .filter-btn {
                padding: 8px 15px;
                font-size: 1rem;
            }
        }

        /* Small Mobile Devices */
        @media (max-width: 400px) {
            h1 {
                font-size: 1.5rem;
            }
            
            .category-title {
                font-size: 1.3rem;
            }
            
            .item-image {
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo $row['hotel_name']; ?></h1>
        <p class="tagline">Sing your heart out while enjoying delicious food and drinks!</p>
    </header>
    
    <div class="container">
        <section class="menu-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-category="all">All Items</button>
                <button class="filter-btn" data-category="appetizers">Appetizers</button>
                <button class="filter-btn" data-category="main-courses">Main Courses</button>
                <button class="filter-btn" data-category="drinks">Drinks</button>
                <button class="filter-btn" data-category="desserts">Desserts</button>
            </div>
            
            <div class="menu-category" data-category="appetizers">
                <h2 class="category-title">Appetizers</h2>
                <div class="menu-items">
                    <!-- Appetizer items will be added by JavaScript -->
                </div>
            </div>
            
            <div class="menu-category" data-category="main-courses">
                <h2 class="category-title">Main Courses</h2>
                <div class="menu-items">
                    <!-- Main course items will be added by JavaScript -->
                </div>
            </div>
            
            <div class="menu-category" data-category="drinks">
                <h2 class="category-title">Drinks</h2>
                <div class="menu-items">
                    <!-- Drink items will be added by JavaScript -->
                </div>
            </div>
            
            <div class="menu-category" data-category="desserts">
                <h2 class="category-title">Desserts</h2>
                <div class="menu-items">
                    <!-- Dessert items will be added by JavaScript -->
                </div>
            </div>
        </section>
        
        <section class="cart-section">
            <h2 class="cart-title">Your Order</h2>
            <div class="cart-items">
                <!-- Cart items will be added here -->
                <p class="empty-cart">Your cart is empty</p>
            </div>
            <div class="cart-total">
                <span>Total:</span>
                <span class="total-price">$0.00</span>
            </div>
            <button class="checkout-btn">Checkout</button>
        </section>
    </div>

    <script>
        // Menu Data and JavaScript functionality remains the same as before
        // ... (keep all your existing JavaScript code)
    </script>
</body>
</html>