/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */

$(function ()
{
    $(document).keyup(function(e)
    {
        if (e.keyCode === 121)
        {
            $('#wakers_user_login_modal').wakersModal('toggle');
        }
    });

    if (window.location.hash === '#login')
    {
        $('#wakers_user_login_modal').wakersModal('show');
    }
});