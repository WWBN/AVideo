<script>
    function aiNewTranslationAvailable(json){
        avideoToast(json);
        if(typeof loadAIUsage == 'function'){
            loadAIUsage();
        }
    }
</script>