<?php
/**
 * แสดงปุ่มย้อนกลับ
 *
 * @param string|null $fallbackUrl  ลิงก์ที่จะไปถ้าไม่มีประวัติหน้าเว็บก่อนหน้า (ใช้ history.back() เป็นหลัก)
 * @param string      $label        ข้อความบนปุ่ม
 */
function renderBackButton($fallbackUrl = null, $label = 'กลับ')
{
    if ($fallbackUrl) {
        // มี fallback: ถ้า history ว่างเปล่า ให้ไปที่ fallback แทน
        echo '<a href="' . htmlspecialchars($fallbackUrl) . '" '
            . 'onclick="if (document.referrer && document.referrer.includes(location.host)) { event.preventDefault(); history.back(); }" '
            . 'class="btn btn-secondary">'
            . '<i class="bi bi-arrow-left"></i> ' . htmlspecialchars($label)
            . '</a>';
    } else {
        // ไม่มี fallback: ใช้ history.back() อย่างเดียว
        echo '<a href="javascript:void(0)" onclick="history.back()" class="btn btn-secondary">'
            . '<i class="bi bi-arrow-left"></i> ' . htmlspecialchars($label)
            . '</a>';
    }
}