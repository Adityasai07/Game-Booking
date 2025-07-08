const fromInput = document.getElementById("fromDate");
const toInput = document.getElementById("toDate");
const dateList = document.getElementById("dateList");

let selectedDates = new Set();

function isWeekend(date) {
    const day = date.getDay();
    return day === 0 || day === 1;
}

function formatDate(d) {
    return d.toISOString().split('T')[0];
}

function generateDateButtons(start, end) {
    dateList.innerHTML = "";

    const today = new Date();
    today.setHours(0, 0, 0, 0); 

    for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
        dt.setHours(0, 0, 0, 0);
        if (dt < today || isWeekend(dt)) {
            continue;
        }

        const dateStr = formatDate(dt);
        const btn = document.createElement("button");
        btn.textContent = dateStr;
        btn.classList.add("date-btn");
        btn.dataset.date = dateStr;
        btn.onclick = () => toggleDate(btn);

        if (selectedDates.has(dateStr)) {
            btn.classList.add("selected");
        }

        dateList.appendChild(btn);
    }

    updateSelectedDatesDisplay(); 
}


function toggleDate(btn) {
    const date = btn.dataset.date;

    if (selectedDates.has(date)) {
        selectedDates.delete(date);
        btn.classList.remove("selected");
    } else {
        selectedDates.add(date);
        btn.classList.add("selected");
    }

    updateSelectedDatesDisplay();
}

function selectRange() {
    const from = new Date(fromInput.value);
    from.setDate(from.getDate() + 1); 
    const to = new Date(toInput.value);
    to.setDate(to.getDate() + 1); 

    if (!fromInput.value || !toInput.value || from > to) {
        showMessage("Please enter a valid date range.");
        return;
    }
    generateDateButtons(from, to);
}

function deselectAll() {
    selectedDates.clear();
    document.querySelectorAll(".date-btn").forEach(btn => btn.classList.remove("selected"));
    updateSelectedDatesDisplay();
}

function selectAll() {
    document.querySelectorAll(".date-btn").forEach(btn => {
        const date = btn.dataset.date;
        if (!selectedDates.has(date)) {
            selectedDates.add(date);
            btn.classList.add("selected");
        }
    });
    updateSelectedDatesDisplay();
}

function updateSelectedDatesDisplay() {
    const display = document.getElementById("selectedDatesDisplay");
    display.innerHTML = "";

    const sortedDates = Array.from(selectedDates).sort();
    sortedDates.forEach(date => {
        const span = document.createElement("span");
        span.textContent = date;
        span.className = "bg-blue-200 px-2 py-1 rounded-md";
        display.appendChild(span);
    });
}


function submitDates() {
    const gameId = localStorage.getItem("gameId");
    if (!gameId || selectedDates.size === 0) {
        showMessage("No game ID or dates selected.");
        return;
    }

    fetch("add_dates.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            game_id: gameId,
            dates: Array.from(selectedDates)
        })
    })
    .then(res => res.text())
    .then(response => {
        showMessage("Dates and time slots saved successfully.");
        console.log(response);
    })
    .catch(error => {
        console.error("Failed to save dates:", error);
        showMessage("Error saving dates.");
    });
}


function goToDateSection() {
    const form = document.getElementById("gameForm");
    const gamename = form.querySelector('input[name="gamename"]').value.trim();
    const description = form.querySelector('textarea[name="description"]').value.trim();

    if (!gamename || !description) {
        showMessage("Please fill in game name and description.");
        return;
    }

    document.getElementById("gameDetailsSection").style.display = "none";
    document.getElementById("dateSection").style.display = "block";
}

function goToGameSection() {
    document.getElementById("dateSection").style.display = "none";
    document.getElementById("gameDetailsSection").style.display = "block";
}


function submitFinal() {
    const form = document.getElementById("gameForm");
    const formData = new FormData(form);

    if (selectedDates.size === 0) {
        showMessage("Please select at least one date.");
        return;
    }

    fetch("add_game.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(gameId => {
        if (!gameId || isNaN(gameId)) {
            showMessage("Error saving game.");
            return;
        }

        return fetch("add_dates.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                game_id: gameId,
                dates: Array.from(selectedDates)
            })

            //const gameId = 7; const selectedDates = new Set(["2025-07-27"]);
            //{"game_id": 7,"dates": ["2025-07-01", "2025-07-02"]}


        });
    })
    .then(res => res.text())
    .then(response => {
        showMessage("Game and dates saved successfully.");
        console.log(response);
        form.reset();
        deselectAll();
        dateList.innerHTML = "";
        document.getElementById("gameDetailsSection").style.display = "block";
        document.getElementById("dateSection").style.display = "none";
    })
    .catch(err => {
        console.error("Submission failed:", err);
        showMessage("Error submitting data.");
    });
}



document.getElementById('bookingsLink').addEventListener('click', function (e) {
    e.preventDefault();

    fetch('session.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'bookings.html';
            } else {
                showMessage('Please login to view bookings.', 'error');
            }
        })
        .catch(() => {
            showMessage('Error verifying session. Try again.', 'error');
        });
});


