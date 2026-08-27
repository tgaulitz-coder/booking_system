const bookingsContainer = document.getElementById("bookings");

loadBookings();

async function requestJson(url, options = {}) {
    const response = await fetch(url, options);
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

function bookingCard(booking) {
    return `
        <div class="booking appointment" data-booking-id="${booking.booking_id}">
            <h3>${booking.name}</h3>
            <p>Användarnamn: ${booking.username}</p>
            <p>E-post: ${booking.email}</p>
            <p>Datum: ${booking.appointment_date}</p>
            <p>Tid: ${booking.appointment_time}</p>
            <p>Information om tiden:<br>${booking.information?.trim() || "Ingen information"}</p>
            <p>Användarens meddelande:<br>${booking.message?.trim() || "Inget meddelande"}</p>
            <button class="cancelBookingButton" data-id="${booking.booking_id}">Avboka bokning</button>
        </div>
    `;
}

async function loadBookings() {
    try {
        const bookings = await requestJson("../ajax/adminGetBookings.php");
        bookingsContainer.innerHTML = bookings.length
            ? bookings.map(bookingCard).join("")
            : "<p>Det finns inga bokningar.</p>";
    } catch (error) {
        bookingsContainer.innerHTML = `<p>${error.message}</p>`;
    }
}

document.addEventListener("click", async (event) => {
    const button = event.target.closest(".cancelBookingButton");
    if (!button) return;

    if (!confirm("Vill du avboka denna bokning?")) return;

    button.disabled = true;
    const card = button.closest(".booking");

    try {
        await requestJson("../ajax/adminCancelBooking.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ bookingId: Number(button.dataset.id) })
        });

        card.remove();
        if (!bookingsContainer.querySelector(".booking")) {
            bookingsContainer.innerHTML = "<p>Det finns inga bokningar.</p>";
        }
    } catch (error) {
        button.disabled = false;
        alert(error.message);
    }
});
