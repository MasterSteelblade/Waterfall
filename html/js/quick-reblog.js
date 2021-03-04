var id;
var wait = false; //
var targetLoop;


function hideError() {
  $("#quick-box").hide();
  $("#quick-error").css("visibility", "hidden");
  $("quick-error-text").text("");
}


$(document).ready(function() {

  $("body").append(
    "<div id='quick-box' style='display: none'><div id='quick-error' style='visibility: hidden;'><p id='quick-error-text'> </p></div><form action='process/post/reblog.php' method='post' id='quick-form'>" +
        "<div name='quick-text' id='quick-text'></div><input name='postTags' id='quick-tags' placeholder='Tags (separate by comma)' /><input name='reblogging' id='quick-reblog-id' value='' type=hidden>" +
        "<button name='post' id='quick-reblog-button' class='btn btn-primary' type=button>Reblog</button></form></div>"
    );


    var quillReblog = new Quill('#quick-text', {
        theme: 'snow',
        modules: {
            toolbar: false
        }
      });

    $(".footer-button").hover(function(){
      id = $(this).attr("data-post-id");
    });


    $("#quick-reblog-button").click(function(){

      if (id != undefined) {
        $('#quick-reblog-id').val(id);
        console.log(id);
      }
      hideError();
      if (document.getElementById('quick-reblog-id').value == undefined || document.getElementById('quick-reblog-id').value == null) {
        return false;
      }
      var quickFormData = new FormData();
      quickFormData.append('postText', document.querySelector('#quick-text').children[0].innerHTML);
      quickFormData.append('reblogging', document.getElementById('quick-reblog-id').value);
      quickFormData.append('postTags', document.getElementById('quick-tags').value);
      quickFormData.append('onBlog', activeBlog);
      quickFormData.append('submitType', 'post');
      fetch(siteURL +"/process/post/reblog.php",
        {
            method: 'POST',
            mode: 'cors',
            credentials: 'include',
            redirect: 'follow',
            body: quickFormData
        }
    ).then(
      function(response) {
        if (response.status !== 200) {
            console.log('Error logged, status code: ' + response.status);
            return false;
        }
        response.json().then(function(data) {
          quillReblog.setContents([{insert: '\n'}]);
          $("#quick-reblog-id").val("");
          $("#quick-tags").val("");

          if (targetLoop.hasClass("already-reblogged")) { return; }
          targetLoop.addClass("already-reblogged");
          targetLoop.removeClass("fad");
          targetLoop.addClass("fas");
        }).catch(function(err) {
          quillReblog.setContents([{insert: '\n'}]);
          $("#quick-reblog-id").val("");
          $("#quick-tags").val("");
        })
    }
    )
      

    })
  });

$(document).on("mouseover", ".fa-reblog-alt", function(loop) {
  var obj = $(loop.target);
  targetLoop = $(loop.target);
  var offset = $(obj).offset();
  var box_left = offset.left - ($("#quick-box").width() / 2 - 45);
  var box_top = offset.top - ($("#quick-box").height() / 2 + 135);
  $("#quick-box").css("top", box_top + "px");
  $("#quick-box").css("left", box_left + "px");
  $("#quick-box").show();
  wait = true;
})

$(document).on("mouseover", "#quick-box, .fa-reblog-alt, #quick-text, #quick-tags", function(loop) {
    wait = true;

})

$(document).on("mouseleave", "#quick-box, .fa-reblog-alt, #quick-text, #quick-tags", function(loop) {
    wait = false;

})


$(document).on("mouseleave", "#quick-box, .fa-reblog-alt, #quick-text, #quick-tags", function() {
  wait = false;
  setTimeout(function(){
    if (!wait) {
      hideError();
    }
  }, 1000);
});


