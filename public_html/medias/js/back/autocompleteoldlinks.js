var timer = null;

$(function(){
    $("input[name=old]").livequery(function(){ 
        var $input = $(this); 
        $(this).autocomplete({
            source: function( request, response ) {
                
                
                $.getJSON( 
                    "page/autocomplete-old-links.html", 
                    {
                        
                        term : request.term
                    }, function( data, status, xhr ) {
                        response( data );
                    })
            },
            minLength: 0,
            select: function(e, ui) {
                $(this).val(ui.item.label)
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
        })
        
    });
    
    
 

    
});