<?php

use Vtiful\Kernel\Format;
$pageTitle = 'View Supplier';
$pageSubtitle = 'Detailed profile for an individual supplier.';
include("partials/nav.php");
?>

<section class="section active">
    <div class="section-header">
        <div>
            <p>Quick profile view for a selected supplier (static mock data).</p>
        </div>
        <div class="section-actions">
            <button class="btn-ghost btn">Back to list</button>
            <button class="btn-primary btn">Edit Supplier</button>
        </div>
    </div>

    <?php
    $supplierquery = "SELECT s.*,sa.logo,sa.banner,rp.paid_date AS contract_start, rp.due_date  AS contract_end
            FROM suppliers s LEFT JOIN shop_assets sa ON sa.supplier_id = s.supplier_id LEFT JOIN (
            SELECT rp1.* FROM rent_payments rp1 INNER JOIN (
                SELECT supplier_id, MAX(paid_date) AS latest_paid
                FROM rent_payments
                GROUP BY supplier_id
            ) rp2 ON rp1.supplier_id = rp2.supplier_id AND rp1.paid_date = rp2.latest_paid ) rp ON rp.supplier_id = s.supplier_id;";
    $supplierresult = mysqli_query($conn, $supplierquery);
    while ($supplierrow = mysqli_fetch_assoc($supplierresult)) {
        $reviewquery = "SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total_reviews 
                FROM reviews 
                WHERE supplier_id = '$supplierrow[supplier_id]';";
        $reviewresult = mysqli_query($conn, $reviewquery);
        $reviewrow = mysqli_fetch_assoc($reviewresult);
        ?>

        <div class="grid">
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <div class="company_image_container">
                        <img class="company_image"
                            src="../uploads/shops/<?= $supplierrow['supplier_id'] ?>/<?= $supplierrow['banner'] ?>" alt="">
                    </div>
                </div>
                <div class="company_status">
                    <div class="card-value"><?= $supplierrow['company_name'] ?></div>
                    <div>
                        <?php
                        $status = $supplierrow['status'];
                        $statusClass = match ($status) {
                            'active' => 'status-active',
                            'inactive' => 'status-inactive',
                            'banned' => 'status-banned',
                            default => 'status-inactive'
                        };
                        ?>
                    </div>
                    <div class="company_status_right">
                        <a
                            href="suppliersmanagement.php?adminid=<?= urlencode($adminid) ?>&supplierid=<?= $supplierrow['supplier_id'] ?>"><i
                                class="fa-solid fa-bars"></i></a>
                        <span class="status-pill <?= $statusClass ?>">
                            <?= ucfirst(htmlspecialchars($status)) ?>
                        </span>
                    </div>
                </div>
                <p style="font-size: 13px; color: var(--muted); margin-top: 6px;">
                    <?= $supplierrow['description'] ?>
                </p>
                <p style="font-size: 12px; color: var(--muted); margin-top: 8px;">
                    <strong>Location:</strong> <?= $supplierrow['address'] ?><br>
                    <strong>Contract:</strong> <?= date('M d, Y', strtotime($supplierrow['contract_start'])) ?> –
                    <?= date('M d, Y', strtotime($supplierrow['contract_end'])) ?><br>
                    <strong>Contact:</strong> <?= $supplierrow['email'] ?> • <?= $supplierrow['phone'] ?>
                </p>
            </div>
            <div class="card">
                <div class="card-title">Key Metrics</div>
                <p style="font-size: 13px; margin-top: 6px; margin-bottom: 10px;"><strong>Monthly Rent:</strong>
                    $<?= $supplierrow['renting_price'] ?></p>
                <p style="font-size: 13px; margin-bottom: 10px;"><strong>Last Payment:</strong>
                    <?= date('M d, Y', strtotime($supplierrow['contract_start'])) ?></p>
                <p style="font-size: 13px; margin-bottom: 10px;"><strong>Average Rating:</strong>
                    <?= $reviewrow['avg_rating'] ?>★</p>
                <p style="font-size: 13px; margin-bottom: 10px;"><strong>Due In:</strong>
                    <?php
                    if ($supplierrow['contract_end']) {
                        $dueDate = new DateTime($supplierrow['contract_end']);
                        $today = new DateTime();
                        $interval = $today->diff($dueDate);

                        if ($today > $dueDate) {
                            $dueText = "Overdue";
                            $dueClass = "status-banned";
                        } elseif ($interval->days === 0) {
                            $dueText = "Due today";
                            $dueClass = "status-pending";
                        } else {
                            $dueText = $interval->days . " day" . ($interval->days > 1 ? "s" : "") . " left";
                            $dueClass = "status-active";
                        }

                        echo '<span class=" status-pill ' . $dueClass . '">' . $dueText . '</span>';
                    } else {
                        echo '<span class=" status-pill status-inactive">No payment</span>';
                    }
                    ?>

            </div>
            <div class="card">
                <div class="card-title">Tags</div>
                <p style="margin-top: 6px;">
                    <?php
                    $tags = $supplierrow['tags'];

                    if (!empty($tags)) {
                        $tagArray = array_map('trim', explode(',', $tags));

                        foreach ($tagArray as $tag) {
                            echo '<span class="badge-soft">' . htmlspecialchars($tag) . '</span>';
                        }
                    } else {
                        echo '<span class="badge-soft muted">No tags</span>';
                    }
                    ?>

                </p>
            </div>
        </div>
    <?php } ?>
</section>

<script src="script.js"></script>
</body>

</html>