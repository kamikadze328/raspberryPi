document.addEventListener('mousemove', onActivity)
document.addEventListener('mousedown', onActivity)
document.addEventListener('keydown', onActivity)
document.addEventListener('scroll', onActivity)
document.addEventListener('touchstart', onActivity)

const currentPath = () => document.location.pathname
let lastSending = {
    date: new Date(),
    path: currentPath(),
    isSending: false
}
sendStats()

function onActivity(){
    (!lastSending.isSending && (new Date - lastSending.date > 60000 || lastSending.path !== currentPath())) ? sendStats() : null;
}

function sendStats(){
    lastSending.isSending = true
    fetch('/api/statistics/stats.php', {
        method: 'POST',
    }).then(() =>{
        lastSending.date = new Date
        lastSending.path = document.location.pathname
        lastSending.isSending = false
    })
}