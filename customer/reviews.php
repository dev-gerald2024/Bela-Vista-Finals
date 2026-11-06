<?php
session_start();
require_once '../config/app.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
if (!is_logged_in() || get_user_role() !== 'customer') { redirect('../login.php'); }
$page_title = 'My Reviews';
$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT c.id FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer_id = $stmt->fetch()['id'];

// Completed appointments without reviews
$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_date, s.name as service_name, t.id as therapist_id,
           CONCAT(u.first_name, ' ', u.last_name) as therapist_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    LEFT JOIN therapist_assignments ta ON ta.appointment_id = a.id
    LEFT JOIN therapists t ON ta.therapist_id = t.id
    LEFT JOIN employees e ON t.employee_id = e.id
    LEFT JOIN users u ON e.user_id = u.id
    LEFT JOIN reviews r ON r.appointment_id = a.id AND r.customer_id = ?
    WHERE a.customer_id = ? AND a.status = 'completed' AND r.id IS NULL
    ORDER BY a.appointment_date DESC
");
$stmt->execute([$customer_id, $customer_id]);
$reviewable = $stmt->fetchAll();

// Past reviews
$stmt = $pdo->prepare("
    SELECT r.*, s.name as service_name, a.appointment_date,
           CONCAT(u.first_name, ' ', u.last_name) as therapist_name
    FROM reviews r
    LEFT JOIN appointments a ON r.appointment_id = a.id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN therapists t ON r.therapist_id = t.id
    LEFT JOIN employees e ON t.employee_id = e.id
    LEFT JOIN users u ON e.user_id = u.id
    WHERE r.customer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$customer_id]);
$reviews = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <h2 class="font-heading text-gold mb-4"><i class="fas fa-star me-2"></i>My Reviews</h2>
            <?php display_flash(); ?>

            <!-- Leave a Review -->
            <?php if (!empty($reviewable)): ?>
            <div class="glass-card p-4 mb-4">
                <h5 class="text-gold mb-3"><i class="fas fa-pen me-2"></i>Leave a Review</h5>
                <form action="../process/submit_review.php" method="POST">
                    <?= csrf_token_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Select Appointment</label>
                            <select name="appointment_id" class="form-select bg-dark text-light border-secondary" required id="reviewAppointment">
                                <option value="">Choose an appointment to review...</option>
                                <?php foreach ($reviewable as $r): ?>
                                    <option value="<?= $r['id'] ?>" data-therapist="<?= $r['therapist_id'] ?>">
                                        <?= htmlspecialchars($r['service_name']) ?> — <?= date('M d, Y', strtotime($r['appointment_date'])) ?>
                                        <?= $r['therapist_name'] ? ' (Therapist: ' . htmlspecialchars($r['therapist_name']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="therapist_id" id="reviewTherapistId">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Rating</label>
                            <div class="star-rating" id="starRating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="far fa-star fs-3" data-rating="<?= $i ?>" style="cursor:pointer;color:#9C9A96;transition:color 0.2s;" onmouseover="highlightStars(<?= $i ?>)" onmouseout="resetStars()" onclick="setRating(<?= $i ?>)"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Your Comment</label>
                            <textarea name="comment" class="form-control bg-dark text-light border-secondary" rows="4" placeholder="Share your experience..." required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-gold mt-3"><i class="fas fa-paper-plane me-2"></i>Submit Review</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Past Reviews -->
            <div class="glass-card p-4">
                <h5 class="text-gold mb-3"><i class="fas fa-history me-2"></i>Your Reviews</h5>
                <?php if (empty($reviews)): ?>
                    <p class="text-muted text-center py-4">You haven't submitted any reviews yet.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                    <div class="border-bottom border-secondary pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-gold mb-1"><?= htmlspecialchars($rev['service_name'] ?? 'Service') ?></h6>
                                <small class="text-muted"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                                <?php if ($rev['therapist_name']): ?>
                                    <small class="text-muted ms-2">| Therapist: <?= htmlspecialchars($rev['therapist_name']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa<?= $i <= $rev['rating'] ? 's' : 'r' ?> fa-star text-gold"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mt-2 mb-1"><?= htmlspecialchars($rev['comment']) ?></p>
                        <?php if ($rev['admin_response']): ?>
                            <div class="mt-2 p-3 rounded" style="background:rgba(214,183,123,0.08);border-left:3px solid #D6B77B;">
                                <small class="text-gold fw-bold">Bella Vista Response:</small>
                                <p class="mb-0 small mt-1"><?= htmlspecialchars($rev['admin_response']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
let selectedRating = 0;
function highlightStars(n) { document.querySelectorAll('#starRating i').forEach((s,i) => { s.style.color = i < n ? '#D6B77B' : '#9C9A96'; s.className = i < n ? 'fas fa-star fs-3' : 'far fa-star fs-3'; }); }
function resetStars() { highlightStars(selectedRating); }
function setRating(n) { selectedRating = n; document.getElementById('ratingValue').value = n; highlightStars(n); }
document.getElementById('reviewAppointment')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('reviewTherapistId').value = opt.dataset.therapist || '';
});
</script>

<?php require_once 'includes/footer.php'; ?>

