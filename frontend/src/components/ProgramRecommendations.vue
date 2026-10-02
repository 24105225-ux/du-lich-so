<!--
D:\CODE\du-lich-so\frontend\src\components\ProgramRecommendations.vue
-->

<script setup>
import { onMounted, ref } from 'vue'
import api from '../lib/api'

const props = defineProps({
  programId: {
    type: Number,
    required: true,
  },
})

const loading = ref(true)
const error = ref('')
const source = ref('')
const items = ref([])

async function loadRecommendations() {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get(
      `/api/v1/programs/${props.programId}/recommend`
    )

    source.value = response.data?.data?.source ?? ''
    items.value = response.data?.data?.items ?? []
  } catch (err) {
    error.value =
      err?.response?.data?.message ??
      'Khong tai duoc goi y chuong trinh.'
  } finally {
    loading.value = false
  }
}

onMounted(loadRecommendations)
</script>

<template>
  <section class="recommendation-box">
    <div class="recommendation-header">
      <div>
        <p class="eyebrow">
          PHAN TICH DU LIEU
        </p>

        <h2>
          Chương trình tương tự
        </h2>
      </div>

      <span
        v-if="source"
        class="source-badge"
      >
        {{
          source === 'python'
            ? 'Python TF-IDF'
            : 'Fallback Laravel'
        }}
      </span>
    </div>

    <div v-if="loading" class="loading-box">
      Đang tính toán gợi ý...
    </div>

    <div
      v-else-if="error"
      class="error-box"
      role="alert"
    >
      {{ error }}
    </div>

    <div
      v-else-if="items.length === 0"
      class="empty-box"
    >
      Chưa có chương trình phù hợp để gợi ý.
    </div>

    <div
      v-else
      class="recommendation-grid"
    >
      <article
        v-for="item in items"
        :key="item.program_id"
        class="recommendation-card"
      >
        <p class="education-level">
          {{ item.education_level }}
        </p>

        <h3>
          {{ item.title }}
        </h3>

        <p class="price">
          {{
            Number(
              item.price_per_student ?? 0
            ).toLocaleString('vi-VN')
          }}
          đ / học sinh
        </p>

        <p
          v-if="item.similarity !== undefined"
          class="similarity"
        >
          Độ tương đồng:
          {{
            (
              Number(item.similarity) * 100
            ).toFixed(1)
          }}%
        </p>

        <RouterLink
          :to="{
            name: 'program-detail',
            params: {
              id: item.program_id,
            },
          }"
          class="view-link"
        >
          Xem chương trình
        </RouterLink>
      </article>
    </div>
  </section>
</template>

<style scoped>
.recommendation-box {
  margin-top: 32px;
  padding: 24px;
  border: 1px solid var(--mau-vien);
  border-radius: var(--bo-goc);
  background: var(--mau-nen-phu);
}

.recommendation-header {
  display: flex;
  gap: 16px;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
}

.eyebrow {
  margin: 0 0 4px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: var(--mau-chinh);
}

.recommendation-header h2 {
  margin: 0;
}

.source-badge {
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--mau-chinh);
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
}

.recommendation-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  margin-top: 20px;
}

.recommendation-card {
  padding: 18px;
  border: 1px solid var(--mau-vien);
  border-radius: var(--bo-goc);
  background: var(--mau-nen);
}

.education-level {
  margin: 0 0 6px;
  font-size: 13px;
  color: var(--mau-chinh);
  font-weight: 700;
}

.recommendation-card h3 {
  margin: 0 0 10px;
}

.price {
  margin: 0 0 8px;
  font-weight: 700;
}

.similarity {
  margin: 0 0 14px;
  color: var(--mau-chu-nhat);
  font-size: 14px;
}

.view-link {
  font-weight: 700;
  color: var(--mau-chinh-dam);
}

.loading-box,
.error-box,
.empty-box {
  margin-top: 16px;
  padding: 16px;
  border-radius: var(--bo-goc);
}

.loading-box {
  background: var(--mau-nen);
}

.error-box {
  background: var(--mau-nen);
  color: var(--mau-nhan);
}

.empty-box {
  background: var(--mau-nen);
  color: var(--mau-chu-nhat);
}

@media (min-width: 640px) {
  .recommendation-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .recommendation-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
