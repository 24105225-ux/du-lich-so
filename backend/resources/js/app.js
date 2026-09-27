import "../css/app.css";

document.addEventListener(
    "DOMContentLoaded",
    () => {
        const studentCount =
            document.querySelector(
                "#student_count"
            );

        if (studentCount) {
            studentCount.addEventListener(
                "input",
                () => {
                    if (
                        Number(studentCount.value) < 1
                    ) {
                        studentCount.value = 1;
                    }

                    if (
                        Number(studentCount.value) > 60
                    ) {
                        studentCount.value = 60;
                    }
                }
            );
        }
    }
);
