/*experimental load page*/
// 1. Query a URL and process its contents
// Modified queryAndProcessURL function to execute replaceInlineJS after all scripts are loaded
function queryAndProcessURL(url) {
    if ($('#_avideoPageContent').length) {
        $('body').addClass('_avideoPageContentLoading');
        console.log('a.ajaxLoad _avideoPageContent is present locally');
        var urlA = addQueryStringParameter(url, 'avideoIframe', 1);
        var urlA = addQueryStringParameter(url, 'ajaxLoad', 1);
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
                        if (replaceResult) {
                            // Add new CSS files
                            addNewCSSFiles(tempDiv);

                            // Change the page title
                            changePageTitle(tempDiv);

                            // Replace all <meta> tags
                            replaceMetaTags(tempDiv);
                            
                            addNewScriptFiles(tempDiv);

                            replaceInlineCSS(tempDiv);

                            makeAjaxLoad();
                            // Change the current URL (using the History API for SPA behavior)
                            history.pushState({}, '', url);
                        } else {
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

function addNewCSSFiles(tempDiv) {
    // Remove the 'addNewCSSFiles' class from all existing <link> tags in <head>
    $('link[rel="stylesheet"]').removeClass('addNewCSSFiles');

    // Remove all <style> tags in <head> to avoid duplicates before adding new ones from tempDiv
    $('head style').remove();

    // Helper function to get the base URL without query parameters
    function getBaseURL(href) {
        return href.split('?')[0];
    }

    // Check each CSS link in tempDiv
    tempDiv.find('link[rel="stylesheet"]').each(function () {
        const tempHref = getBaseURL($(this).attr('href'));
        
        // Find the corresponding CSS link in <head> (ignoring parameters)
        let existsInHead = false;
        $('head link[rel="stylesheet"]').each(function () {
            if (getBaseURL($(this).attr('href')) === tempHref) {
                $(this).addClass('addNewCSSFiles');
                existsInHead = true;
                return false; // Break the loop once a match is found
            }
        });

        // If the CSS file is not in <head>, append it and add 'addNewCSSFiles' class
        if (!existsInHead) {
            const newStyle = $(this).clone().addClass('addNewCSSFiles');
            $('head').append(newStyle);
        }
    });

    // Add <style> tags from tempDiv to <head>
    tempDiv.find('style').each(function () {
        $('head').append($(this).clone());
    });

    // Remove any <link> tags in <head> that are not marked with 'addNewCSSFiles' (i.e., not in tempDiv)
    // $('head link[rel="stylesheet"]:not(.addNewCSSFiles)').remove();
}


// Replace the .principalContainer HTML or the entire body's content
function replacePrincipalContainer(tempDiv) {
    // Check if #_avideoPageContent exists in the tempDiv
    const newPrincipalContainer = tempDiv.find('#_avideoPageContent').html();

    if (newPrincipalContainer) {
        // Replace the content of #_avideoPageContent with newPrincipalContainer
        $('#_avideoPageContent').html(newPrincipalContainer);

        return true;
    }
    return false;
}

function addNewScriptFiles(tempDiv) {
    const promises = [];

    // Add inline script tags without src attributes first
    tempDiv.find('script:not([src])').each(function () {
        const scriptContent = $(this).html();
        let scriptExists = false;

        // Check if the same inline script content already exists in the body
        $('body script:not([src])').each(function () {
            if ($(this).html().trim() === scriptContent.trim()) {
                scriptExists = true;
                return false; // Stop the loop if a match is found
            }
        });

        // Append the inline script if it doesn't exist in the body
        if (!scriptExists) {
            try {
                $('body').append($(this).clone());
            } catch (error) {
                console.log('addNewScriptFiles body script:not([src])', error);
            }
        }
    });

    // Add external scripts with src attributes after inline scripts
    $('script[src]').each(function () {
        const currentSrc = $(this).attr('src');
        tempDiv.find('script[src]').each(function () {
            const newSrc = $(this).attr('src');
            if (currentSrc !== newSrc && !$('body').find(`script[src="${newSrc}"]`).length) {
                try {
                    const promise = $.getScript(newSrc);  // Load script asynchronously
                    promises.push(promise);
                    $('body').append($(this).clone());
                } catch (error) {
                    console.log('addNewScriptFiles script[src]', error);
                }
            }
        });
    });

    // Return a promise that resolves when all external scripts are loaded
    return $.when.apply($, promises);
}


// Replace inline CSS
function replaceInlineCSS(tempDiv) {
    const newStyle = tempDiv.find('style').text();
    $('head').find('style').text(newStyle);
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

function makeAjaxLoad() {
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