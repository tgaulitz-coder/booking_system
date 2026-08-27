const appointmentsContainer = document.getElementById("appointments");
const bookingMessageInput = document.getElementById("bookingMessage");
const pageMessage = document.getElementById("message");

loadAppointments();

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
        ? appointment.information
        : "Ingen information";

    return `
        <div class="appointment" data-appointment-id="${appointment.id}">
            <h3>${appointment.appointment_date}</h3>
            <p>Tid: ${appointment.appointment_time}</p>
            <p>Information: ${information}</p>
            <button class="bookButton" data-id="${appointment.id}">Boka</button>
        </div>
    `;
}

async function loadAppointments() {
    try {
        const appointments = await requestJson("ajax/getAppointments.php");

        appointmentsContainer.innerHTML = appointments.length
            ? appointments.map(appointmentCard).join("")
            : "<p>Det finns inga lediga tider.</p>";
    } catch (error) {
        appointmentsContainer.innerHTML = "<p>Kunde inte hämta tiderna.</p>";
        showMessage(error.message, true);
    }
}

document.addEventListener("click", async (event) => {
    const button = event.target.closest(".bookButton");
    if (!button) return;

    button.disabled = true;

    try {
        const result = await requestJson("ajax/book.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                appointmentId: Number(button.dataset.id),
                message: bookingMessageInput.value.trim()
            })
        });

        showMessage(result.message);
        bookingMessageInput.value = "";

        // En användare får bara ha en aktiv bokning. Därför ersätts hela
        // listan direkt i DOM:en utan en ny hämtning eller sidomladdning.
        appointmentsContainer.innerHTML = `
            <div class="appointment">
                <h3>Bokningen är klar</h3>
                <p>Du kan se eller avboka tiden under Mina bokningar.</p>
                <a href="mybookings.php">Gå till Mina bokningar</a>
            </div>
        `;
    } catch (error) {
        button.disabled = false;
        showMessage(error.message, true);
    }
});
