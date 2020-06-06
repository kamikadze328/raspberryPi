let dataToServer = {duration:localStorage.getItem("duration") ? localStorage.getItem("duration") : "hour"}
let duration = dataToServer.duration

document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);
    setEventListeners()
    //getHTMLMainInfoFromServer()
    setEventListenerSettings()
});

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
}

function toggleVisibility(e){
    const elem = e.target.nextSibling.nextSibling
    elem.style.display = elem.style.display === "block" ? "none" : "block"

}


function setEventListeners() {

    document.querySelectorAll(".select-clickable").forEach(elem => elem.addEventListener('click', toggleVisibility))

    document.getElementById("db-list").addEventListener('click', (e) => {
        document.querySelectorAll(".select-items").forEach(elem => {
            if (!e.target.classList.contains('select-clickable') && e.target.parentNode.tagName !== 'LABEL' && e.target.tagName !== 'LABEL')
                elem.style.display = "none"
        })
    })

    document.querySelectorAll('.select-box>input').forEach(elem=>elem.addEventListener('input', filterSelectorItems))
    document.querySelectorAll("#select-temperature-items input[type=checkbox]")
}

function filterSelectorItems(e) {
    const userText = e.target.value.toLowerCase()
    const items = e.target.nextSibling.nextSibling.children
    if (userText && userText.length > 0)
        for (let i = 0; i < items.length; i++)
            items[i].style.display = (items[i].outerText.toLocaleLowerCase().indexOf(userText) > -1) ? 'flex' : 'none'
    else
        for (let i = 0; i < items.length; i++)
            items[i].style.display = 'flex'

}


//Settings
function setEventListenerSettings() {
    document.getElementById("settings-button").addEventListener('click', toggleSettingsPanel)

    let durationItems = document.querySelectorAll(".duration-item")
    durationItems.forEach(elem => {
        elem.addEventListener('click', chooseDuration)
        setChoiceDuration(elem)
    })

    document.getElementById("db-list").addEventListener('click', () => {
        if (getSettingsListClasses().contains("close-icon"))
            closeSettingsPanel(getSettingsListClasses())
    })
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
    //clearTempSettingsInfo()
}

function chooseDuration(e) {
    duration = e.target.textContent.toLowerCase()
    localStorage.setItem("duration", duration)
    dataToServer.duration = e.target.textContent.toLowerCase()

    document.querySelectorAll(".duration-item").forEach(elem => setChoiceDuration(elem))
    document.getElementById("settings-button").click()

    /*updateChartsMeta()
    initCharts()
    //updateChart()
    updateMainInfo()*/
}

function setChoiceDuration(htmlElem) {
    if (htmlElem.textContent.toLowerCase() === duration)
        htmlElem.classList.add("duration-choice")
    else htmlElem.classList.remove("duration-choice")
}

function getSettingsListClasses(){
    return document.getElementById("settings-icon").classList
}

function redirectToMain(){
    window.location.replace('http://' + location.hostname)
}

