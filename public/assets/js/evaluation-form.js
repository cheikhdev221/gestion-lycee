document.addEventListener('DOMContentLoaded', () => {
    const assignment = document.querySelector('#affectation_id');
    const semester = document.querySelector('#semestre_id');

    if (!assignment || !semester) {
        return;
    }

    const options = Array.from(semester.options).slice(1);
    const updateSemesters = () => {
        const yearId = assignment.selectedOptions[0]?.dataset.year;
        let hasSemester = false;

        for (const option of options) {
            const matches = Boolean(yearId) && option.dataset.year === yearId;
            option.hidden = !matches;
            option.disabled = !matches;
            hasSemester ||= matches;
            if (!matches && option.selected) {
                option.selected = false;
            }
        }

        semester.disabled = !yearId || !hasSemester;
    };

    assignment.addEventListener('change', updateSemesters);
    updateSemesters();
});
