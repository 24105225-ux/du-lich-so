<script setup>
import { APP_CONFIG } from "../config";
</script>

<template>
    <footer class="site-footer">
        <div class="container">
            <p>
                Học phần:
                <strong>
                    Lập trình ứng dụng web trong du lịch 2
                </strong>
            </p>

            <p>
                Mã học phần:
                <strong>CSE703073</strong>
            </p>

            <p>
                Đề tài:
                <strong>
                    ĐT-17 — Nền tảng du lịch học đường
                </strong>
            </p>

            <p>
                Nhóm:
                <strong>
                    {{ APP_CONFIG.teamName }}
                </strong>
            </p>

            <p>
                <em>
                    Sản phẩm học thuật – phục vụ mục đích đào tạo.
                </em>
            </p>
        </div>
    </footer>
</template>
