$('div.progress').hide();
$('span#alertFile').hide();
$("button#btnSubmit").hide();

$(document).on('click', '.browse', function(){
  var file = $(this).parent().parent().parent().find('.file');
  file.trigger('click');
});

$(document).on('change', '.file', function(){
  $(this).parent().find('.form-control').val($(this).val().replace(/C:\\fakepath\\/i, ''));

  if(findSize() > maxUploadSize){
    $('span#alertFile').show();
    $('div.progress').hide();

    $("button#btnSubmit").hide();
  }else{
    $('span#alertFile').hide();

    $("button#btnSubmit").show();
  }

});

function redirection() {
  window.location.assign(redirection);
}

function sendRequest() {
    var http = createRequestObject();
    http.open("GET", urlAjax);
    http.onreadystatechange = function () { handleResponse(http); };
    http.send(null);
}

function findSize() {
    var fileInput =  document.getElementById("file");
    try{
        return fileInput.files[0].size; // Size returned in bytes.
    }catch(e){
        var objFSO = new ActiveXObject("Scripting.FileSystemObject");
        var e = objFSO.getFile( fileInput.value);
        var fileSize = e.size;
        return fileSize;
    }
}


function createRequestObject() {
    var http;
    if (navigator.appName == "Microsoft Internet Explorer") {
        http = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else {
        http = new XMLHttpRequest();
    }
    return http;
}


function handleResponse(http) {
    var response;
    if (http.readyState == 4) {
        response = http.responseText;
        document.getElementById("progress-bar").style.width = response + "%";
        document.getElementById("progress-bar").innerHTML = response + "%";

        if (response < 100) {
            setTimeout("sendRequest()", 3000);
        }
    }
}

function startUpload() {
    $('div.progress').show();
    setTimeout("sendRequest()", 3000);
}

(function () {
    document.getElementById("uploadform").onsubmit = startUpload;
})();
