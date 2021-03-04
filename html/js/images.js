var buttonPressed;


function b64toBlob(b64Data, contentType, sliceSize) {
    contentType = contentType || '';
    sliceSize = sliceSize || 512;

    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        var slice = byteCharacters.slice(offset, offset + sliceSize);

        var byteNumbers = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
        }
        var byteArray = new Uint8Array(byteNumbers);

        byteArrays.push(byteArray);
    }

    var blob = new Blob(byteArrays, {type: contentType});
    return blob;
}


function selectedHandler(ev) {
    // Use DataTransferItemList interface to access the file(s)
    // If dropped items aren't files, reject them
    fileinput = document.getElementById("file-input");
    for (var i = 0; i < fileinput.files.length; i++) {
        maxFilesizeBytes = imageMax;
        var file = fileinput.files[i];
        if (document.getElementsByClassName("sortable-image").length >= 10) {
            alert('10 images max, sorry!');
            return false;
        }
        if (file.size > maxFilesizeBytes) {
            alert('File too big!')
            return false;
        }
        filearea = document.getElementById('files');
        fileareaHTML = document.getElementById('files').innerHTML;
        reader = new FileReader();
        form = document.getElementById('PostForm');

        obj = URL.createObjectURL(file);
        reader.readAsDataURL(file);
        reader.onload = function () {
            fileHTML = fileareaHTML + '<div class="sortable-image"><a href="#" class="delete-sortable sortable-disabled" onclick="$(this).parent().remove();">delete</a><img class="thumb-post sortable-image-file" data-base64="'+ reader.result +'" src="'+ obj + '"><div class="row"><div class="col"><input class="form-control image-caption" type="text" placeholder="Caption"><br><input class="form-control image-description" type="text" placeholder="Image ID"></div></div></div>';
            filearea.innerHTML = fileHTML;
        };
    }
    document.getElementById('file-input').value = null;
}

function dropHandler(ev) {
    // Prevent default behavior (Prevent file from being opened)
    ev.preventDefault();
    if (ev.dataTransfer.items) {
      // Use DataTransferItemList interface to access the file(s)
        for (var i = 0; i < ev.dataTransfer.items.length; i++) {
            maxFilesizeBytes = imageMax;
                // If dropped items aren't files, reject them
            if (ev.dataTransfer.items[i].kind === 'file') {
                var file = ev.dataTransfer.items[i].getAsFile();
                if (document.getElementsByClassName("sortable-image").length >= 10) {
                    alert('10 images max, sorry!');
                    return false;
                }
                if (file.size > maxFilesizeBytes) {
                    
                alert('File too big!');
                return false;
                }
                filearea = document.getElementById('files')
                fileareaHTML = document.getElementById('files').innerHTML;
                reader = new FileReader();
                form = document.getElementById('PostForm');

                obj = URL.createObjectURL(file);
                reader.readAsDataURL(file);
                reader.onload = function () {
                    bn64 = reader.result
                    fileHTML = fileareaHTML + '<div class="sortable-image"><a href="#" class="delete-sortable sortable-disabled" onclick="$(this).parent().remove();">delete</a><img class="thumb-post sortable-image-file" data-base64="'+ bn64 +'" src="'+ obj + '"><div class="row"><div class="col"><input class="form-control image-caption" type="text" placeholder="Caption"><br><input class="form-control image-description" type="text" placeholder="Image ID"></div></div></div>';
                };
            }
        }
    } else {
      // Use DataTransfer interface to access the file(s)
        for (var i = 0; i < ev.dataTransfer.files.length; i++) {
            console.log('.K.. file[' + i + '].name = ' + ev.dataTransfer.files[i].name);
        }
    }
}

function dragOverHandler(ev) {
    //console.log('File(s) in drop zone');

    // Prevent default behavior (Prevent file from being opened)
    ev.preventDefault();
}


$(document).ready(function(){
    $('.delete-sortable').click(function(){
        $(this).parent().remove();
        return false;
    });
});