const bookingStatusContainer = document.getElementById("bookingStatus");
const adminStatsContainer = document.getElementById("adminStats");

init();

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

function bookingStatusCard(booking) {
    if (!booking) {
        return `
            <div class="appointment" data-status="Ledig">
                <h3>Du har ingen bokad tid</h3>
                <p>Boka en ledig tid när det passar dig.</p>
                <div class="btnGroup">
                    <a class="btn" href="appointments.php">Boka tid →</a>
                </div>
            </div>
        `;
    }

    const information = booking.information?.trim()
        ? escapeHtml(booking.information)
        : "Ingen information";

    return `
        <div class="appointment" data-status="Bokad">
            <h3>Din bokade tid</h3>
            <p>Datum: ${escapeHtml(booking.appointment_date)}</p>
            <p>Tid: ${escapeHtml(booking.appointment_time)}</p>
            <p class="preserveLines">Information: ${information}</p>
            <div class="btnGroup">
                <a class="btn btn-secondary" href="mybookings.php">Visa eller avboka</a>
            </div>
        </div>
    `;
}

function adminStatsCard(stats) {
    return `
        <div class="statsRow">
            <div class="statCard">
                <span class="statNumber">${stats.availableAppointments}</span>
                <span class="statLabel">Lediga tider</span>
            </div>
            <div class="statCard">
                <span class="statNumber">${stats.totalBookings}</span>
                <span class="statLabel">Bokningar totalt</span>
            </div>
        </div>
    `;
}

async function init() {
    try {
        const bookings = await requestJson("ajax/getMyBookings.php");
        bookingStatusContainer.innerHTML = bookingStatusCard(bookings[0] ?? null);
    } catch (error) {
        bookingStatusContainer.innerHTML = "<p>Kunde inte hämta bokningsstatus.</p>";
    }

    if (!adminStatsContainer) return;

    try {
        const stats = await requestJson("ajax/adminGetStats.php");
        adminStatsContainer.innerHTML = adminStatsCard(stats);
    } catch (error) {
        adminStatsContainer.innerHTML = "";
    }
}