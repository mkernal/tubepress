function tubepress_popup_player(galleryId, videoId) {
    var obj = jQuery("#tubepress_embedded_object_" + galleryId + " > object");
    var win = window.open('', 'tubepress_popup_' + galleryId + '_' + videoId, 'location=0,directories=0,menubar=0,scrollbars=0,status=0,toolbar=0,width=' + obj.css("width") + ',height=' + obj.css("height"));
    var preamble = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><title>' + jQuery("#tubepress_image_" + videoId + "_" + galleryId + " > img").attr("alt") + '</title></head><body style="margin: -6px 0pt 0pt; background-color: black;">';
    win.document.write(preamble + jQuery("#tubepress_embedded_object_" + galleryId).html() + '</body></html>');
    win.document.close() ;
}

function tubepress_popup_player_init(baseUrl) { 
    //nada
}
