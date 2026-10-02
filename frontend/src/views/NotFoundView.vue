<template>
    <section class="section">
        <div class="container">
            <div class="empty-state">
                <p>
                    404
                </p>

                <h1>
                    Không tìm thấy trang
                </h1>

                <p>
                    Địa chỉ bạn truy cập
                    không tồn tại.
                </p>

                <RouterLink
                    class="button"
                    :to="{
                        name: 'home',
                    }"
                >
                    Về trang chủ
                </RouterLink>
            </div>
        </div>
    </section>
</template>
