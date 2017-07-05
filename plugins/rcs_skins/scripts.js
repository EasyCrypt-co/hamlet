
var rcs_skins = new function()
{
    this.dialog = function(type, skin, element)
    {
        // if selection dialog shown, copy the selected item to the main screen
        
        if ($(".skin-list.ui-dialog-content").is(":visible"))
        {
            var link = $(element);
        
            $("#" + type + "-skin-select").html("");
            $("#" + type + "-skin-select").append(link.parent().clone());
            $("#" + type + "-skin-post").val(skin);
        
            $("#" + type + "-skin-list .skinselection").removeClass("selected");
            link.parent().addClass("selected");
        
            setTimeout(function() { $("#" + type + "-skin-list").dialog('close') }, 200);
            return;
        }
        
        // show the selection dialog
        
        $("#" + type + "-skin-list").dialog({
            height: $(window).height() * 0.8,
            width: $(window).width() * 0.8,
            modal: true,
            hide: "fade",
            show: "fade"
        });
      }
}
