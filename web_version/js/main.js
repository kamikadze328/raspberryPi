let dataToServer = {duration:localStorage.getItem("duration") ? localStorage.getItem("duration") : "hour"}
let duration = dataToServer.duration
let refreshInfoID = 0
document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);
    getHTMLMainInfoFromServer()
});

function clearUpdaterAndGetTimeout(id){
    clearInterval(id)
    if (duration === 'hour') return 1000 * 10
    else if (duration === 'week') return 1000 * 60 * 60
    else return (1000) * 60
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
            'Accept':'text/html'},
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
        });
}
function updateMainInfo(){
    fetch('./php/servers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':'application/json'
        },
        body: JSON.stringify(dataToServer),
    })
        .then(response => response.json())
        .then(data => {
            setNewInfoUpdater()
            console.log(data)
        })
}

function setNumberDB(htmlPath){
    const numberDB = htmlPath ? htmlPath.length : 0;
    document.querySelector(".header :first-child").textContent = 'List of ' + numberDB + ' DBs'
}
function dateToDeltaHTML(htmlCollectionDates) {
    for (let date of htmlCollectionDates)
        date.textContent = dateToDelta(String(date.textContent))
}
function dateToDelta(str_date){
    if (str_date !== "NAN") {
        const delta = new Date - new Date(Date.parse(str_date))
        return  Math.round(delta / 1000)
    } else return str_date
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
function setChoiceDuration(htmlElem){
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
