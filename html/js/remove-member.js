function kickBlogMember(elem) {
    var blogName = elem.getAttribute('data-blog-name');
   var formData = new FormData();
   formData.append('removeBlog', blogName);
   fetch(siteURL + "/process/settings/remove_member.php",
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
                    elem.innerHTML = 'Removed!';
                    element = document.getElementById(blogName + 'MemberNode');
                    element.parentNode.removeChild(element);
                    return true;
                } else {
                    return false;
                }
            })        }
    ).catch(function(err) {
        console.log(err);
    })
}