document.addEventListener('mousemove', onActivity);
document.addEventListener('mousedown', onActivity);
document.addEventListener('keydown', onActivity);
document.addEventListener('scroll', onActivity);
document.addEventListener('touchstart', onActivity);
window.addEventListener('beforeunload', async () => {
    while(lastSending.isSending){
        console.log('here');
        await sleep(100);
    }
});
const currentPath = () => document.location.pathname;
let lastSending = {
    date: new Date(),
    path: currentPath(),
    isSending: false,
};
sendStats();
function onActivity(){
    if(!lastSending.isSending && (new Date - lastSending.date > 60000 || lastSending.path !== currentPath()))
        sendStats();
}

function sendStats(){
    lastSending.isSending = true;
    fetch('/api/statistics/stats.php', {
        method: 'POST',
    }).then(() =>{
        lastSending.date = new Date;
        lastSending.path = document.location.pathname;
        lastSending.isSending = false;
    })
}
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}