basehref = $('base').attr('href');
var uploader = []
var uploaderInited = [];

var createUploader = function(idBtn) {
    uploader[idBtn] = new plupload.Uploader({
        runtimes : 'gears,html5,silverlight,flash,html4',
        multi_selection:false,     // <-this is what you needed
        browse_button : idBtn,
        max_file_size : '1000mb',
        chunk_size : '7mb',
        url : basehref + 'ressource/upload.html',
        flash_swf_url : basehref + 'js/admin/plupload/plupload.flash.swf',
        silverlight_xap_url : basehref + 'js/admin/plupload/plupload.silverlight.xap',
        filters : [
        {
            title : "Image files", 
            extensions : "jpg,jpeg,gif,png"
        },

        {
            title : "Zip files", 
            extensions : "zip,rar,bz2"
        },

        {
            title : "Adobe", 
            extensions : "pdf,eps,psd,ai,indd"
        }
        ],
        drop_element : 'colright',
        unique_names : false,
        multiple_queues : true
    });
    
    uploader[idBtn].name = idBtn.substr(16)
    
    uploaderInited[idBtn] = false
    uploaderInit(idBtn)
    
}
    

var uploaderInit = function(idBtn){
    if (!uploaderInited[idBtn]) {
        uploaderInited[idBtn] = true;

        uploader[idBtn].init();

        uploader[idBtn].bind('FilesAdded', function(up, files) {
            var file = files[0]
            // affichage à l'ajout avec <div class="progressbar"></div>
                
            file.div = $('<div>');

            $('.progressbar').progressbar({
                value: 0
            });

            uploader[idBtn].refresh();
        });

        uploader[idBtn].bind('UploadProgress', function(up, file) {
            $('.progressbar', file.div).progressbar("value", file.percent);
        });

        uploader[idBtn].bind('Error', function(up, err) {
            err.file.error = true;
            up.refresh();
        });

        uploader[idBtn].bind('FileUploaded', function(up, file, info) {

            $(file.div, '.progressbar').progressbar("destroy");

            var response = $.parseJSON(info.response);

            if(response.status != "error") {
                    
            }                
                
            uploader[idBtn].splice(0, 1);
                
        });
    }
    else
        uploader[idBtn].refresh();
}
    

