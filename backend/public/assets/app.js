document.addEventListener('DOMContentLoaded', () => {
  const studentCount = document.querySelector('#student_count');
  if (!studentCount) return;
  studentCount.addEventListener('input', () => {
    const value = Number(studentCount.value);
    if (value < 1) studentCount.value = 1;
    if (value > 60) studentCount.value = 60;
  });
});
