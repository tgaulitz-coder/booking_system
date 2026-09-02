const appointmentsContainer = document.getElementById("appointments");
const bookingMessageInput = document.getElementById("bookingMessage");
const pageMessage = document.getElementById("message");
const currentBookingContainer = document.getElementById("currentBooking");
const bookingSection = document.getElementById("bookingSection");

init();
initCharCounter("bookingMessage", "bookingMessageCount", 500);

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

function showMessage(text, isError = false) {
    pageMessage.textContent = text;
    pageMessage.className = isError ? "message error" : "message success";
}

function appointmentCard(appointment) {
    const information = appointment.information?.trim()
        ? escapeHtml(appointment.information)
        : "Ingen information";

    return `
        <div class="appointment" data-appointment-id="${appointment.id}">
            <h3>${escapeHtml(appointment.appointment_date)}</h3>
            <p>Tid: ${escapeHtml(appointment.appointment_time)}</p>
            <p class="preserveLines">Information: ${information}</p>
            <button class="bookButton" data-id="${appointment.id}">Boka</button>
        </div>
    `;
}

function currentBookingCard(booking) {
    const information = booking.information?.trim()
        ? escapeHtml(booking.information)
        : "Ingen information";
    const message = booking.message?.trim()
        ? escapeHtml(booking.message)
        : "Inget meddelande";

    return `
        <div class="appointment" data-status="Bokad">
            <h3>Du har redan en bokad tid</h3>
            <p>Datum: ${escapeHtml(booking.appointment_date)}</p>
            <p>Tid: ${escapeHtml(booking.appointment_time)}</p>
            <p class="preserveLines">Information: ${information}</p>
            <p class="preserveLines">Ditt meddelande: ${message}</p>
            <div class="btnGroup">
                <a class="btn" href="mybookings.php">Gå till Mina bokningar för att avboka</a>
            </div>
        </div>
    `;
}

// En användare får bara ha en aktiv bokning. Kolla detta innan de
// lediga tiderna visas, så man slipper klicka "Boka" i onödan.
async function init() {
    try {
        const [myBookings, appointments] = await Promise.all([
            requestJson("ajax/getMyBookings.php"),
            requestJson("ajax/getAppointments.php")
        ]);

        if (myBookings.length > 0) {
            currentBookingContainer.innerHTML = currentBookingCard(myBookings[0]);
            bookingSection.style.display = "none";
            return;
        }

        renderAppointments(appointments);
    } catch (error) {
        appointmentsContainer.innerHTML = "<p>Kunde inte hämta tiderna.</p>";
        showMessage(error.message, true);
    }
}

function renderAppointments(appointments) {
    appointmentsContainer.innerHTML = appointments.length
        ? appointments.map(appointmentCard).join("")
        : "<p>Det finns inga lediga tider.</p>";
}

document.addEventListener("click", async (event) => {
    const button = event.target.closest(".bookButton");
    if (!button) return;

    button.disabled = true;

    try {
        const result = await requestJson("ajax/book.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-Token": getCsrfToken() },
            body: JSON.stringify({
                appointmentId: Number(button.dataset.id),
                message: bookingMessageInput.value.trim()
            })
        });

        showMessage(result.message);
        bookingMessageInput.value = "";

        // En användare får bara ha en aktiv bokning. Därför ersätts hela
        // listan direkt i DOM:en utan en ny hämtning eller sidomladdning.
        bookingSection.style.display = "none";
        currentBookingContainer.innerHTML = `
            <div class="appointment" data-status="Bokad">
                <h3>Bokningen är klar</h3>
                <p>Du kan se eller avboka tiden under Mina bokningar.</p>
                <div class="btnGroup">
                    <a class="btn" href="mybookings.php">Gå till Mina bokningar</a>
                </div>
            </div>
        `;
    } catch (error) {
        button.disabled = false;
        showMessage(error.message, true);
    }
});