<?php
include_once("../config.php");

if (!isset($_GET['hotel_id']) || !is_numeric($_GET['hotel_id'])) {
    die("Invalid hotel ID");
}
$hotel_id = (int) $_GET['hotel_id'];

// Get hotel info
$hotel_result = $conn->query("SELECT * FROM hotels WHERE id = $hotel_id");
if ($hotel_result->num_rows <= 0) die("Hotel not found.");
$hotel = $hotel_result->fetch_assoc();

// Filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Fetch categories
$categories = [];
$cat_query = $conn->query("SELECT DISTINCT category FROM places ORDER BY category");
while ($row = $cat_query->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Build places query
$escaped_search = $conn->real_escape_string($search);
$escaped_cat = $conn->real_escape_string($category_filter);

$sql = "SELECT * FROM places WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$escaped_search%' OR description LIKE '%$escaped_search%')";
}
if (!empty($category_filter) && $category_filter !== 'all') {
    $sql .= " AND category = '$escaped_cat'";
}
$sql .= " ORDER BY category, name";
$places = $conn->query($sql);

// Image provider
function getPlaceImage($category) {
    $keywords = [
        "historical" => "lucknow,historical,monument",
        "park" => "lucknow,park,garden",
        "mall" => "lucknow,shopping,mall",
        "restaurant" => "lucknow,restaurant,food",
        "temple" => "lucknow,temple,hindu",
        "market" => "lucknow,market,bazaar"
    ];
    $query = $keywords[strtolower($category)] ?? "lucknow,travel";
    return "https://source.unsplash.com/300x200/?$query&sig=" . rand(1, 9999);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Explore Lucknow - <?= htmlspecialchars($hotel['hotel_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a4d8e;
            --secondary: #1a366d;
            --accent: #ff6b6b;
            --light: #f8f9fa;
            --dark: #212529;
            --text: #495057;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f7ff; color: var(--text); }

        header {
            background-color: var(--primary);
            color: white;
            padding: 15px 50px;
            text-align: center;
        }

        .logo img {
            height: 50px;
            margin-bottom: 10px;
        }

        .hero-header {
            background: linear-gradient(rgba(0,0,0,.5), rgba(0,0,0,.5)),
            url("../<?= $hotel['image_url'] ?>") center/cover no-repeat;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero-content {
            max-width: 700px;
            padding: 20px;
        }

        .hotel-name { font-size: 3.5rem; font-weight: 600; text-shadow: 2px 2px 5px #000; }
        .tagline { font-size: 1.4rem; margin-top: 15px; font-weight: 300; }

        .container {
            padding: 40px 50px;
        }

        .search-box {
            background: white;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-box input,
        .search-box select {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
        }

        .search-box button {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: var(--secondary);
        }

        .category-title {
            font-size: 1.8rem;
            color: var(--primary);
            margin: 40px 0 20px;
            position: relative;
            padding-left: 10px;
        }

        .category-title::before {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: var(--accent);
            bottom: 5px;
            left: 0;
        }

        .places-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .place-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .place-card:hover {
            transform: translateY(-5px);
        }

        .place-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }

        .place-content {
            padding: 15px;
        }

        .place-name { font-size: 1.2rem; font-weight: 600; }
        .place-category {
            font-size: 0.85rem;
            background: var(--accent);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
        }

        .place-desc {
            font-size: 0.95rem;
            color: var(--text);
        }

        .no-results {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            margin-top: 50px;
        }

        footer {
            background-color: var(--secondary);
            color: white;
            text-align: center;
            padding: 25px 50px;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
            .hero-content .hotel-name { font-size: 2.5rem; }
            .container { padding: 20px; }
            .search-box { flex-direction: column; }
        }
        header {
    background-color: var(--primary);
    color: white;
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}

.logo img {
    height: 60px;
}

.header-title {
    flex: 1;
    text-align: center;
    font-size: 1.6rem;
    font-weight: 500;
}

#time-display {
    font-size: 1rem;
    font-weight: 400;
}

        header{
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="../<?= $hotel['logo_of_hotel'] ?>" alt="<?= $hotel['hotel_name'] ?> Logo" />
    </div>
    
    <div id="time-display"></div>
</header>


<div class="hero-header">
    <div class="hero-content">
        <h1 class="hotel-name"><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
        <p class="tagline">Experience the City of Nawabs</p>
    </div>
</div>

<div class="container">
    <form method="GET" class="search-box">
        <input type="hidden" name="hotel_id" value="<?= $hotel_id ?>">
        <input type="text" name="search" placeholder="Search places..." value="<?= htmlspecialchars($search) ?>">
        <select name="category">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat == $category_filter) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <?php if ($places->num_rows > 0): ?>
        <?php $currentCategory = ""; ?>
        <?php while ($place = $places->fetch_assoc()): ?>
            <?php if ($place['category'] != $currentCategory): ?>
                <?php if ($currentCategory !== "") echo '</div>'; ?>
                <h2 class="category-title"><?= htmlspecialchars($place['category']) ?></h2>
                <div class="places-grid">
                <?php $currentCategory = $place['category']; ?>
            <?php endif; ?>

            <div class="place-card">
                <div class="place-image" style="background-image: url('<?= getPlaceImage($place['category']) ?>');"></div>
                <div class="place-content">
                    <div class="place-name"><?= htmlspecialchars($place['name']) ?></div>
                    <div class="place-category"><?= htmlspecialchars($place['sub_category']) ?></div>
                    <div class="place-desc"><?= htmlspecialchars($place['description']) ?></div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="far fa-compass fa-3x" style="color: var(--primary); margin-bottom: 20px;"></i>
            <h3>No Places Found</h3>
            <p>Try adjusting your search or category.</p>
        </div>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($hotel['hotel_name']) ?>. All rights reserved.</p>
    <p>Powered by Yashinfosystem</p>
</footer>

<script>
    function updateTime() {
        const now = new Date();
        const time = now.toLocaleTimeString();
        document.getElementById("time-display").textContent = time;
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>

</body>
</html>

<?php $conn->close(); ?>
