function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

// Kopplar en liven teckenräknare ("42/500") till en textarea. Visar en
// varningsfärg när man närmar sig gränsen. Används av bokningsmeddelandet
// och admins informationsfält.
function initCharCounter(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    if (!textarea || !counter) return;

    function update() {
        const length = textarea.value.length;
        counter.textContent = `${length}/${maxLength}`;
        counter.classList.toggle("charCount-warning", length >= maxLength * 0.9);
    }

    textarea.addEventListener("input", update);
    update();
}