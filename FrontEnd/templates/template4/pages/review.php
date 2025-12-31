<?php
// ... (Database connection code assumed above or included)

// 1. Handle Insertion if POST (Ensure this only runs on POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO reviews (supplier_id, customer_id, review, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    // Note: Make sure variables $supplier_id, $customer_id, $review_text, $rating are defined from $_POST input before this
    $stmt->bind_param("iisi", $supplier_id, $customer_id, $review, $rating);
    $stmt->execute();
    $stmt->close();
}

// 2. Fetch Stats
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 1;

// --- 1. Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = (int)$_POST['rating'];
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $review_text = trim($_POST['review_text']);

    if ($rating > 0 && !empty($email)) {
        // Find customer ID based on email
        $cust_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
        $cust_stmt->bind_param("s", $email);
        $cust_stmt->execute();
        $res = $cust_stmt->get_result();

        if ($res->num_rows > 0) {
            $customer = $res->fetch_assoc();

            // Insert Review
            $stmt = $conn->prepare("INSERT INTO reviews (supplier_id, customer_id, review, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iisi", $supplier_id, $customer['customer_id'], $review_text, $rating);

            if ($stmt->execute()) {
                echo "<script>alert('Review submitted successfully!'); window.location.href='?supplier_id=$supplier_id&page=review';</script>";
            } else {
                echo "<script>alert('Error submitting review.');</script>";
            }
        } else {
            echo "<script>alert('Email not found. Please register first.');</script>";
        }
    } else {
        echo "<script>alert('Please select a star rating and fill all fields.');</script>";
    }
}

// --- 2. Fetch Stats for Bars ---
$sql_stats = "SELECT rating FROM reviews WHERE supplier_id = $supplier_id";
$result_stats = $conn->query($sql_stats);

$total_reviews = 0;
$sum_ratings = 0;
$star_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if ($result_stats->num_rows > 0) {
    while ($row = $result_stats->fetch_assoc()) {
        $r = (int)$row['rating'];
        if ($r >= 1 && $r <= 5) {
            $star_counts[$r]++;
            $sum_ratings += $r;
            $total_reviews++;
        }
    }
}
$avg_rating = $total_reviews > 0 ? number_format($sum_ratings / $total_reviews, 1) : "0.0";

// --- 3. Fetch Recent Reviews ---
$sql_reviews = "
    SELECT r.*, c.name, c.image 
    FROM reviews r 
    JOIN customers c ON r.customer_id = c.customer_id 
    WHERE r.supplier_id = $supplier_id 
    ORDER BY r.created_at DESC LIMIT 5";
$reviews_res = $conn->query($sql_reviews);
?>

<style>
    :root {
        --gold: #ffc107;
        /* Active star color */
        --grey: #e0e0e0;
        /* Inactive star color */
        --text-main: #333;
        --bg-white: #ffffff;
    }

    /* --- LAYOUT --- */
    .review-container {
        max-width: 1100px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Segoe UI', sans-serif;
    }

    /* --- TOP STATS SECTION --- */
    .stats-panel {
        display: flex;
        gap: 40px;
        background: var(--bg-white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 40px;
        align-items: center;
    }

    .bars-area {
        flex: 2;
    }

    /* Bar Row: Label | Track | Percent | Count */
    .bar-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 14px;
        color: #555;
    }

    .bar-label {
        width: 50px;
        font-weight: 600;
        font-size: 12px;
    }

    .bar-track {
        flex: 1;
        height: 8px;
        background: #eee;
        border-radius: 4px;
        margin: 0 15px;
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        background: #e0bb7d;
        border-radius: 4px;
    }

    /* Muted gold for bars */
    .bar-percent {
        width: 45px;
        text-align: right;
        color: #888;
        font-size: 13px;
    }

    .bar-count {
        width: 35px;
        text-align: right;
        color: #aaa;
        font-size: 13px;
    }

    /* Big Score Display */
    .score-area {
        flex: 1;
        text-align: center;
        background: #fffbea;
        padding: 25px;
        border-radius: 12px;
        min-width: 180px;
    }

    .big-rating {
        font-size: 48px;
        font-weight: 800;
        color: #d4a017;
        line-height: 1;
    }

    .big-stars {
        color: var(--gold);
        font-size: 20px;
        margin: 10px 0;
    }

    /* --- CONTENT SPLIT --- */
    .content-split {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 40px;
    }

    .section-header {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #222;
    }

    /* --- REVIEW LIST --- */
    .review-item {
        display: flex;
        gap: 15px;
        background: #fff;
        padding: 20px;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .u-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        background: #ddd;
    }

    .u-name {
        font-weight: bold;
        display: block;
        margin-bottom: 3px;
        font-size: 15px;
    }

    .u-date {
        font-size: 11px;
        color: #999;
        display: block;
        margin-bottom: 5px;
    }

    .u-stars {
        font-size: 11px;
        color: var(--gold);
        margin-bottom: 8px;
    }

    .u-text {
        font-size: 14px;
        color: #555;
        line-height: 1.5;
        margin: 0;
    }

    /* --- REVIEW FORM (IMPROVED) --- */
    .form-panel {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #eee;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04);
        height: fit-content;
    }

    .input-wrap {
        margin-bottom: 15px;
    }

    .input-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 13px;
        color: #333;
    }

    .text-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: #fafafa;
        font-size: 14px;
        box-sizing: border-box;
    }

    .text-input:focus {
        outline: none;
        border-color: var(--gold);
        background: #fff;
    }

    /* === INTERACTIVE STAR WIDGET CSS === */
    .star-widget {
        display: flex;
        flex-direction: row;
        /* Normal order */
        gap: 8px;
    }

    .star-icon {
        font-size: 24px;
        color: var(--grey);
        /* Default inactive color */
        cursor: pointer;
        transition: color 0.2s, transform 0.1s;
    }

    /* Hover interaction handled by JS primarily, but css hover for scale */
    .star-icon:hover {
        transform: scale(1.15);
    }

    /* Active State (Applied by JS) */
    .star-icon.active {
        color: var(--gold);
    }

    /* Make span-based stars align and focusable */
    .star-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        font-size: 28px;
        line-height: 1;
        user-select: none;
    }

    .star-icon:focus {
        outline: 2px solid rgba(255, 193, 7, 0.18);
        outline-offset: 3px;
    }

    /* Stars used in review list */
    .u-star {
        font-size: 14px;
        margin-right: 4px;
        color: var(--gold);
        display: inline-block;
        line-height: 1;
    }

    .u-star.empty {
        color: #ccc;
    }

    .submit-btn {
        width: 100%;
        background: var(--gold);
        color: #fff;
        border: none;
        padding: 14px;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        transition: background 0.2s;
    }

    .submit-btn:hover {
        background: #e0a800;
    }

    @media(max-width: 768px) {
        .content-split {
            grid-template-columns: 1fr;
        }

        .stats-panel {
            flex-direction: column;
        }
    }
