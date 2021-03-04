function joinBlog(elem) {
    var inviteID = elem.getAttribute('data-invid');
   var formData = new FormData();
   formData.append('inviteID', inviteID);
   fetch(siteURL + "/process/settings/join_blog.php",
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
                    elem.innerHTML = 'Joined!';
                    elem.onclick = '';

                    return true;
                } else {
                    return false;
                }
            })        }
    ).catch(function(err) {
        console.log(err);
    })
}