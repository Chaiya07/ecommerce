</div>
                </div>
            </div>
        </div>
        <footer class="bg-white border-top mt-4">
            <div class="container-fluid py-3">
                <div class="row align-item-center">
                    <div class="col-md-6 text-center text-start">
                        <p class="mb-0">
                            Copyright By Chaiya &copy;
                            <?= date('Y'); ?>
                            <?= htmlspecialchars($storeName); ?>
                            ALL Right Reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0">
                            Version 1.0.0
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

        <!-- SweetAlert2: popup ยืนยัน / แจ้งเตือนสวยๆ -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            /**
             * ใช้แทน onclick="return confirm('...')"
             * ตัวอย่าง:
             * <a href="#" onclick="confirmDelete(event, 'manage_product.php?delete=1', 'ลบสินค้านี้?')">ลบ</a>
             */
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

            /**
             * ใช้สำหรับ action ยืนยันทั่วไปที่ไม่ใช่การลบ (เช่น เปลี่ยนสิทธิ์/สถานะ)
             */
            function confirmAction(event, url, title = 'ยืนยันการทำรายการ', text = '') {
                event.preventDefault();
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ยืนยัน',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
                return false;
            }

            <?php if (!empty($message)) : ?>
                Swal.fire({
                    title: 'สำเร็จ',
                    text: <?= json_encode($message) ?>,
                    icon: 'success',
                    confirmButtonColor: '#0d6efd',
                    timer: 2500,
                    timerProgressBar: true
                });
            <?php endif; ?>
        </script>
    </body>
</html>