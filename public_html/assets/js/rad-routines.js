function json_populate_form(div, json)
{
    for (key in json)
    {
        key = key.replace('[', '\\[').replace(']', '\\]');
        var o = $(div).find('[id="'+key+'"]');
        if (o.length > 0)
        {
            if (o.attr('type') == 'checkbox')
            {
                o.attr('checked', 'checked');
                o.get(0).checked = true;
            }
            o.val(json[key]);
        }
        else console.log(key + ' not found');
    }
}   
function json_get_form(div)
{
    var obj={};
    
    $(div).find('input').each(function(idx, ele)
    {
        if ($(ele).attr('type') =='checkbox')
            if (!$(ele).get(0).checked)
                return;
        obj[ $(ele).attr('id') ] = $(this).val();
    });
    return obj;
}
