$(function() {
    
    $(".visible-lang").live("click", function() {
        var $this = $(this);
        var value = $(this).val().split("|")
        var id_gab_page = parseInt(value[0]);
        var id_version = parseInt(value[1]);
        var checked = $this.is(':checked');
        $.post(
            'page/visible.html',
            {
                id_gab_page : id_gab_page,
                id_version : id_version,
                visible : checked ? 1 : 0
            },
            function(data){
                if(data.status != 'success') {
                    $this.attr('checked', !checked);
                    $.sticky("Une erreur est survenue", {
                        type:"error"
                    });
                }
                    
                else {
                    var $otherPageBloc = $('.visible-lang-' + id_gab_page + '-' + id_version).not($this)
                    $otherPageBloc.attr('checked', checked);
                    var $thisAll = $this.add($otherPageBloc)
                    if(checked) {
                        $.sticky("La page a été rendue visible", {
                            type:"success"
                        });
                        $thisAll.each(function() {
                            $(this).parents("li:first").removeClass("translucide")
                        })
                    } else {
                        $.sticky("La page a été rendue invisible", {
                            type:"success"
                        });
                        $thisAll.each(function() {
                            $(this).parents("li:first").addClass("translucide")
                        })
                    }
                }
            },
            'json'
            );
    }) 

});

