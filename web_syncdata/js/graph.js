let refreshChartId = 0
let margin = ({top: 10, right: 0, bottom: 10, left: 35})
let format_time, deltaForChart, date_one_duration_ago
let charts_data = []
let html_chart = []
let maxDate
let minDate
const ruLocale = d3.timeFormatLocale({
    "dateTime": "%A, %e %B %Y г. %X",
    "date": "%d.%m.%Y",
    "time": "%H:%M:%S",
    "periods": ["AM", "PM"],
    "days": ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
    "shortDays": ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
    "months": ["Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
    "shortMonths": ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"]
})
const format = {
    millisecond: ruLocale.format(".%L"),
    second: ruLocale.format(":%S"),
    minute: ruLocale.format("%H:%M"),
    hour: ruLocale.format("%H:%M"),
    day: ruLocale.format("%d %b (%a)"),
    week: ruLocale.format("%d %b (%a)"),
    month: ruLocale.format("%B"),
    year: ruLocale.format("%Y"),
}


function multiFormat(date) {
    return (d3.timeSecond(date) < date ? format.millisecond
        : d3.timeMinute(date) < date ? format.second
            : d3.timeHour(date) < date ? format.minute
                : d3.timeDay(date) < date ? format.hour
                    : d3.timeMonth(date) < date ? (d3.timeWeek(date) < date ? format.day : format.week)
                        : d3.timeYear(date) < date ? format.month
                            : format.year)(date);
}

function initCharts() {
    duration = dataToServer.duration
    fetch('./php/graph.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(dataToServer),
    })
        .then(response => {if(response.ok) return response.json(); else throw response})
        .then(data => {
            if (data && !data.error) {
                charts_data = []
                updateChartsMeta()
                d3.selectAll("svg").remove()
                data.forEach(server => initChart(server))
                window.removeEventListener("resize", resizeAllCharts)
                window.addEventListener("resize", resizeAllCharts)
                setNewChartUpdater()
            }
        }).catch(error => console.log(error));
}

function updateCharts() {
    fetch('./php/graph.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({duration:dataToServer.duration, minDate: getMinMaxDate()}),
    })
        .then(response => {if(response.ok) return response.json(); else throw response})
        .then(data => {
            if (data && !data.error) {
                updateChartsMeta()
                updateAllChartsData(data)
                redrawAllCharts()
            }
        }).catch(error => console.log(error));
}

function updateAllChartsData(data) {
    data.forEach(server => {
        updateChartData(charts_data.find(chart => {
            return chart.id === "chart-" + getServerId(server.host) ? chart : false
        }), server.data)
    })
}

function updateChartData(chart, newData){
    let i = 0
    newData = prepareData(newData)
    const stopData = date_one_duration_ago(newData[0].date)
    for(i; i < chart.data.length; i++) {
        if (chart.data[i].date.getTime() >= stopData) break;
    }
    chart.data = chart.data.slice(i, chart.data.length - 1)

    if (chart.data.length && newData[0] && newData[0].date - chart.data[chart.data.length - 1].date > deltaForChart)
        chart.data.push({
            data: new Date(newData[0].date.getTime() - 1),
            value: undefined
        })
    chart.data.push(...newData)
}

function setNewChartUpdater() {
    const timeout = clearUpdaterAndGetTimeout(refreshChartId);
    refreshChartId = window.setInterval(updateCharts, timeout);
}

function resizeAllCharts() {
    charts_data.forEach(chart => resizeChart(chart))
}

function redrawAllCharts() {
    charts_data.forEach(chart => reDrawChart(chart))
}

function reDrawChart(chart){
    chart.xScale.domain([minDate, maxDate])
    const maxValue = d3.max(chart.data, d => d.value)
    chart.yScale.domain([0, maxValue ? maxValue : 3]).nice()
    chart.line = d3.line()
        .defined(d => !isNaN(d.value))
        .x(d => chart.xScale(d.date))
        .y(d => chart.yScale(d.value))
    chart.svg.select(".main-line")
        .datum(chart.data.filter(chart.line.defined()))
    chart.svg.select(".empty-line")
        .datum(chart.data)
    resizeChart(chart)
}

function resizeChart(chart) {
    let chartHTML = html_chart[chart.id]
    let width = chartHTML.clientWidth - margin.right - margin.left,
        height = chartHTML.clientHeight - margin.top - margin.bottom;

    chart.xScale.range([margin.left, width - margin.right])
    chart.yScale.range([height - margin.bottom, margin.top])

    chart.svg.select(".x.axis")
        .attr("transform", `translate(0, ${height - margin.bottom})`)
        .call(chart.xAxis.ticks(width / 80 <= 10 ? width / 80 : 10).tickFormat(multiFormat))
    chart.svg.select(".y.axis")
        .attr("transform", `translate(${margin.left},0)`)
        .call(chart.yAxis)

    chart.svg.select(".main-line")
        .attr("d", chart.line);

    chart.svg.select(".empty-line")
        .attr("d", chart.line);
}

