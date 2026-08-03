</div>

<?php $s = $GLOBALS['storeSettings'] ?? []; ?>
<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <h5 class="footer-title">
                    <?php if (!empty($s['logo'])) : ?>
                        <img src="uploads/settings/<?= htmlspecialchars($s['logo']) ?>" height="28" class="me-2">
                    <?php endif; ?>
                    <?= htmlspecialchars($s['name'] ?? 'ไชยยา') ?>
                </h5>
                <p class="footer-text mb-0">
                    ร้านค้าออนไลน์คุณภาพดี ราคาคุ้มค่า พร้อมโปรโมชั่นพิเศษสำหรับลูกค้าทุกท่าน
                </p>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <h6 class="footer-heading"><i class="bi bi-geo-alt-fill me-1"></i>ติดต่อร้านค้า</h6>
                <ul class="footer-list">
                    <?php if (!empty($s['address'])) : ?>
                        <li><i class="bi bi-house-door me-2"></i><?= nl2br(htmlspecialchars($s['address'])) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($s['phone'])) : ?>
                        <li><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($s['phone']) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($s['email'])) : ?>
                        <li><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($s['email']) ?></li>
                    <?php endif; ?>
                    <?php if (empty($s['address']) && empty($s['phone']) && empty($s['email'])) : ?>
                        <li class="text-muted">ยังไม่ได้เพิ่มข้อมูลติดต่อ</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <h6 class="footer-heading"><i class="bi bi-share me-1"></i>ช่องทางโซเชียล</h6>
                <div class="footer-social">
                    <?php if (!empty($s['facebook'])) : ?>
                        <a href="<?= htmlspecialchars($s['facebook']) ?>" target="_blank" class="footer-social-icon">
                            <i class="bi bi-facebook"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($s['line_id'])) : ?>
                        <a href="#" class="footer-social-icon" title="<?= htmlspecialchars($s['line_id']) ?>">
                            <i class="bi bi-line"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (empty($s['facebook']) && empty($s['line_id'])) : ?>
                        <span class="text-muted">ยังไม่ได้เพิ่มช่องทางโซเชียล</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom text-center py-3">
        Copyright © <?= date("Y") ?> <?= htmlspecialchars($s['name'] ?? 'ไชยยา') ?> E-Commerce System
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2: popup ยืนยัน / แจ้งเตือนสวยๆ -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(event, url, text = 'คุณต้องการลบรายการนี้ใช่หรือไม่?') {
        event.preventDefault();
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
        return false;
    }
</script>

<!-- AOS: ไลบรารีทำ animation ตอนเลื่อนหน้าจอ -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60
    });
</script>
</body>
</html>