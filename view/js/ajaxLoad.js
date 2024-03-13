/*experimental load page*/
// 1. Query a URL and process its contents
// Modified queryAndProcessURL function to execute replaceInlineJS after all scripts are loaded
function queryAndProcessURL(url) {
    if ($('#_avideoPageContent').length) {
        $('body').addClass('_avideoPageContentLoading');
        console.log('a.ajaxLoad _avideoPageContent is present locally');
        var urlA = addQueryStringParameter(url, 'avideoIframe', 1);
        $.ajax({
            url: urlA,
            type: 'GET',
            success: function (response) {
                // Assuming response is the full HTML of the page
                const tempDiv = $('<div></div>').html(response);

                if (!tempDiv.find('#_avideoPageContent').length) {
                    console.log('a.ajaxLoad _avideoPageContent NOT is present remotely');
                    document.location = url;
                } else {
                    console.log('a.ajaxLoad _avideoPageContent is present remotely');

                    // When all scripts are loaded, execute replaceInlineJS
                    addNewScriptFiles(tempDiv).done(function () {
                        // Only execute replaceInlineJS if replacePrincipalContainer was successful
                        const replaceResult = replacePrincipalContainer(tempDiv);
                        if(replaceResult){
                            // Add new CSS files
                            addNewCSSFiles(tempDiv);
        
                            // Change the page title
                            changePageTitle(tempDiv);
        
                            // Replace all <meta> tags
                            replaceMetaTags(tempDiv);

                            replaceInlineJS(tempDiv);
                            
                            makeAjaxLoad();
                            // Change the current URL (using the History API for SPA behavior)
                            history.pushState({}, '', url);
                        }else{
                            console.log('a.ajaxLoad replacePrincipalContainer fail');
                            document.location = url;
                        }
                        
                        $('body').removeClass('_avideoPageContentLoading');
                    });

                }
                
            }
        });
    } else {
        console.log('a.ajaxLoad _avideoPageContent is NOT present locally');
        document.location = url;
    }
}

// Add new CSS files that are not already present
function addNewCSSFiles(tempDiv) {
    $('link[rel="stylesheet"]').each(function () {
        const currentHref = $(this).attr('href');
        tempDiv.find('link[rel="stylesheet"]').each(function () {
            if (currentHref !== $(this).attr('href') && !$('head').find(`link[href="${$(this).attr('href')}"]`).length) {
                $('head').append($(this).clone());
            }
        });
    });
}

// Replace the .principalContainer HTML or the entire body's content
function replacePrincipalContainer(tempDiv) {
    // Check if #_avideoPageContent exists in the tempDiv
    const newPrincipalContainer = tempDiv.find('#_avideoPageContent').html();

    if (newPrincipalContainer) {
        // Replace the content of #_avideoPageContent with newPrincipalContainer
        $('#_avideoPageContent').html(newPrincipalContainer);
        
        // Clone body classes from tempDiv and replace existing body classes
        // const tempBodyClasses = tempDiv.find('body').attr('class');
        //$('body').attr('class', tempBodyClasses);

        // Continue with additional operations
        // 4. Add new script files
        addNewScriptFiles(tempDiv);
    
        // 5. Replace inline CSS
        replaceInlineCSS(tempDiv);
    
        // 6. Replace inline JS
        replaceInlineJS(tempDiv);
        return true;
    }
    return false;
}

function addNewScriptFiles(tempDiv) {
    const promises = [];
    $('script[src]').each(function () {
        const currentSrc = $(this).attr('src');
        tempDiv.find('script[src]').each(function () {
            if (currentSrc !== $(this).attr('src') && !$('body').find(`script[src="${$(this).attr('src')}"]`).length) {
                // Load script asynchronously and push the promise to the promises array
                const promise = $.getScript($(this).attr('src'));
                promises.push(promise);
                $('body').append($(this).clone());
            }
        });
    });

    // Return a promise that resolves when all scripts are loaded
    return $.when.apply($, promises);
}

// Replace inline CSS
function replaceInlineCSS(tempDiv) {
    const newStyle = tempDiv.find('style').text();
    $('head').find('style').text(newStyle);
}

// Replace inline JS
function replaceInlineJS(tempDiv) {
    const newScript = tempDiv.find('script:not([src])').text();
    $('body').append(`<script>${newScript}</script>`);
}

// Change the page title
function changePageTitle(tempDiv) {
    const newTitle = tempDiv.find('title').text();
    $('title').text(newTitle);
}

// Replace all <meta> tags
function replaceMetaTags(tempDiv) {
    $('head meta').remove();
    tempDiv.find('meta').each(function () {
        $('head').append($(this).clone());
    });
}

function makeAjaxLoad(){
    // Bind function to all <a> tags with class .ajaxLoad
    $('a.ajaxLoad').each(function () {
        $(this).on('click', function (event) {
            event.preventDefault(); // Prevent default click action
            console.log('a.ajaxLoad clicked');
            var url = $(this).attr('href');
            queryAndProcessURL(url);
        });
    });
}

$(function () {
    makeAjaxLoad();
});