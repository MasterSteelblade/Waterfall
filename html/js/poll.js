$(document).ready(function() {
    $('.poll-object').submit(function(event) { // catch the form's submit event
        event.preventDefault();
        var formData = new FormData();
        parent = document.activeElement.form.parentNode;
        pollID = document.activeElement.value;
        formData.append('pollID', pollID);
        selected = document.querySelectorAll('input[name="poll'+ pollID +'Answer"]:checked')
        for(i = 0; i < selected.length; ++i) {
            formData.append('selected[]', selected[i].value);
        }

        fetch(siteURL +"/process/poll/vote.php",
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
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        response.json().then(function(data) {
                        
                        })
                    } else {
                        response.text().then(function(data) {
                            nodeList = document.querySelectorAll('[data-poll-id="' + pollID + '"]');
                            for (let i = 0; i < nodeList.length; i++) {
                                let item = nodeList[i];
                                    item.outerHTML = data
                            }
                        });

                    }
                }
            ).catch(function(err) {
            })
        return false; // cancel original event to prevent form submitting
        });
    });