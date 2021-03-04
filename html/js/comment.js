function submitComment(elem) {
    postID = elem.getAttribute('data-comment-post');
    text = document.getElementById('commentForm' + postID + 'Text');
    formData = new FormData;
    formData.append('postID', postID);
    formData.append('text', text.value);
    fetch(siteURL + "/process/post/comment.php",
        {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData
        }
    ).then(
        function(response) {
            response.json().then(function(data) {
                text.value = '';
                elem.innerHTML = 'Commented!';

            })
        }
    ).catch(function(err) {
        console.log(err);
    })
}