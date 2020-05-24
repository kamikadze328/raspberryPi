let chart_data, refreshChartId = 0

function charts(json_data) {
    duration = json_data.duration
    fetch('./php/graph.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(json_data),
    })
        .then(response => {if(response.ok) return response.json(); else throw response})
        .then(data => {
            if (!data.error) {
                chart_data = data
                chart_data.forEach(server => {
                    console.log([server.host, server.data.length])
                    drawChart(server, false)
                    console.log(server.data)
                })
                window.removeEventListener("resize", resizeCharts)
                window.addEventListener("resize", resizeCharts)
                setNewChartUpdater()
            }
        }).catch(error => console.log(error));
}

function updateChart() {
    fetch('./php/graph.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(dataToServer),
    })
        .then(response => {if(response.ok) return response.json(); else throw response})
        .then(data => {
            if (!data.error) {
                chart_data = data
                d3.selectAll("svg").remove()
                reDrawAll()
                setNewChartUpdater()
            }
        }).catch(error => console.log(error));
}

function setNewChartUpdater() {
    const timeout = clearUpdaterAndGetTimeout(refreshChartId);
    refreshChartId = window.setInterval(updateChart, timeout);
}

function resizeCharts() {
    chart_data.forEach(server => drawChart(server, true))
}

function reDrawAll() {
    chart_data.forEach(server => drawChart(server, false))
}


function drawChart(server, isResize) {
    //TODO Сейчас при перерисовке удаляется тег и заново добавляется.
    //Переделать, чтобы перерисовывалось, а не вот это всё
    let id = getServerId(server.host)
    let chart = document.getElementById('chart-' + id)
    let margin = ({top: 10, right: 0, bottom: 10, left: 35}),
        width = chart.clientWidth - margin.right - margin.left,
        height = chart.clientHeight - margin.top - margin.bottom;
    if (isResize) d3.select(`#chart-${id} > svg`).remove()
    const svg = d3.select('#chart-' + id).append('svg')
        .attr("width", '100%')
        .attr("height", '100%')
        .classed("svg-content", true);

    let preparedData

    if (!isResize) {
        let format_time
        let delta
        if (duration === 'day') {
            format_time = '%Y-%m-%d %H:%M'
            //5 minute
            delta = 300000
        } else if (duration === 'week') {
            format_time = '%Y-%m-%d %H'
            //60 minute
            delta = 3600000
        } else {
            format_time = '%Y-%m-%d %H:%M:%S'
            //5 minute
            delta = 300000
        }
        let parseDate = d3.timeParse(format_time)
        preparedData = []
        server.data.forEach(elem => {
            let preparedElem = {
                date: parseDate(elem.date),
                value: elem.value ? +elem.value : undefined
            }
            const prev = preparedData[preparedData.length - 1]
            if (elem.value && prev && preparedElem.date - prev.date > delta)
                preparedData.push({
                    data: new Date(prev.date.getMilliseconds() + 1),
                    value: undefined
                })
            preparedData.push(preparedElem)
        }, server.data)
        if (new Date - preparedData[preparedData.length - 1].date > delta)
            preparedData.push({
                data: new Date,
                value: undefined
            })

        server.data = preparedData
    }
    let data = server.data
    /*let minusHours
    if(duration === 'day') minusHours = 24
    else if(duration === 'week') minusHours = 24*7
    else minusHours = 1
    const nowDate = new Date
    const minDate = new Date(nowDate.setHours(nowDate.getHours() - minusHours))
    console.log(d3.extent(data, d=>d.date))
    console.log(d3.extent([minDate, nowDate]))*/

    let xScale = d3.scaleTime()
        .range([margin.left, width - margin.right])
        .domain(d3.extent(data, d => d.date))
    const maxValue = d3.max(data, d => d.value)
    let yScale = d3.scaleLinear()
        .range([height - margin.bottom, margin.top])
        .domain([0, maxValue ? maxValue : 3/* > 3 ? 3 : maxValue*/]).nice()

    const xAxis = d3.axisBottom()
        .scale(xScale)
        .ticks(width / 80);
    const yAxis = d3.axisLeft()
        .scale(yScale)
        .ticks(3);


    svg.append("g")
        .attr("class", "axisWhite")
        .attr("transform", `translate(0, ${height - margin.bottom})`)
        .call(xAxis)
    svg.append("g")
        .attr("class", "axisWhite")
        .attr("transform", `translate(${margin.left},0)`)
        .call(yAxis);


    const line = d3.line()
        .defined(d => !isNaN(d.value))
        .x(d => xScale(d.date))
        .y(d => yScale(d.value))


    svg.append("path")
        .datum(data.filter(line.defined()))
        .attr("stroke", "#ff5b57")
        .attr("fill", "none")
        .attr("stroke-width", 1.5)
        .attr("stroke-dasharray", 4)
        .attr("d", line);

    svg.append("path")
        .datum(data)
        .attr("stroke", "#348fe2")
        .attr("stroke-width", 1.5)
        .attr("fill", "none")
        .attr("d", line);
}

