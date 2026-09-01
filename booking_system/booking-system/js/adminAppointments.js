const appointmentsContainer = document.getElementById("appointments");
const appointmentForm = document.getElementById("appointmentForm");
const bulkAppointmentForm = document.getElementById("bulkAppointmentForm");
const pageMessage = document.getElementById("message");

let appointmentsCache = [];

loadAppointments();
initFormTabs();
initBreakToggle();
initCharCounter("information", "informationCount", 500);
initCharCounter("bulkInformation", "bulkInformationCount", 500);

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

function initFormTabs() {
    const tabButtons = document.querySelectorAll(".formTabButton");
    if (tabButtons.length === 0) return;

    tabButtons.forEach((button) => {
        button.addEventListener("click", () => {
            tabButtons.forEach((b) => b.classList.remove("active"));
            button.classList.add("active");

            document.querySelectorAll(".formTabButton").forEach((b) => {
                const target = document.getElementById(b.dataset.target);
                if (target) target.style.display = b === button ? "" : "none";
            });
        });
    });
}

function initBreakToggle() {
    const checkbox = document.getElementById("bulkUseBreak");
    const fields = document.getElementById("bulkBreakFields");
    if (!checkbox || !fields) return;

    checkbox.addEventListener("change", () => {
        fields.style.display = checkbox.checked ? "" : "none";

        if (!checkbox.checked) {
            document.getElementById("bulkBreakStart").value = "";
            document.getElementById("bulkBreakEnd").value = "";
        }
    });
}

function actionButtons(appointment) {
    const booked = appointment.status === "Bokad" || appointment.status === "Passerad bokad";
    const removable = appointment.status === "Ledig" || appointment.status === "Passerad";
    const editable = appointment.status === "Ledig" || appointment.status === "Bokad";

    let buttons = "";

    if (editable) {
        buttons += `<button class="editButton" data-id="${appointment.id}">Redigera</button>`;
    }

    if (removable) {
        buttons += `<button class="deleteButton" data-id="${appointment.id}">Ta bort</button>`;
    } else if (booked) {
        buttons += `<button class="cancelBookingButton" data-id="${appointment.booking_id}">Avboka bokning</button>`;
    }

    return buttons;
}

function appointmentCard(appointment) {
    const information = appointment.information?.trim()
        ? escapeHtml(appointment.information)
        : "Ingen information";

    return `
        <div class="appointment" data-appointment-id="${appointment.id}" data-status="${appointment.status}">
            <h3>${escapeHtml(appointment.appointment_date)}</h3>
            <p>Tid: ${escapeHtml(appointment.appointment_time)}</p>
            <p>Status: <strong class="statusText">${escapeHtml(appointment.status)}</strong></p>
            <p class="preserveLines">Information: ${information}</p>
            <div class="appointmentActions">${actionButtons(appointment)}</div>
        </div>
    `;
}

// Lediga tider: datum, tid och information kan alla ändras.
// Bokade tider: bara informationstexten kan ändras (datum/tid ligger fast
// eftersom en användare redan bokat just den tiden).
function editForm(appointment) {
    const booked = appointment.status === "Bokad";
    const information = appointment.information ?? "";

    const dateTimeFields = booked ? "" : `
        <label>Datum</label>
        <input type="date" class="editDate" value="${escapeHtml(appointment.appointment_date)}" required>

        <label>Tid</label>
        <input type="time" class="editTime" value="${escapeHtml(appointment.appointment_time.slice(0, 5))}" required>
    `;

    return `
        <div class="appointment editingCard" data-appointment-id="${appointment.id}" data-status="${appointment.status}">
            <h3>Redigerar tid</h3>

            ${booked ? "<p>Datum och tid kan inte ändras på en redan bokad tid.</p>" : ""}

            ${dateTimeFields}

            <label>Information</label>
            <textarea class="editInformation" maxlength="500">${escapeHtml(information)}</textarea>

            <div class="appointmentActions">
                <button class="saveEditButton">Spara</button>
                <button class="cancelEditButton" type="button">Avbryt</button>
            </div>
        </div>
    `;
}

async function loadAppointments() {
    try {
        appointmentsCache = await requestJson("../ajax/adminGetAppointments.php");
        appointmentsContainer.innerHTML = appointmentsCache.length
            ? appointmentsCache.map(appointmentCard).join("")
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
        appointmentsCache.push(result.appointment);
        appointmentsContainer.insertAdjacentHTML("beforeend", appointmentCard(result.appointment));
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        submitButton.disabled = false;
    }
});

