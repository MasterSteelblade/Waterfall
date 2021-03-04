function deleteInbox(elem) {
    var messageID = elem.getAttribute('data-message-id');
   var formData = new FormData();
   formData.append('messageID', messageID);
   fetch(siteURL + "/process/messages/delete_inbox.php",
        {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData
        }
    ).then(
        function(response) {
            if (response.status !== 200) {
                console.log('Error logged, status code: ' + response.status);
                return false;
            }
            response.json().then(function(data) {
                if (data.code == "SUCCESS") {
                    elem.innerHTML = 'Deleted!';
                    return true;
                } else {
                    return false;
                }
            })        }
    ).catch(function(err) {
        console.log(err);
    })
}