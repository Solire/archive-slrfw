var tree, uploader, oTable = null, nomdesobjets='fichier';
var basehref = ''
var extensionsImage = ['jpg', 'jpeg', 'gif', 'png'];
var image, contenu, resid = null, restype, currentData;

var orderby = {champ:"date_crea", sens:"desc"};
var orderstates = ["", "asc", "desc", ""];
var orderclasses = ["ui-icon-carat-2-n-s", "ui-icon-carat-1-n", "ui-icon-carat-1-s", "ui-icon-carat-2-n-s"]

$(function () {
    basehref = $('base').attr('href');
    
	$("#tableau thead th").click(function(){
		span = $('> a', this);
		
		if (span.length > 0) {
			ind = $.inArray(orderby.sens, orderstates);
			
			if (orderby.champ == span.attr('id')) {
				orderby.sens = orderstates[ind + 1];
				$('#' + orderby.champ).removeClass(orderclasses[ind]).addClass(orderclasses[ind + 1]);
			}
			else {
				$('#' + orderby.champ).removeClass(orderclasses[ind]).addClass(orderclasses[0]);
				orderby.sens = orderstates[1];
				orderby.champ = span.attr('id');
				$('#' + orderby.champ).removeClass(orderclasses[0]).addClass(orderclasses[1]);
			}
			
			$('#node_' + resid + ' > a').click()
		}
		
		return false;
	});
	
//////////////////// JSTREE ////////////////////
	tree = $("#folders").jstree({
			"plugins" : ["themes", "json_data", "ui", "crrm", "cookies", "search", "types", "hotkeys"],//, "contextmenu"
			"core" : {"html_titles" : true},
			"json_data" : {
				"ajax" : {
					"url" : "media/folderlist.html",
					"data" : function (n) {
						return {
							"id" : n.attr ? n.attr("id").replace("node_","") : ""
						};
					}
				}
			},
			"types" : {
				"max_depth" : -2,
				"max_children" : -2,
				"valid_children" : "root",
				"types" : {
					"root" : {
						"valid_children" : ["page"]
					},
					"page" : {
						"valid_children" : "none"
					}
				}
			}
		})
		.bind("select_node.jstree", function(e, data) {
			resid = data.rslt.obj.attr("id").replace("node_","");
			restype = data.rslt.obj.attr("rel");

            $.cookie('id_gab_page', resid, {path : '/'});
            
            tree.jstree("open_node", data.rslt.obj);
            
            if (restype == "page") {
                $('#pickfiles').fadeIn(200);
                $('#foldercontent').html('<tr><td colspan="6">chargement ... </td></tr>');

                $.post(
                    "media/list.html",
                    {
                        id_gab_page: resid,
                        search: $('#search').val(),
                        orderby: orderby
                    },
                    function(data){
                        $('#foldercontent').html(data);
    //					initscroll();
                    }
                );
            }
            else {
                $('#foldercontent').html('');
                $('#pickfiles').fadeOut(200);
            }
		});	
//////////////////// FIN JSTREE ////////////////////


//////////////////// PLUPLOAD ////////////////////
	uploader = new plupload.Uploader({
		runtimes : 'gears,html5,silverlight,flash,html4',
		browse_button : 'pickfiles',
		max_file_size : '1000mb',
		chunk_size : '7mb',
		url : basehref + 'media/upload.html',
		flash_swf_url : basehref + 'back/plupload/plupload.flash.swf',
		silverlight_xap_url : basehref + 'back/plupload/plupload.silverlight.xap',
		filters : [
			{title : "Image files", extensions : "jpg,jpeg,gif,png"},
			{title : "Zip files", extensions : "zip,rar,bz2"},
			{title : "Adobe", extensions : "pdf,eps,psd,ai,indd"}
		],
		drop_element : 'colright',
		unique_names : false,
		multiple_queues : true
	});

	uploader.bind('Init', function(up, params) {
		$('#currentruntime').text("Current runtime: " + params.runtime);
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		if (restype == "page") {
			$.each(files, function(i, file) {
				var tr, td;
                if(!file.error) {
                    tr = $('<tr>');
                    $('<td>', {colspan : 4}).html(file.name + '<div class="progressbar"></div>').appendTo(tr);
                    file.tr = tr;
				}
			});
            
            $.each(files, function(i, file) {
                if (i==0)
                    file.tr.prependTo($('#foldercontent'));
                else
                    file.tr.insertAfter(files[i-1].tr);
            });
            
            $('.progressbar').progressbar({value: 0});
			
			up.refresh();
			up.start();
		}
		else
			uploader.splice(0, uploader.files.length);
	});

	uploader.bind('UploadProgress', function(up, file) {
		$(file.tr, '.progressbar').progressbar("value", file.percent);
	});

	uploader.bind('Error', function(up, err) {
		err.file.error = true;
		up.refresh(); // Reposition Flash/Silverlight
	});

	uploader.bind('FileUploaded', function(up, file, info) {       
        $(file.tr, '.progressbar').progressbar("destroy");
        
		var response = $.parseJSON(info.response);
		
		if(response.status != "error") {
            var ligne = '';
            
			ligne += '<td><a href="' + response.path + '" id="fileid_' + response.id + '" target="_blank" class="previsu">';
			
            var ext = file.name.split('.').pop().toLowerCase();
			if (extensionsImage.indexOf(ext) != -1)
				ligne += '<img class="vignette" src="' + response.minipath + '" alt="' + ext + '" /></a></td>';
			else
				ligne += '<img class="vignette" src="styles/admin/images/' + ext + '.png" alt="' + ext + '" /></a></td>';
			
            ligne += '<td>' + response.size + '</td>';
            ligne += '<td>' + response.width + '</td>';
            ligne += '<td>' + response.height + '</td>';
			ligne += '<td>' + response.date.substr(0, 10) + '<br />' + response.date.substr(11) + '</td>';
			ligne += '<td><a href="' + response.path + '" class="previsu button bleu"><span class="bleu"><img alt="supprimer" src="img/back/voir.png" /></span></a></td>';

            file.tr.attr("id", "fileid_" + response.id);
            file.tr.html(ligne);
		}
	});
	
	image = $(null);
	
	var previsu = $('<div>', {id: 'previsu'}).dialog({
		title : "Prévisualisation",
		buttons: [
			{
				text : "Supprimer",
				click : function(){
					var tr = image.parents('tr').first();
					$.post('media/delete.html', {id_media_fichier : tr.attr('id').split('_').pop()}, function(data){
						if(data.status == 'success'){
                            tr.fadeOut(500, function(){$(this).remove()});
                        }
					},'json');
					$(this).dialog("close");
				}
			},
			{
				text : "Annuler",
				click : function(){$(this).dialog("close");}
			}
		],
		autoOpen: false,
		close: function(event, ui){
            image = $(null);
        },
		height: "400",
		width: "400"
	});

	$('.previsu').live('click', function(){
		image = $(this);

		var link = $(this).attr('href');
		var ext = link.split('.').pop().toLowerCase();

		if (extensionsImage.indexOf(ext) != -1) {
			previsu.dialog( "option" , "height" , 400 );
			previsu.html('<img src="' + link + '" />');
		}
		else {
			previsu.dialog( "option" , "height" , 0 );
			previsu.html('');
		}
		
		previsu.dialog('open');
		
		return false;
	});
    
	$('#search').keyup(function(){
		$('#node_' + resid + ' > a').click()
	});
});