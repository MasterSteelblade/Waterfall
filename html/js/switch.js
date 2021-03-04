function switchBlog(blogName) {
var formData = new FormData();
formData.append('switchTo', blogName)
fetch(siteURL + "/process/user/switch.php",
            {
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                redirect: 'follow',
                body: formData
            }
        )
            .then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Error logged, status code: ' + response.status);
                        return false;
                    }
                    response.json().then(function(data) {
                        if (data.code == "SUCCESS") {
                            location.reload();
                            return true;
                        } else {
                            return false;
                        }
                    })
                }
            ).catch(function(err) {
                return false;
            })
        
}