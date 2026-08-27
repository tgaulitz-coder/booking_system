const appointmentsContainer = document.getElementById("appointments");
const appointmentForm = document.getElementById("appointmentForm");
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
    const booked = appointment.status === "Bokad" || appointment.status === "Passerad bokad";
    const removable = appointment.status === "Ledig" || appointment.status === "Passerad";

    let action = "";
    if (removable) {
        action = `<button class="deleteButton" data-id="${appointment.id}">Ta bort</button>`;
    } else if (booked) {
        action = `<button class="cancelBookingButton" data-id="${appointment.booking_id}">Avboka bokning</button>`;
    }

    const information = appointment.information?.trim()
        ? escapeHtml(appointment.information)
        : "Ingen information";

    return `
        <div class="appointment" data-appointment-id="${appointment.id}" data-status="${appointment.status}">
            <h3>${escapeHtml(appointment.appointment_date)}</h3>
            <p>Tid: ${escapeHtml(appointment.appointment_time)}</p>
            <p>Status: <strong class="statusText">${escapeHtml(appointment.status)}</strong></p>
            <p>Information: ${information}</p>
            <div class="appointmentActions">${action}</div>
        </div>
    `;
}

async function loadAppointments() {
    try {
        const appointments = await requestJson("../ajax/adminGetAppointments.php");
        appointmentsContainer.innerHTML = appointments.length
            ? appointments.map(appointmentCard).join("")
            : "<p>Det finns inga tider.</p>";
    } catch (error) {
        appointmentsContainer.innerHTML = "<p>Kunde inte hämta tiderna.</p>";
        showMessage(error.message, true);
    }
}

appointmentForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const submitButton = appointmentForm.querySelector("button[type='submit'], button:not([type])");
    submitButton.disabled = true;

    try {
        const result = await requestJson("../ajax/createAppointment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                date: document.getElementById("date").value,
                time: document.getElementById("time").value,
                information: document.getElementById("information").value.trim()
            })
        });

        showMessage(result.message);
        appointmentForm.reset();

        // Lägg in den nya tiden direkt utan att hämta om listan.
        if (!appointmentsContainer.querySelector(".appointment")) {
            appointmentsContainer.innerHTML = "";
        }
        appointmentsContainer.insertAdjacentHTML("beforeend", appointmentCard(result.appointment));
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        submitButton.disabled = false;
    }
});

document.addEventListener("click", async (event) => {
    const deleteButton = event.target.closest(".deleteButton");
    const cancelButton = event.target.closest(".cancelBookingButton");

    if (deleteButton) {
        const card = deleteButton.closest(".appointment");
        deleteButton.disabled = true;

        try {
            const result = await requestJson("../ajax/deleteAppointment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: Number(deleteButton.dataset.id) })
            });

            showMessage(result.message);
            card.remove();

            if (!appointmentsContainer.querySelector(".appointment")) {
                appointmentsContainer.innerHTML = "<p>Det finns inga tider.</p>";
            }
        } catch (error) {
            deleteButton.disabled = false;
            showMessage(error.message, true);
        }
    }

    if (cancelButton) {
        if (!confirm("Vill du avboka denna bokning?")) return;

        const card = cancelButton.closest(".appointment");
        cancelButton.disabled = true;

        try {
            const result = await requestJson("../ajax/adminCancelBooking.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ bookingId: Number(cancelButton.dataset.id) })
            });

            showMessage(result.message);

            // Bokningen är borta men själva tiden finns kvar. Uppdatera bara kortet.
            const newStatus = card.dataset.status === "Passerad bokad" ? "Passerad" : "Ledig";
            card.dataset.status = newStatus;
            card.querySelector(".statusText").textContent = newStatus;
            card.querySelector(".appointmentActions").innerHTML =
                `<button class="deleteButton" data-id="${card.dataset.appointmentId}">Ta bort</button>`;
        } catch (error) {
            cancelButton.disabled = false;
            showMessage(error.message, true);
        }
    }
});
