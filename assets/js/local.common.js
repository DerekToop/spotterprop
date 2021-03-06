/**
 * 
 * @param {*} msg 
 */
function ConsoleLog(msg) {
    if (window.console)
    {
        console.log(msg);
    }
}

/**
 * 
 * @param {*} msg 
 */
function ConsoleDebug(msg) {
    var _DEBUG_ = _DEBUG_ || true;
    if (window.console && _DEBUG_)
    {
        console.log(msg);
    }
}

/**
 * 
 * @param {*} msg 
 */
function ConsoleError(msg) {
    if (window.console)
    {
        console.error(msg);
    }
}

/**
 * 
 * @param {*} dom 
 * @param {*} visible 
 */
function SetVisible(dom, visible) {
    if (visible)
        $(dom).show();
    else
        $(dom).hide();
}

/**
 * 
 * @param {*} dom 
 * @param {*} visible 
 */
function SetRedefineVisible(dom, visible) {
    if (visible)
    {
        $(dom).show();
        $(dom).removeClass('redefine-hidden');
        $(dom).addClass('redefine-visible');
    }
    else
    {
        $(dom).hide();
        $(dom).addClass('redefine-hidden');
        $(dom).removeClass('redefine-visible');
    }
}

/**
 * 
 */
function get_lang_from_url()
{
    var fields = location.href.replace(BASEURL, '').split('/');
    var field = fields[0] || "en";

    var lang = (field == "ar" || field == "en" || field == "cn") ? field : "";
    return lang;
}

/**
 * 
 * @param {*} name 
 */
function get_url_param(name) {
    var url = new URL(location.href);
    var value = url.searchParams.get(name);        
    
    return value;
}

/**
 * 
 * @param {*} x 
 */
function isNullOrEmpty(x)
{
    return (x == undefined || x == null || x == "");
}

/**
 * 
 * @param {*} val 
 * @param {*} selector 
 */
function isInvalid(val, selector)
{
    var isEmpty = isNullOrEmpty(val);
    if (isEmpty)
    {
        $(selector).focus();
        ConsoleDebug("Invalid: " + selector);
        return true;
    }

    return false;
}