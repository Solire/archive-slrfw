$(function(){
    var nav = $('#menu');
        
    var openMenu = function(elmt, balise){
        $(balise, nav).hide();
        $(balise, elmt).show();
    }
    
    var closeMenu = function(elmt, balise){
        $(balise, elmt).hide();
    } 
    
    $('li', nav).hover(
        function(){
//            window.clearTimeout($(this).prop('timeid'));
            openMenu(this, 'dl');
           
        }, function(){
//            $(this).prop('timeid', window.setTimeout(closeMenu, 500, this, 'dl'));
            closeMenu(this, 'dl');
        }
    );
});