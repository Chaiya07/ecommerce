</div>
<footer class="bg-dark text-white text-center py-3 mt-5">
    Copyright © <?= date("Y") ?>
    Chaiya E-Commerce System
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS: ไลบรารีทำ animation ตอนเลื่อนหน้าจอ -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 700,   // ความเร็ว animation (ms)
        easing: 'ease-out-cubic',
        once: true,       // เล่นครั้งเดียวตอนเลื่อนผ่าน ไม่เล่นซ้ำ
        offset: 60
    });
</script>
</body>
</html>