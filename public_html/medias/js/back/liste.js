//var sort_elmt = $(null);
//var sort_box = $(null);
var positions = {};

$(function(){
//// SUPPRIMER UNE PAGE.
	confirm = $('<div>')
        .html("Etes-vous sur de vouloir supprimer cette page?")
        .dialog({
            autoOpen : false,
            title : "Attention",
            buttons: {
                "Ok" : function(){$(this).dialog("close");},
                "Annuler" : function(){$(this).dialog("close");}
            }
    });
    
    var confirmOpen = function(sort_elmt) {
        var sort_box = sort_elmt.parent();
        var id_gab_page = parseInt(sort_elmt.attr('id').split('_').pop());
        
        confirm.dialog('option', 'buttons', {
            "Ok" : function(){
                $.post(
                    'page/delete.html',
                    {id_gab_page : id_gab_page},
                    function(data){
                        if(data.status == 'success')
                            sort_elmt.slideUp('fast', function(){
                                $(this).remove();
                                sort_box.sortable('refresh');
                                confirm.dialog("close")
                            })
                    },
                    'json'
                );
            },
            "Annuler" : function(){$(this).dialog("close");}            
        }).dialog('open');
    }
	
	$('.supprimer').live('click', function(){
        confirmOpen($(this).parents('.sort-elmt').first());

		return false
	});
	
//// RENDRE VISIBLE UNE PAGE.
	$('.rendrevisible').live('click', function(){
		var $this = $(this);
		var id_gab_page = parseInt($this.parents('.sort-elmt').first().attr('id').split('_').pop());
		var checked = $this.is(':checked');
		
		$.post(
			'page/visible.html',
			{
				id_gab_page : id_gab_page,
				visible     : checked ? 1 : 0
			},
			function(data){                
				if(data.status != 'success')
					$this.attr('checked', !checked);
			},
            'json'
		);
	});

//// GESTION DU TRI DES PAGES.
	var initTri = function () {
		$('.sort-box').each(function(){
			var i = 1;
			$(this).children().each(function(){
				positions[parseInt($(this).attr('id').split('_').pop())] = i++;
			});

			$(this).sortable({
				placeholder: 'empty',
				items: '> .sort-elmt',
				handle: '.sort-move',
				deactivate: function(){
					var i = 1;
					$(this).children().each(function(){
						positions[parseInt($(this).attr('id').split('_').pop())] = i++;
					});
				}
			 });
		});
	}

	initTri();

	$('a.enregistrerordre').click(function(){
		$.post('page/order.html', {'positions' : positions}, function(data){
			$("a.enregistrerordre span").text(data);
			window.setTimeout('$("a.enregistrerordre span").text("Enregistrer Ordre")', 2000);
		});

		return false;
	});

	$('select[name=id_sous_rubrique]').change(function(){
        var id_sous_rubrique = $(this).val();
        $.cookie('id_sous_rubrique', id_sous_rubrique, {path : '/'});
        $(this).parents('form').submit();
	});

//// OUVERTURE / FERMETURE DES PAGES PARENTES.
	$('legend').live('click', function(){
		if ($(this).next('div').is(':hidden') && $(this).next('div').html()=='') {
            
            if (!$(this).next('div').hasClass('children-loaded')) {            
                var id = $(this).parent().attr('id').split('_').pop();
                $(this).next('div').load('page/children.html', {id_parent : id}, function(data){
                    $(this).addClass('children-loaded');
                    if (data != '') {
                        initTri();
                        $(this).slideToggle(500);
                        $(this).siblings('.cat-modif').slideToggle(500);
                    }
                });
            }
		}
		else {
			$(this).next('div').slideToggle(500);
			$(this).siblings('.cat-modif').slideToggle(500);
		}
        
        return false;
	});
});