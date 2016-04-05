$(function() {
        $("#bookmark_button").click(function() {
            // Mozilla Firefox Bookmark
            if ('sidebar' in window && 'addPanel' in window.sidebar) { 
                window.sidebar.addPanel(location.href,document.title,"");
            } else if( /*@cc_on!@*/false) { // IE Favorite
                window.external.AddFavorite(location.href,document.title); 
            } else { // webkit - safari/chrome
                $("#bookmark_button").text("Press CTRL + D to bookmark");
            }
        });
    });