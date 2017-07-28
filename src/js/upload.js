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
    $('div.progress').show();
    $('span#alertFile').hide();

    $("button#btnSubmit").show();
  }

});

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
function redirection() {
  window.location.assign(urlRedirect);
}

(function() {

var percent = $('div.progress-bar');
var status = $('#status');
var error = 0;
$('form').ajaxForm({
    beforeSend: function() {
      status.empty();
        var percentVal = '0%';
        percent.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        percent.width(percentVal)
        percent.html(percentVal);
    },
    success: function() {
        var percentVal = '100%';
        percent.width(percentVal)
        percent.html(percentVal);
    },
	complete: function(xhr) {
      status.html('<span class="label label-success">Fichier uploadé !</span>');
      setTimeout(redirection, 1000);
	}
});

})();
