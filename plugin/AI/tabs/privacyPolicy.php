<div class="row">
    <div class="col-md-12">
        <h2>AI Services Pricing</h2>
        <p>We are excited to introduce new AI services provided by YPT, offering a range of functionalities to enhance your experience. Below are the details of the pricing for each service:</p>
        <ul>
            <li><strong>Basic Service:</strong> <span class="basicAIPrice">Loading...</span> per request. You may make as many requests as needed.</li>
            <li><strong>Transcription Service:</strong> <span class="transcriptionAIPrice">Loading...</span> per request. Generally, only one request is necessary.</li>
            <li><strong>Translation Service:</strong> <span class="translationAIPrice">Loading...</span> per language. The price is charged for each language translation request.</li>
        </ul>
        <p><strong>Note:</strong> Prices are subject to change at any time.</p>

        <h3>Terms and Conditions</h3>
        <p>Our AI services rely on the capabilities provided by Open AI. While we strive to deliver the best results, please note:</p>
        <ul>
            <li>All requests are subject to charges regardless of the outcome or satisfaction level. This includes cases where results are unsatisfactory or fail to meet specific expectations.</li>
            <li>The accuracy and quality of the results depend on various factors, including the complexity of the request and the performance of the underlying AI technology.</li>
        </ul>

        <h3>Privacy Policy</h3>
        <p>Your privacy is of utmost importance to us. In using our AI services, please be aware of the following:</p>
        <ul>
            <li>We take careful measures to ensure that your data is handled securely and responsibly.</li>
            <li>Your requests may be processed and stored to improve service quality and for operational purposes.</li>
            <li>We adhere to strict confidentiality and privacy standards and will not share your data with third parties without your consent, except as required by law or for technical processing needs.</li>
        </ul>

        <p>By using our AI services, you agree to these terms and our privacy policy. We look forward to serving you with these new, innovative capabilities.</p>
    </div>
</div>
<script>    
    function getAIPrices() {
        $.ajax({
            url: 'https://youphp.tube/marketplace/AI/prices.json.php',
            success: function(response) {
                if(response.basic !== undefined) {
                    $('.basicAIPrice').text(formatAICurrency(response.basic));
                }
                if(response.transcription !== undefined) {
                    $('.transcriptionAIPrice').text(formatAICurrency(response.transcription));
                }
                if(response.translation !== undefined) {
                    $('.translationAIPrice').text(formatAICurrency(response.translation));
                }
            }
        });
    }

    function formatAICurrency(value) {
        return '$' + parseFloat(value).toFixed(2);
    }

    $(document).ready(function() {
        getAIPrices();
    });
</script>
