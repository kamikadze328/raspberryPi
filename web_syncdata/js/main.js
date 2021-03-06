let dataToServer = {duration:localStorage.getItem("duration") ? localStorage.getItem("duration") : "hour"}
let duration = dataToServer.duration
let refreshInfoID = 0

document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);
    getHTMLMainInfoFromServer()
    setEventListenerSettings()
});

function clearUpdaterAndGetTimeout(id) {
    clearInterval(id)
    return 1000 * 60
    /*if (duration === 'hour') return 1000 * 60
    else if (duration === 'week') return 1000 * 60 * 60
    else return (1000) * 60 * 5*/
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
            initCharts()
            setNumberDB(document.getElementsByClassName("db"))
            dateToDeltaHTML(document.querySelectorAll(".delta-time"))
            setNewInfoUpdater()
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
        .then(response => {if(response.ok) return response.json(); else throw response})
        .then(data => {
            setNewInfoUpdater()
            if (data && !data.error)
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
        }).catch(error => console.log(error))
}

function getServerId(serverHost){
    return String(serverHost).replace(/\./g, "")
}

function setNumberDB(htmlPath) {
    const numberDB = htmlPath ? htmlPath.length : 0;
    document.getElementById("number-db").textContent = 'Найдено баз данных: ' + numberDB
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
    let dates = document.querySelectorAll(".delta-time")
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
    elemHTML.className = "delta-time text-" + status
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
    document.getElementById("time").textContent = date.toLocaleString("ru", options)
    updateDeltaTime()

}

function getSettingsListClasses(){
    return document.getElementById("settings-icon").classList
}

function toggleSettingsPanel() {
    let listClassesIcon = getSettingsListClasses()
    if (listClassesIcon.contains("close-icon")) closeSettingsPanel(listClassesIcon)
    else openSettingsPanel(listClassesIcon)
}

function openSettingsPanel(listClassesIcon) {
    listClassesIcon.remove("show-icon")
    listClassesIcon.add("close-icon")
    document.getElementById("settings-button").classList.add("show-button")
    document.getElementById("settings-panel").classList.add("show-settings")
}

function closeSettingsPanel(listClassesIcon) {
    listClassesIcon.remove("close-icon")
    listClassesIcon.add("show-icon")
    document.getElementById("settings-button").classList.remove("show-button")
    document.getElementById("settings-panel").classList.remove("show-settings")
    clearTempSettingsInfo()
}

function chooseDuration(e) {
    duration = e.target.dataset.value
    localStorage.setItem("duration", duration)
    dataToServer.duration = duration

    let durationItems = document.querySelectorAll(".duration-item")
    durationItems.forEach(elem =>
        setChoiceDuration(elem))
    document.getElementById("settings-button").click()

    updateChartsMeta()
    initCharts()
    //updateChart()
    updateMainInfo()
}

function setChoiceDuration(htmlElem) {
    if (htmlElem.dataset.value === duration)
        htmlElem.classList.add("duration-choice")
    else htmlElem.classList.remove("duration-choice")
}

function setEventListenerSettings() {
    document.getElementById("settings-button").addEventListener('click', toggleSettingsPanel)

    let durationItems = document.querySelectorAll(".duration-item")
    durationItems.forEach(elem => {
        elem.addEventListener('click', chooseDuration)
        setChoiceDuration(elem)
    })

    document.getElementById("db-list").addEventListener('click', () => {if (getSettingsListClasses().contains("close-icon")) closeSettingsPanel(getSettingsListClasses())})
    document.getElementById("refresh-button").addEventListener('click', ()=>{updateCharts(); updateMainInfo()})
    document.getElementById("update-db-button").addEventListener('click', updateDB)
}

function updateDB(){
    clearTempSettingsInfo()
    fetch('./php/update_tables.php')
        .then(response => response.json())
        .then(data => {
            let elem = document.getElementById("update-db-info")
            let html = ''
            data.forEach((server)=>{
                html += `<div style="margin: 10px 0; border-radius: 5px" class="${server.status ? 'norm' : 'error'}">${server.host}</div>`
            })
            elem.innerHTML = html
            elem.parentElement.classList.add("border-bottom")
        })
        .catch(error => console.log(error))
}

function clearTempSettingsInfo() {
    document.getElementById("update-db-info").innerHTML = ''
    document.getElementById("update-db-info").parentElement.classList.remove("border-bottom")
}

function redirectToMain(){
    window.location.replace('http://' + location.hostname)
}

