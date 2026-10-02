<script setup>
import { APP_CONFIG } from "../config";
</script>

<template>
    <section class="section">
        <div class="container">
            <div class="the-sp">
                <span class="eyebrow">
                    NHÀ TRƯỜNG
                </span>

                <h1>
                    Đăng ký chương trình theo lớp
                </h1>

                <p class="lead">
                    Chức năng đăng ký theo lớp
                    được xử lý tại Laravel
                    để bảo vệ session,
                    CSRF và kiểm tra quyền
                    ở phía máy chủ.
                </p>

                <p>
                    Bạn có thể mở biểu mẫu
                    đăng ký của hệ thống
                    để tiếp tục.
                </p>

                <a
                    class="button"
                    :href="
                        `${APP_CONFIG.backendUrl}/nha-truong/dang-ky-theo-lop`
                    "
                >
                    Mở biểu mẫu đăng ký
                </a>
            </div>
        </div>
    </section>
</template>