function initChart(server) {
    const id = 'chart-' + getServerId(server.host)
    const chartHTML = document.getElementById(id)
    html_chart[id] = chartHTML
    const width = chartHTML.clientWidth - margin.right - margin.left,
        height = chartHTML.clientHeight - margin.top - margin.bottom,
        data = prepareData(server.data)
    console.log(d3.extent(data, d => d.date))
    const xScale = d3.scaleTime()
        .range([margin.left, width - margin.right])
        .domain([minDate, maxDate])
    const maxValue = d3.max(data, d => d.value)

    const yScale = d3.scaleLinear()
        .range([height - margin.bottom, margin.top])
        .domain([0, maxValue ? maxValue : 3]).nice()

    const xAxis = d3.axisBottom()
        .scale(xScale)
        .ticks(width / 80 <= 10 ? width / 80 : 10).tickFormat(multiFormat);
    const yAxis = d3.axisLeft()
        .scale(yScale)
        .ticks(3);

    const line = d3.line()
        .defined(d => !isNaN(d.value))
        .x(d => xScale(d.date))
        .y(d => yScale(d.value))

    const svg = d3.select('#' + id).append('svg')
        .attr("width", '100%')
        .attr("height", '100%')
        .classed("svg-content", true);

    svg.append("g")
        .attr("class", "x axis axisWhite")
        .attr("transform", `translate(0, ${height - margin.bottom})`)
        .call(xAxis)
    svg.append("g")
        .attr("class", "y axis axisWhite")
        .attr("transform", `translate(${margin.left},0)`)
        .call(yAxis);

    svg.append("path")
        .datum(data.filter(line.defined()))
        .attr("class", "main-line")
        .attr("stroke", "#ff5b57")
        .attr("fill", "none")
        .attr("stroke-width", 1.5)
        .attr("stroke-dasharray", 4)
        .attr("d", line)
    svg.append("path")
        .datum(data)
        .attr("class", "empty-line")
        .attr("stroke", "#348fe2")
        .attr("stroke-width", 1.5)
        .attr("fill", "none")
        .attr("d", line)

    charts_data.push({
        id,
        data,
        xScale,
        yScale,
        xAxis,
        yAxis,
        svg,
        line,
    })
}

function prepareData(serverData) {
    if(serverData && serverData.length) {
        let preparedData = []
        let parseDate = d3.timeParse(format_time)
        serverData.forEach(elem => {
            let preparedElem = {
                date: parseDate(elem.date),
                value: (elem.value && elem.value > 0) ? +elem.value : undefined
            }
            const prev = preparedData[preparedData.length - 1]
            if (elem.value && prev && preparedElem.date - prev.date > deltaForChart)
                preparedData.push({
                    date: new Date(prev.date.getTime() + 1),
                    value: undefined
                })
            preparedData.push(preparedElem)
        }, serverData)
        if (new Date - preparedData[preparedData.length - 1].date > deltaForChart)
            preparedData.push({
                date: new Date,
                value: undefined
            })
        return preparedData
    } else return serverData
}

function updateChartsMeta(){
    if (duration === 'day') {
        format_time = '%Y-%m-%d %H:%M'
        date_one_duration_ago = (date) => new Date(date).setDate(date.getDate() - 1)
        //5 minute
        deltaForChart = 300000
    } else if (duration === 'week') {
        format_time = '%Y-%m-%d %H'
        date_one_duration_ago = (date) => new Date(date).setDate(date.getDate() - 7)
        //60 minute
        deltaForChart = 3600000
    } else {
        format_time = '%Y-%m-%d %H:%M:%S'
        date_one_duration_ago = (date) => new Date(date).setHours(date.getHours() - 1)
        //5 minute
        deltaForChart = 300000
    }
    updateMinMaxDates()
}

function getMinMaxDate() {
    let maxDates = []
    charts_data.forEach(chart => {
        maxDates.push(chart.data[chart.data.length - 1].date)})
    return Math.min.apply(null, maxDates)
}

function updateMinMaxDates(){
    maxDate = new Date
    minDate = new Date(date_one_duration_ago(maxDate))
    console.log(maxDate)
    console.log(minDate)
}

