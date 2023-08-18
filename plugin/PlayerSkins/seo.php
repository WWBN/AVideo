<?php
global $advancedCustom, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$videos_id = getVideos_id();
if (empty($videos_id)) {
    return '';
}
function extractKeywords($description) {
    // List of common stopwords
    $stopWords = ["a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "as", "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "could", "did", "do", "does", "doing", "down", "during", "each", "few", "for", "from", "further", "had", "has", "have", "having", "he", "he'd", "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "i", "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "it", "it's", "its", "itself", "let's", "me", "more", "most", "my", "myself", "nor", "of", "on", "once", "only", "or", "other", "ought", "our", "ours", "ourselves", "out", "over", "own", "same", "she", "she'd", "she'll", "she's", "should", "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves", "then", "there", "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through", "to", "too", "under", "until", "up", "very", "was", "we", "we'd", "we'll", "we're", "we've", "were", "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom", "why", "why's", "with", "would", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves"];

    // Convert description to lowercase and tokenize into words
    $words = str_word_count(strtolower($description), 1);

    // Filter out stopwords, words with length less than 3, and get the frequency of each word
    $filteredWords = array_filter($words, function($word) use ($stopWords) {
        return !in_array($word, $stopWords) && strlen($word) >= 3;
    });

    $wordFrequencies = array_count_values($filteredWords);

    // Sort by frequency
    arsort($wordFrequencies);

    // Return the top 10 words
    return array_slice(array_keys($wordFrequencies), 0, 10);
}

$video = new Video('', '', $videos_id);
$keywords = strip_tags($advancedCustom->keywords);
$relatedVideos = Video::getRelatedMovies($videos_id);
$keywords2 = extractKeywords(strip_tags($video->getTitle().''.$video->getDescription()));
$keywords3 = implode(', ', $keywords2);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="device_id" content="<?php echo getDeviceID(); ?>">
    <meta name="keywords" content=<?php printJSString($keywords.$keywords3); ?>>
    <link rel="manifest" href="<?php echo $global['webSiteRootURL']; ?>manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
    <link rel="shortcut icon" href="<?php echo $config->getFavicon(); ?>" sizes="16x16,24x24,32x32,48x48,144x144">
    <meta name="msapplication-TileImage" content="<?php echo $config->getFavicon(true); ?>">
    <meta name="robots" content="index, follow" />
    <meta name="description" content="<?php echo getSEODescription($video->getDescription(), 160); ?>">
    <?php
    getOpenGraph($videos_id);
    ?>
    <title><?php echo getSEOTitle($video->getTitle(), 75); ?></title>
</head>

<body>
    <section>
        <h1><?php echo $video->getTitle(); ?></h1>
        <video controls poster="<?php echo Video::getPoster($video->getId()); ?>" style="width: 100%;">
            <?php
            echo getSources($video->getFilename());
            ?>
            Your browser does not support the video tag.
        </video>
        <p><?php echo $video->getDescription(); ?></p>
        <?php
        getLdJson($videos_id);
        getItemprop($videos_id);
        ?>
    </section>
    <section>
        <h2><?php echo __('Related Videos'); ?></h2>
        <?php
        foreach ($relatedVideos as $key => $value) {
        ?>
            <article>
                <h3>
                    <a href="<?php echo Video::getURL($value['id']); ?>" title="<?php echo $value['title']; ?>">
                        <?php echo $value['title']; ?>
                    </a>
                </h3>
                <?php
                getLdJson($value['id']);
                getItemprop($value['id']);
                ?>
            </article>
        <?php
        }
        ?>
    </section>
</body>

</html>
<?php
exit;
?>