function likePost(elem) {
    var message = elem;
    var postID = elem.getAttribute('data-post-id');
    var sourceID = elem.getAttribute('data-source-id');
    $('.like-button[data-source-id="' + sourceID + '"]').each(function() {
        if (elem.classList.contains('liked-post')) {
           elem.classList.remove('liked-post');
   } else {
           elem.classList.add("liked-post");
       }
   });
   var formData = new FormData();
   formData.append('postID', postID);
   fetch(siteURL + "/process/post/toggle-like.php",
        {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: formData
        }
    ).then(
        function(response) {
            // It might seem odd not to do anything here, but it's intentional.
        }
    ).catch(function(err) {
        console.log(err);
    })
}