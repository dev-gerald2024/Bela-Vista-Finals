<?php
/**
 * Bella Vista Oasis Spa - Customer Portal Footer Component
 */
?>
        </main>

        <!-- Customer Portal Footer -->
        <footer class="py-3 px-4 border-top text-center" style="border-color: rgba(214, 183, 123, 0.12) !important; background: rgba(16, 19, 19, 0.6);">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <p class="mb-0 text-muted small">
                    &copy; <?= date('Y') ?> <strong class="text-gold">Bella Vista Oasis Spa</strong>. All rights reserved. Holistic Serenity & Luxury Wellness.
                </p>
                <div class="small">
                    <a href="support.php" class="text-muted me-3 hover-gold text-decoration-none">Concierge Support</a>
                    <a href="../terms.php" target="_blank" class="text-muted me-3 hover-gold text-decoration-none">Terms</a>
                    <a href="../privacy.php" target="_blank" class="text-muted hover-gold text-decoration-none">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mobile Sidebar Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    const sidebar = document.getElementById('customerSidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
