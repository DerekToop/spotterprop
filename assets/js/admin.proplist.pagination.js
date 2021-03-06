var AdminPropListPage = AdminPropListPage || {};

AdminPropListPage.NUMBER_PER_PAGE = $("#header_npp_admin").val();
AdminPropListPage.MAX_PAGE_BUTTONS = 10;

/**
 * 
 * @param {*} totalNum 
 */
AdminPropListPage.initPageButtons = function (totalNum)
{
    totalNum = totalNum || $(".tr-row").length;
    if (totalNum <= AdminPropListPage.NUMBER_PER_PAGE) return;
    var max_pages = totalNum / AdminPropListPage.NUMBER_PER_PAGE;
    max_pages = (max_pages > AdminPropListPage.MAX_PAGE_BUTTONS) ? AdminPropListPage.MAX_PAGE_BUTTONS : max_pages;

    $(".pagination").html('');
    var prev_button = $(".li-prev-button-template").html();
    $(prev_button).appendTo($(".pagination"));
    
    var button_template = $(".li-page-button-template").html();
    for (var i = 0; i < max_pages; i++)
    {
        var html = button_template.replace(new RegExp("{{page-number}}", 'g'), i+1);
        $(html).appendTo($(".pagination"));
    }
    
    var next_button = $(".li-next-button-template").html();
    $(next_button).appendTo($(".pagination"));
}

/**
 * 
 * @param {*} pageNum 
 * @param {*} totalNum 
 */
AdminPropListPage.resetPageButtons = function (pageNum, totalNum)
{
    totalNum = totalNum || $(".tr-row").length;
    if (totalNum <= AdminPropListPage.NUMBER_PER_PAGE) return;
    var max_pages = Math.ceil(totalNum / AdminPropListPage.NUMBER_PER_PAGE);
    if (max_pages <= AdminPropListPage.MAX_PAGE_BUTTONS) 
    {
        AdminPropListPage.initPageButtons();
        return;
    }

    var min_number = pageNum - Math.ceil(AdminPropListPage.MAX_PAGE_BUTTONS/2);
    if (min_number < 1)
    {
        AdminPropListPage.initPageButtons();
        return;
    }
    
    var max_number = pageNum + Math.floor(AdminPropListPage.MAX_PAGE_BUTTONS/2) - 1;
    if (max_number > max_pages)
    {
        max_number = max_pages;
        min_number = max_number - (AdminPropListPage.MAX_PAGE_BUTTONS - 1);
    }

    $(".pagination").html('');
    var prev_button = $(".li-prev-button-template").html();
    $(prev_button).appendTo($(".pagination"));
    
    var button_template = $(".li-page-button-template").html();
    for (var i = min_number; i <= max_number; i++)
    {
        var html = button_template.replace(new RegExp("{{page-number}}", 'g'), i);
        $(html).appendTo($(".pagination"));
    }
    
    var next_button = $(".li-next-button-template").html();
    $(next_button).appendTo($(".pagination"));
}

/**
 * 
 * @param {*} pageNum 
 * @param {*} totalNum 
 */
AdminPropListPage.gotoPage = function (pageNum, totalNum) {
    // check if same page number
    var selected = $(".page-item-selected").data("page");
    if (pageNum == selected)
        return;

    totalNum = totalNum || $(".tr-row").length;

    AdminPropListPage.resetPageButtons(pageNum, totalNum);

    // reset all
    $(".tr-row").hide();
    $(".page-item").removeClass("page-item-selected");

    // search all rows
    $(".tr-row").each(function(idx){
        var itemSeqNum = $(this).data("seq");
        var item_page_num = parseInt((itemSeqNum - 1) / AdminPropListPage.NUMBER_PER_PAGE) + 1;
        if (item_page_num == pageNum)
        {
            // found items in this page
            var row = ".tr-row-seq-" + itemSeqNum;
            $(row).show();            
        }
    });

    // mark current page
    $(`.page-item-${pageNum}`).addClass("page-item-selected");
    $(`.page-item-${pageNum}`).find("a").css("background", "#337ab7");
    $(`.page-item-${pageNum}`).find("a").css("color", "#fff");
}

/**
 * 
 * @param {*} totalNum 
 */
AdminPropListPage.gotoNext = function (totalNum)
{
    totalNum = totalNum || $(".tr-row").length;
    if (totalNum <= AdminPropListPage.NUMBER_PER_PAGE) return;
    var max_pages = Math.ceil(totalNum / AdminPropListPage.NUMBER_PER_PAGE);

    var selected = $(".page-item-selected").data("page") || 1;
    var next_page = parseInt(selected) + 1;
    
    next_page = (next_page > max_pages) ? max_pages : next_page;
    AdminPropListPage.gotoPage(next_page, totalNum);
}

/**
 * 
 * @param {*} totalNum 
 */
AdminPropListPage.gotoPrev = function (totalNum)
{
    totalNum = totalNum || $(".tr-row").length;
    if (totalNum <= AdminPropListPage.NUMBER_PER_PAGE) return;
    var max_pages = Math.ceil(totalNum / AdminPropListPage.NUMBER_PER_PAGE);

    var selected = $(".page-item-selected").data("page") || 1;
    var prev_page = parseInt(selected) - 1;
    
    prev_page = (prev_page < 1) ? 1 : prev_page;
    AdminPropListPage.gotoPage(prev_page, totalNum);
}