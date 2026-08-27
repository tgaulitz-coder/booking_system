const bookingsContainer = document.getElementById("bookings");
const pageMessage = document.getElementById("message");

loadBookings();

async function requestJson(url, options = {}) {
    const response = await fetch(url, {
        cache: "no-store",
        ...options
    });
    const text = await response.text();

    let data;
    try {
        data = JSON.parse(text);
    } catch {
        throw new Error("Servern skickade ett ogiltigt svar.");
    }

    if (!response.ok) {
        throw new Error(data.message || "Ett serverfel uppstod.");
    }

    return data;
}

function showMessage(text, isError = false) {
    pageMessage.textContent = text;
    pageMessage.className = isError ? "message error" : "message success";
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function bookingCard(booking) {
    const bookingId = Number(booking.booking_id ?? booking.id);
    const information = booking.information?.trim() || "Ingen information";
    const message = booking.message?.trim() || "Inget meddelande";

    if (!Number.isInteger(bookingId) || bookingId <= 0) {
        console.error("Bokningen saknar giltigt ID:", booking);
        return "";
    }

    return `
        <div class="appointment" data-booking-id="${bookingId}">
            <h3>${escapeHtml(booking.appointment_date)}</h3>
            <p>Tid: ${escapeHtml(booking.appointment_time)}</p>
            <p>Information: ${escapeHtml(information)}</p>
            <p>Ditt meddelande: ${escapeHtml(message)}</p>
            <button type="button" class="cancelButton" data-booking-id="${bookingId}">Avboka</button>
        </div>
    `;
}

async function loadBookings() {
    try {
        const bookings = await requestJson("ajax/getMyBookings.php");
        const cards = bookings.map(bookingCard).filter(Boolean);

        bookingsContainer.innerHTML = cards.length
            ? cards.join("")
            : "<p>Du har inga bokade tider.</p>";
    } catch (error) {
        bookingsContainer.innerHTML = "<p>Kunde inte hämta bokningarna.</p>";
        showMessage(error.message, true);
    }
}

document.addEventListener("click", async (event) => {
    const button = event.target.closest(".cancelButton");
    if (!button) return;

    const bookingId = Number(button.dataset.bookingId);
    if (!Number.isInteger(bookingId) || bookingId <= 0) {
        showMessage("Bokningen saknar ett giltigt boknings-ID.", true);
        return;
    }

    if (!confirm("Vill du verkligen avboka tiden?")) return;

    button.disabled = true;
    const card = button.closest(".appointment");

    try {
        const result = await requestJson("ajax/cancelBooking.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ booking_id: bookingId })
        });

        showMessage(result.message);
        card?.remove();

        if (!bookingsContainer.querySelector(".appointment")) {
            bookingsContainer.innerHTML = "<p>Du har inga bokade tider.</p>";
        }
    } catch (error) {
        button.disabled = false;
        showMessage(error.message, true);
    }
});
