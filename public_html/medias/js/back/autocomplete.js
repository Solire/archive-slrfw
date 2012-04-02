var timer = null;

$(function(){
    $(".autocomplete-join").livequery(function(){ 
        var $input = $(this); 
        $(this).autocomplete({
            source: function( request, response ) {
                var table = $input.parent().find("input.join-table").val();
                var idField = $input.parent().find("input.join-id_field").val();
                var labelField = $input.parent().find("input.join-label_field").val();
                var queryFilter = $input.parent().find("input.join-query_filter").val();
                var idGabPage = $("input[name='id_gab_page']").val();
                
                $.getJSON( 
                    "page/autocomplete-join.html", 
                    {
                        table : table,
                        id_field : idField,
                        label_field : labelField,
                        query_filter : queryFilter,
                        id_gab_page : idGabPage,
                        term : request.term
                    }, function( data, status, xhr ) {
                        response( data );
                    })
            },
            minLength: 0,
            select: function(e, ui) {
                $(this).parent().find(".join").val(ui.item.id)
            }
                
        }).focus( function() {
            
            if (this.value == "")
            {
                clearTimeout(timer);
                timer = setTimeout(function(){
                    if ($input.val() == "")
                    {
                        $input.autocomplete('search', '');
                    }
                },220);
                
            }
        });; 
    });

    
});