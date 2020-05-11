

document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);

    fetch('php/servers.php')
        .then(response => response.text())
        .then(data => {
            try {
                data = JSON.stringify(JSON.parse(data), null, 4)
            } catch (e) {
            }
            console.log(data);
            document.getElementById("db-list").innerHTML = data;
            let dbs = document.getElementsByClassName("db");
            dbs = dbs ? dbs.length : 0;
            document.querySelector(".header :first-child").textContent = 'List of ' + dbs + ' DBs'
            dateToDelta()
            charts({duration:'hour'})
        });

});


function dateToDelta() {
    let dates = document.querySelectorAll(".db-last-conn > :last-child")
    for (let date of dates) {
        const str_date = String(date.textContent)
        if (str_date !== "NAN") {
            const delta = new Date - new Date(Date.parse(str_date))
            date.textContent = Math.round(delta / 1000)
        }
    }
}

function updateDeltaTime() {
    let dates = document.querySelectorAll(".db-last-conn > :last-child")
    for (let date of dates) {
        let str_delta = String(date.textContent)
        if (str_delta !== "NAN")
            date.textContent = secToHms(hmsToSecondsOnly(str_delta) + 1)
    }
}
function secToHms(sec_num) {
    let hours = Math.floor(sec_num / 3600);
    let minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    let seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours > 0)
        if (hours < 10)
            hours = "0" + hours;
    hours = (hours > 0) ? (hours + ':') : ""

    if (minutes > 0)
        if (minutes < 10)
            minutes = "0" + minutes;
    minutes = (minutes > 0) ? (minutes + ':') : ""

    if (seconds < 10)
        seconds = "0" + seconds;

    return hours + minutes + seconds;
}
function hmsToSecondsOnly(str) {
    let p = str.split(':'),
        s = 0, m = 1;
    while (p.length > 0) {
        s += m * parseInt(p.pop(), 10);
        m *= 60;
    }
    return s;
}

function updateTime() {
    const date = new Date()
    const options = {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
        timezone: 'UTC',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }
    document.querySelector(".header :last-child").textContent = date.toLocaleString("ru", options)
    updateDeltaTime()

}
