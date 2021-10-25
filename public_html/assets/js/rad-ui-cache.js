/* radSYS 1.3 UI Caching */

function rad_ui_check_cache(cache_identifier)
{
    //('checking '+cache_identifier);
    var item_data = localStorage.getItem(cache_identifier);
    if (item_data === null)
    {
        $.get(
            root_path+'/rad-ui-cache/uistorage_'+cache_identifier,
            function(data)
            {
                if (data.indexOf('rad-UI-cahce error') === -1)
                {
                    localStorage.setItem(cache_identifier, data);
                }
                $('#'+cache_identifier+'_storage').replaceWith(data);
            }    
        );
    }
    else
        $('#'+cache_identifier+'_storage').replaceWith(
            //'{<b>from cahce</b> ' +cache_identifier + '}:'+
            item_data
        );
        
}
