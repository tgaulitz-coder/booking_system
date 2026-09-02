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

// Läser CSRF-token från <meta name="csrf-token">-taggen i <head>. Token
// skickas med som header vid alla ändrande AJAX-anrop (POST) och kollas
// mot sessionen på servern via requireCsrf() i auth.php.
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : "";
}
