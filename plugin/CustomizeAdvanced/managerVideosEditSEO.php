<div class="row">

    <div class="col-md-6">
        <label class="control-label" for="inputShortSummary" ><?php echo __("Short summary"); ?> <abbr title="H2 tags">(H2)<abbr></label>
        <textarea id="inputShortSummary" class="form-control" placeholder="<?php echo __("Short summary"); ?>"></textarea>
    </div>
    <div class="col-md-6">
        <label class="control-label" for="inputMetaDescription" ><?php echo __("Meta Description"); ?> </label>
        <textarea id="inputMetaDescription" class="form-control" placeholder="<?php echo __("Meta Description"); ?>"></textarea>
    </div>
    <div class="clearfix"></div>
    <hr>
    <div class="col-xs-12">
        <div class="alert alert-info">
            <strong><?php echo __('SEO Tips'); ?> </strong><br>
            <p>
                <strong><?php echo __('Clean Title'); ?> </strong><br>
                SEO best practices use hyphens between words because this tells the search engines and users where the breaks between words are and they are so much easier to read than one all the words smashed together.<br>
                Eliminate stop words (the, and, or, of, a, an, to, for, etc.) that do not need to be in your URL. Remove these words from your URL to make it shorter and more readable. You can see in the URL of this post that I removed the word “for” because it’s shorter and easier to read and remember.
            </p>
            <p>
                <strong><?php echo __('Short summary'); ?> </strong><br>
                Usually, Short summaries (H2 tags) are longer than titles (H1 tags) because they describe the subheadings regarding your video title.<br>
                It's always better to make H2 tags short and H1 tags shorter and to the point, also don't stuff it with unnecessary words because that will negatively affect your SEO.
            </p>
            <p>
                <strong><?php echo __('Meta Description'); ?> </strong><br>
                The meta description is a snippet of up to about 155 characters – a tag in HTML – which summarizes a page’s content. <br>
                Search engines show it in search results mostly when the searched-for phrase is within the description. So optimizing it is crucial for on-page SEO.
            </p>

        </div>

    </div>
</div>
<script>
    $(document).ready(function () {
        setupFormElement('#inputTitle', 35, 65, false, true);
        setupFormElement('#inputShortSummary', 70, 320, true, false);
        setupFormElement('#inputMetaDescription', 70, 320, true, false);
    });
</script>