if (bulkAppointmentForm) {
    bulkAppointmentForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const submitButton = bulkAppointmentForm.querySelector("button[type='submit'], button:not([type])");
        submitButton.disabled = true;

        try {
            const useBreak = document.getElementById("bulkUseBreak").checked;
            const breakStart = document.getElementById("bulkBreakStart").value;
            const breakEnd = document.getElementById("bulkBreakEnd").value;

            if (useBreak && (!breakStart || !breakEnd)) {
                showMessage("Fyll i både start- och sluttid för pausen, eller avmarkera kryssrutan.", true);
                submitButton.disabled = false;
                return;
            }

            const result = await requestJson("../ajax/createAppointmentsBulk.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    startDate: document.getElementById("bulkStartDate").value,
                    endDate: document.getElementById("bulkEndDate").value,
                    startTime: document.getElementById("bulkStartTime").value,
                    endTime: document.getElementById("bulkEndTime").value,
                    intervalMinutes: Number(document.getElementById("bulkInterval").value),
                    skipWeekends: document.getElementById("bulkSkipWeekends").checked,
                    breakStartTime: useBreak ? breakStart : "",
                    breakEndTime: useBreak ? breakEnd : "",
                    information: document.getElementById("bulkInformation").value.trim()
                })
            });

            showMessage(result.message, result.createdCount === 0);

            if (result.createdCount > 0) {
                bulkAppointmentForm.reset();
                // Flera tider kan spänna över olika datum, så listan hämtas
                // om för att hamna i rätt sorterad ordning direkt.
                await loadAppointments();
            }
        } catch (error) {
            showMessage(error.message, true);
        } finally {
            submitButton.disabled = false;
        }
    });
}

document.addEventListener("click", async (event) => {
    const deleteButton = event.target.closest(".deleteButton");
    const cancelButton = event.target.closest(".cancelBookingButton");
    const editButton = event.target.closest(".editButton");
    const cancelEditButton = event.target.closest(".cancelEditButton");
    const saveEditButton = event.target.closest(".saveEditButton");

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
            const appointmentId = card.dataset.appointmentId;

            const updated = appointmentsCache.find(a => a.id === Number(appointmentId));
            if (updated) {
                updated.status = newStatus;
                updated.booking_id = null;
            }

            card.dataset.status = newStatus;
            card.querySelector(".statusText").textContent = newStatus;
            card.querySelector(".appointmentActions").innerHTML = actionButtons(updated ?? {
                id: appointmentId,
                status: newStatus
            });
        } catch (error) {
            cancelButton.disabled = false;
            showMessage(error.message, true);
        }
    }

    if (editButton) {
        const card = editButton.closest(".appointment");
        const appointmentId = Number(card.dataset.appointmentId);
        const appointment = appointmentsCache.find(a => a.id === appointmentId);

        if (!appointment) return;

        card.outerHTML = editForm(appointment);
    }

    if (cancelEditButton) {
        const card = cancelEditButton.closest(".appointment");
        const appointmentId = Number(card.dataset.appointmentId);
        const appointment = appointmentsCache.find(a => a.id === appointmentId);

        if (!appointment) return;

        card.outerHTML = appointmentCard(appointment);
    }

    if (saveEditButton) {
        const card = saveEditButton.closest(".appointment");
        const appointmentId = Number(card.dataset.appointmentId);
        const dateField = card.querySelector(".editDate");
        const timeField = card.querySelector(".editTime");
        const informationField = card.querySelector(".editInformation");

        saveEditButton.disabled = true;

        try {
            const payload = { id: appointmentId, information: informationField.value.trim() };
            if (dateField) payload.date = dateField.value;
            if (timeField) payload.time = timeField.value;

            const result = await requestJson("../ajax/updateAppointment.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            showMessage(result.message);

            const index = appointmentsCache.findIndex(a => a.id === appointmentId);
            if (index !== -1) appointmentsCache[index] = result.appointment;

            card.outerHTML = appointmentCard(result.appointment);
        } catch (error) {
            saveEditButton.disabled = false;
            showMessage(error.message, true);
        }
    }
});