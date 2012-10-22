basehref = $('base').attr('href');
var uploader = []
var uploaderInited = [];
var createUploader = function(idBtn, multi_selection) {
    if (multi_selection) {
        multi_selection = true;
    } else {
        multi_selection = false;
    }
    uploader[idBtn] = new plupload.Uploader({
        runtimes : 'gears,html5,silverlight,flash,html4',
        multi_selection:multi_selection,     // <-this is what you needed
        browse_button : idBtn,
        max_file_size : '1000mb',
        chunk_size : '2mb',
        url : window.location.href + "&dt_action=upload&nomain=1",
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
//            var file = files[0]
//            // affichage à l'ajout avec <div class="progressbar"></div>
//            
//            file.div = $('<div>');
//            $('#filelist').append(
//                '<div id="' + file.id + '">' +
//                file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
//                '</div>');

            if (uploader[idBtn].settings.multi_selection == false) {
                $('#filelist').empty()
            }

            $.each(files, function(i, file) {
                $('#filelist').append(
                    '<div id="' + file.id + '">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div>');
            });
            
            
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

//        uploader[idBtn].bind('FileUploaded', function(up, file, info) {
//
//            $(file.div, '.progressbar').progressbar("destroy");
//
//            var response = $.parseJSON(info.response);
//
//            if(response.status != "error") {
//                    
//            }                
//                
//            uploader[idBtn].splice(0, 1);
//                
//        });
    }
    else
        uploader[idBtn].refresh();
}
    