console.log("Admin js running");



function showMessage(text, type = "info") {
    const template = document.getElementById("messageTemplate");
    const clone = template.content.cloneNode(true);
    const box = clone.querySelector(".message");
    const msgText = clone.querySelector(".message-text");

    msgText.textContent = text;

    if (type === "error") {
        box.className = "message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-lg mb-4";
    } else if (type === "success") {
        box.className = "message bg-blue-600 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-lg mb-4";
    }

    const container = document.getElementById("messageBox");
    const messageNode = box;
    container.appendChild(messageNode);

    setTimeout(() => {
        messageNode.remove();
    }, 5000);
}





fetch('session.php')
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! Status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        const userMessage = document.getElementById('userMessage');
        const loginLink = document.getElementById('loginLink');
        const signupLink = document.getElementById('signupLink');
        const logoutForm = document.getElementById('logoutForm');

        console.log(data.username);

        if (data.success) {
            console.log(data.username);
            userMessage.textContent = `Welcome , ${data.username}!`;
            loginLink.style.display = 'none';
            signupLink.style.display = 'none';
            logoutForm.style.display = 'block';
        } else {
            userMessage.textContent = 'Please login';
            loginLink.style.display = 'inline-block';
            signupLink.style.display = 'inline-block';
            logoutForm.style.display = 'none';
        }
    })
    .catch(err => {
        console.error('Failed to fetch session data:', err);
        document.getElementById('userMessage').textContent = 'Please login';
        document.getElementById('loginLink').style.display = 'inline-block';
        document.getElementById('signupLink').style.display = 'inline-block';
        document.getElementById('logoutForm').style.display = 'none';
    }
);


document.addEventListener("DOMContentLoaded", () => {
    const userSelect = document.getElementById("userSelect");
    const gameSelect = document.getElementById("gameSelect");
    const dateSelect = document.getElementById("dateSelect");
    const slotSelect = document.getElementById("slotSelect");
    const submitBtn = document.getElementById("submitManualBooking");

    // Fetch users
    fetch('fetch_users.php')
        .then(res => res.json())
        .then(users => {
            users.forEach(user => {
                const opt = document.createElement("option");
                opt.value = user.id;
                opt.textContent = user.username;
                userSelect.appendChild(opt);
            });
        });

    // Fetch games
    fetch('fetch_games.php')
        .then(res => res.json())
        .then(games => {
            games.forEach(game => {
                const opt = document.createElement("option");
                opt.value = game.id;
                opt.textContent = game.gamename;
                gameSelect.appendChild(opt);
            });
        });

    // On game select → load dates
    gameSelect.addEventListener("change", () => {
        const gameId = gameSelect.value;
        dateSelect.innerHTML = `<option value="">-- Select Date --</option>`;
        slotSelect.innerHTML = `<option value="">-- Select Slot --</option>`;
        submitBtn.disabled = true;

        if (!gameId) return;

        fetch(`fetch_game_dates.php?game_id=${gameId}`)
            .then(res => res.json())
            .then(dates => {
                console.log("Games fetched ");
                if (dates.length === 0) {
                    const opt = document.createElement("option");
                    opt.value = "";
                    opt.textContent = "No available dates";
                    dateSelect.appendChild(opt);
                } else {
                    dates.forEach(d => {
                        const opt = document.createElement("option");
                        opt.value = d.id;
                        opt.textContent = d.game_date;
                        console.log("Game" + d.game_date);
                        dateSelect.appendChild(opt);
                    });
                }
            });
    });

    // On date select → load available slots
    dateSelect.addEventListener("change", () => {
        const dateId = dateSelect.value;
        slotSelect.innerHTML = `<option value="">-- Select Slot --</option>`;
        submitBtn.disabled = true;

        if (!dateId) return;

        fetch(`fetch_available_slots.php?game_date_id=${dateId}`)
            .then(res => res.json())
            .then(slots => {
                if (slots.length === 0) {
                    const opt = document.createElement("option");
                    opt.value = "";
                    opt.textContent = "No available slots";
                    slotSelect.appendChild(opt);
                } else {
                    slots.forEach(s => {
                        const opt = document.createElement("option");
                        opt.value = s.id;
                        opt.textContent = s.slot_time;
                        slotSelect.appendChild(opt);
                    });
                }
            });
    });

    slotSelect.addEventListener("change", () => {
        submitBtn.disabled = !(slotSelect.value && dateSelect.value && userSelect.value && gameSelect.value);
    });

    document.getElementById("manualBookingForm").addEventListener("submit", e => {
        e.preventDefault();

        const data = new FormData(e.target);

        fetch('insert_manual_booking.php', {
            method: "POST",
            body: data
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message);
                e.target.reset();
                submitBtn.disabled = true;
            }else{
                showMessage(data.message , "error");    
            }
        })
        .catch(err => {
            console.error("Error:", err);
            showMessage("Something went wrong. Please try again." , "error");
        });
    });



});
