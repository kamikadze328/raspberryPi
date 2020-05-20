let dataToServer = {duration:localStorage.getItem("duration") ? localStorage.getItem("duration") : "hour"}
let duration = dataToServer.duration
let refreshInfoID = 0


document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);
    getHTMLMainInfoFromServer()
});

function clearUpdaterAndGetTimeout(id) {
    clearInterval(id)
    if (duration === 'hour') return 1000 * 60
    else if (duration === 'week') return 1000 * 60 * 60
    else return (1000) * 60 * 5
}

function setNewInfoUpdater() {
    const timeout = clearUpdaterAndGetTimeout(refreshInfoID);
    refreshInfoID = window.setInterval(updateMainInfo, timeout);
}

function getHTMLMainInfoFromServer() {
    fetch('./php/servers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'text/html'
        },
        body: JSON.stringify(dataToServer),
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById("db-list").innerHTML = data;
            charts(dataToServer)

            setNumberDB(document.getElementsByClassName("db"))
            dateToDeltaHTML(document.querySelectorAll(".db-last-conn > :last-child"))
            setNewInfoUpdater()
            setEventListenerSettings()
        }).catch(error => console.log(error))
}

function updateMainInfo() {
    fetch('./php/servers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(dataToServer),
    })
        .then(response => response.json())
        .then(data => {
            setNewInfoUpdater()
            console.log(data)
            if (!data.error)
                data.forEach(server => {
                    const serverID = getServerId(server.host)

                    const avg_con = document.getElementById("val-avg-con-" + serverID)
                    avg_con.textContent = server.avg_time_con.value
                    avg_con.className = "text-" + server.avg_time_con.status
                    document.getElementById("circle-avg-con-" + serverID).className = "circle " + server.avg_time_con.status

                    const avg_upld = document.getElementById("val-avg-upld-" + serverID)
                    avg_upld.textContent = server.avg_time_upld.value
                    avg_upld.className = "text-" + server.avg_time_upld.status
                    document.getElementById("circle-avg-upld-" + serverID).className = "circle " + server.avg_time_upld.status

                    const num_err = document.getElementById("val-num-err-" + serverID)
                    num_err.textContent = server.num_err.value
                    num_err.className = "text-" + server.num_err.status
                    document.getElementById("circle-num-err-" + serverID).className = "circle " + server.num_err.status

                    document.getElementById("val-date-" + serverID).textContent = secToHms(dateToDelta(String(server.last_con)))
                })
        })
}

function getServerId(serverHost){
    return String(serverHost).replace(/\./g, "")
}

function setNumberDB(htmlPath) {
    const numberDB = htmlPath ? htmlPath.length : 0;
    document.querySelector(".header :first-child").textContent = 'List of ' + numberDB + ' DBs'
}

function dateToDeltaHTML(htmlCollectionDates) {
    for (let date of htmlCollectionDates)
        date.textContent = secToHms(dateToDelta(String(date.textContent)))
}

function dateToDelta(str_date) {
    if (str_date !== "NAN") {
        const delta = new Date - new Date(Date.parse(str_date))
        return Math.round(delta / 1000)
    } else return str_date
}

function updateDeltaTime() {
    let dates = document.querySelectorAll(".db-last-conn > :last-child")
    for (let dateHTML of dates) {
        let str_delta = String(dateHTML.textContent)
        if (str_delta !== "NAN") {
            const seconds = hmsToSecondsOnly(str_delta) + 1
            dateHTML.textContent = secToHms(seconds)
            setStatusTime(seconds, dateHTML)
        } else setStatusTime(null, dateHTML)
    }
}

function setStatusTime(seconds, elemHTML) {
    let status = "error"
    seconds = Number(seconds)
    if (seconds > 0 && seconds < 300)
        status = "norm"
    else if (seconds >= 300 && seconds < 3600)
        status = "warn"
    elemHTML.className = "text-" + status
    elemHTML.parentElement.firstElementChild.firstElementChild.className = "circle " + status
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

function settingsPanel() {
    let classListIcon = this.getElementsByClassName("settings-icon")[0].classList
    if (classListIcon.contains("close-icon")) {
        classListIcon.remove("close-icon")
        classListIcon.add("show-icon")
        this.parentElement.classList.remove("show-settings")
    } else {
        classListIcon.remove("show-icon")
        classListIcon.add("close-icon")
        this.parentElement.classList.add("show-settings")
    }
}

function chooseDuration(e) {
    duration = e.target.textContent.toLowerCase()
    localStorage.setItem("duration", duration)
    dataToServer.duration = e.target.textContent.toLowerCase()

    let durationItems = document.querySelectorAll(".duration-item")
    durationItems.forEach(elem =>
        setChoiceDuration(elem))
    document.querySelector(".settings-button").click()

    updateChart()
    updateMainInfo()
}

function setChoiceDuration(htmlElem) {
    if (htmlElem.textContent.toLowerCase() === duration)
        htmlElem.classList.add("duration-choice")
    else htmlElem.classList.remove("duration-choice")
}

function setEventListenerSettings() {
    document.querySelector(".settings-button").addEventListener('click', settingsPanel)
    let durationItems = document.querySelectorAll(".duration-item")
    durationItems.forEach(elem => {
        elem.addEventListener('click', chooseDuration)
        setChoiceDuration(elem)
    })
}
