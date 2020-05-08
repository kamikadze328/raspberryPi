document.addEventListener('DOMContentLoaded', () => {
    window.setInterval(updateTime, 1000);

    fetch('php/servers.php')
        .then(response => response.text())
        .then(data => {
            try {
                data = JSON.stringify(JSON.parse(data), null, 4)
            } catch (e) {}
            console.log(data);
            document.getElementById("db-list").innerHTML = data;
            let dbs = document.getElementsByClassName("db");
            dbs = dbs ? dbs.length : 0;
            document.querySelector(".header :first-child").textContent = 'List of ' + dbs + ' DBs'
        });

});

function updateTime() {
    const date = new Date();
    const options = {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
        timezone: 'UTC',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    document.querySelector(".header :last-child").textContent = date.toLocaleString("ru", options);
}
