/* add cart */
function add_to_cart_bdsh(spec_id, quantity,type,store_id)
{
    var url =SITE_URL+ '/index.php?app=cart&act=add';
    $.getJSON(url, {'spec_id':spec_id, 'quantity':quantity,'type':type}, function(data){
        if(data != null){
            if (data.done)
            {
                location.href='/index.php?app=cart&store_id='+store_id;
            }
            else
            {
                alert(data.msg);
            }
        }
    });
}