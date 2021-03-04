var timestampElements = document.getElementsByClassName("timestamp");
Array.prototype.forEach.call(timestampElements, function(time) {
    if (time.classList.contains('time-ago')) {
        dateToShow = new luxon.DateTime.fromSQL(time.getAttribute('data-timestamp')).toRelative();
    } else {
        dateToShow = new luxon.DateTime.fromSQL(time.getAttribute('data-timestamp')).toFormat("DDDD, h:mm:ss a");
    }
    time.innerHTML = dateToShow;
});