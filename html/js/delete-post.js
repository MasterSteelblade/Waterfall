function deletePost(elem) {
    var postID = elem.getAttribute('data-post-id');
    var formData = new FormData();
   formData.append('postID', postID);
   fetch(siteURL + "/process/post/delete.php",
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
                document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. Please contact support."); ?>'
                return false;
            }
            response.json().then(function(data) {
                if (data.code == "SUCCESS") {
                    document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::successBox("Edited! redirecting to dashboard..."); ?>'
                    window.location.href = siteURL + '/dashboard';
                } else if (data.code == "ERROR") {
                    document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You don\'t have permission to post to the blog you selected."); ?>'
                } else if (data.code == "ERR_EMPTY_TEXT") {
                    document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("You didn\'t put anything to post."); ?>'
                } else {
                    document.getElementById("DisplayDiv").innerHTML = '<?php UIUtils::errorBox("There was an error trying to post. Please contact support so we can look into it."); ?>'

                }
            })
        }
    ).catch(function(err) {
        console.log(err);
    })
}