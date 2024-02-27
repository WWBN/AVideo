/*experimental load page*/
// 1. Query a URL and process its contents
function queryAndProcessURL(url) {
    var urlA = addQueryStringParameter(url, 'avideoIframe', 1);
    $.ajax({
        url: urlA,
        type: 'GET',
        success: function (response) {
            // Assuming response is the full HTML of the page
            const tempDiv = $('<div></div>').html(response);

            // 2. Add new CSS files
            addNewCSSFiles(tempDiv);

            // 3. Replace .principalContainer HTML
            replacePrincipalContainer(tempDiv);

            // 7. Change the page title
            changePageTitle(tempDiv);

            // 8. Replace all <meta> tags
            replaceMetaTags(tempDiv);

            // 9. Change the current URL (using the History API for SPA behavior)
            history.pushState({}, '', url);
        }
    });
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
    // Clone the #mainNavBar to re-insert it later
    var mainNavBarClone = $('#mainNavBar').clone();

    // Check for .principalContainer in the response
    const newPrincipalContainer = tempDiv.find('.principalContainer').html();
    if (newPrincipalContainer) {
        console.log('replacePrincipalContainer principalContainer');
        $('.principalContainer').html(newPrincipalContainer);
    } else {
        // If no .principalContainer, replace the body's content directly
        console.log('replacePrincipalContainer with direct response');
        document.body.innerHTML = tempDiv.find('body').html(); // Use .html() on tempDiv directly

        // Prepend the cloned #mainNavBar to the body or to a specific container within the body
        //$('body').prepend(mainNavBarClone);
    }

    // Continue with additional operations
    // 4. Add new script files
    //addNewScriptFiles(tempDiv);

    // 5. Replace inline CSS
    //replaceInlineCSS(tempDiv);

    // 6. Replace inline JS
    //replaceInlineJS(tempDiv);
}



// Add new script files that are not already present
function addNewScriptFiles(tempDiv) {
    $('script[src]').each(function () {
        const currentSrc = $(this).attr('src');
        tempDiv.find('script[src]').each(function () {
            if (currentSrc !== $(this).attr('src') && !$('body').find(`script[src="${$(this).attr('src')}"]`).length) {
                $('body').append($(this).clone());
            }
        });
    });
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