</style>

<div class="review-container">

    <div class="stats-panel">
        <div class="bars-area">
            <?php
            $labels = [5 => 'FIVE', 4 => 'FOUR', 3 => 'THREE', 2 => 'TWO', 1 => 'ONE'];
            foreach ($labels as $star => $label):
                $count = $star_counts[$star];
                $percent = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
            ?>
                <div class="bar-row">
                    <span class="bar-label"><?= $label ?></span>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= $percent ?>%;"></div>
                    </div>
                    <span class="bar-percent"><?= number_format($percent, 1) ?>%</span>
                    <span class="bar-count">(<?= $count ?>)</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="score-area">
            <div class="big-rating"><?= $avg_rating ?></div>
            <div class="big-stars">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= round($avg_rating)) ? '<span class="u-star">★</span>' : '<span class="u-star empty">★</span>';
                }
                ?>
            </div>
            <div style="font-size:13px; color:#666;"><?= $total_reviews ?> Ratings</div>
        </div>
    </div>

    <div class="content-split">

        <div>
            <h3 class="section-header">Recent Feedbacks</h3>
            <?php if ($reviews_res->num_rows > 0): ?>
                <?php while ($row = $reviews_res->fetch_assoc()): ?>
                    <div class="review-item">
                        <img src="<?= $row['image'] ? '../uploads/customers/' . $row['image'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png' ?>" class="u-avatar">
                        <div>
                            <span class="u-name"><?= htmlspecialchars($row['name']) ?></span>
                            <span class="u-date"><?= date('F d, Y', strtotime($row['created_at'])) ?></span>
                            <div class="u-stars">
                                <?php for ($k = 1; $k <= 5; $k++) echo ($k <= $row['rating']) ? '<span class="u-star">★</span>' : '<span class="u-star empty">★</span>'; ?>
                            </div>
                            <p class="u-text"><?= htmlspecialchars($row['review']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#777">No reviews yet.</p>
            <?php endif; ?>
        </div>

        <div>
            <h3 class="section-header">Add a Review</h3>
            <div class="form-panel">
                <form method="POST" action="" id="reviewForm">

                    <div class="input-wrap">
                        <label class="input-label">Your Rating </label>

                        <input type="hidden" name="rating" id="ratingValue" value="0">

                        <div class="star-widget" role="radiogroup" aria-label="Rating">
                            <span class="star-icon" data-value="1" role="radio" tabindex="0" aria-checked="false" aria-label="1 star">★</span>
                            <span class="star-icon" data-value="2" role="radio" tabindex="0" aria-checked="false" aria-label="2 stars">★</span>
                            <span class="star-icon" data-value="3" role="radio" tabindex="0" aria-checked="false" aria-label="3 stars">★</span>
                            <span class="star-icon" data-value="4" role="radio" tabindex="0" aria-checked="false" aria-label="4 stars">★</span>
                            <span class="star-icon" data-value="5" role="radio" tabindex="0" aria-checked="false" aria-label="5 stars">★</span>
                        </div>
                    </div>

                   

                    <div class="input-wrap">
                        <label class="input-label">Write Your Review *</label>
                        <textarea name="review_text" rows="4" class="text-input" placeholder="Share your experience..." required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Submit Review</button>
                </form>
            </div>
        </div>

    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const stars = document.querySelectorAll('.star-icon');
        const ratingInput = document.getElementById('ratingValue');
        let currentRating = 0; // Tracks the clicked/locked rating

        stars.forEach(star => {
            // 1. HOVER: Highlight stars up to the one being hovered
            star.addEventListener('mouseover', function() {
                const value = parseInt(this.getAttribute('data-value'));
                highlightStars(value);
            });

            // 2. MOUSE OUT: Reset to the locked rating (if any)
            star.addEventListener('mouseout', function() {
                highlightStars(currentRating);
            });

            // 3. CLICK: Lock the rating
            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-value'));
                ratingInput.value = currentRating;
                highlightStars(currentRating);
            });
        });

        function highlightStars(count) {
            stars.forEach(s => {
                const val = parseInt(s.getAttribute('data-value'));
                if (val <= count) {
                    s.classList.add('active'); // Turn Gold
                    s.style.color = 'var(--gold)';
                } else {
                    s.classList.remove('active'); // Turn Grey
                    s.style.color = 'var(--grey)';
                }
            });
        }

        // Prevent submission if rating is 0
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            if (ratingInput.value == 0) {
                e.preventDefault();
                alert("Please click a star to rate!");
            }
        });
    });
</script>