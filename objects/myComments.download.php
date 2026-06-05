<?php
global $global, $config;
require_once __DIR__ . '/../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
}

$type = 'posted';
if (!empty($_GET['type']) && in_array($_GET['type'], ['received', 'all'], true)) {
    $type = $_GET['type'];
}

if ($type === 'all' && !User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}

$format = 'html';
if (!empty($_GET['format']) && $_GET['format'] === 'csv') {
    $format = 'csv';
}

// Images only make sense in HTML format
$includeImages = ($format === 'html') && !empty($_GET['includeImages']) && $_GET['includeImages'] == '1';

$_POST['sort'] = [];
$_POST['sort']['id'] = 'DESC';

setRowCount(1000000);

if ($type === 'all') {
    $comments = Comment::getAllPostedComments(true);
    $typeLabel = __('All Comments');
} elseif ($type === 'received') {
    $comments = Comment::getCommentsOnMyVideos(true);
    $typeLabel = __('Comments on My Videos');
} else {
    $comments = Comment::getMyPostedComments(true);
    $typeLabel = __('Comments I Wrote');
}

$comments = Comment::addExtraInfo2InRows($comments);

$exportDate = date('Y-m-d H:i');
$username = htmlspecialchars(User::getName(), ENT_QUOTES, 'UTF-8');

$fileBase = 'comments-' . $type . '-' . date('Ymd-His');

// ── Helpers ───────────────────────────────────────────────────────────────

function safeURL($url) {
    $url = trim($url ?? '');
    if ($url === '') {
        return '';
    }
    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https', 'data'], true)) {
        return '';
    }
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/** Short canonical URL for a video: webSiteRootURL + video/{id} */
function shortVideoURL($videos_id) {
    global $global;
    return rtrim($global['webSiteRootURL'], '/') . '/video/' . (int)$videos_id;
}

/**
 * Strip HTML tags and decode entities for use in plain-text contexts (CSV).
 * Preserves bare URLs so they remain clickable in spreadsheets.
 */
function commentToPlainText($commentWithLinks, $commentPlain) {
    $text = !empty($commentWithLinks) ? $commentWithLinks : ($commentPlain ?? '');
    // Extract bare URLs from <a href="..."> before stripping so they survive
    $text = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 [$1]', $text);
    // Replace <img ...> with its src so images show as URLs
    $text = preg_replace('/<img[^>]+src=["\']([^"\']+)["\'][^>]*\/?>/is', '[img: $1]', $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    return trim($text);
}

// ── CSV export ───────────────────────────────────────────────────────────
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileBase . '.csv"');
    // BOM for Excel UTF-8 detection
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');

    $headers = [__('Date'), __('Author'), __('Video Title'), __('Video URL'),
                __('Comment'), __('Reply Level'), __('Replies'), __('Likes'), __('Dislikes')];
    fputcsv($out, $headers);

    function csvRow($fh, $row, $level = 0) {
        $videoTitle = $row['video']['title'] ?? '';
        $videoURL   = shortVideoURL($row['videos_id'] ?? ($row['video']['id'] ?? 0));
        $commentTxt = commentToPlainText($row['commentWithLinks'] ?? '', $row['commentPlain'] ?? '');
        fputcsv($fh, [
            $row['created']      ?? '',
            strip_tags($row['identification'] ?? ($row['name'] ?? '')),
            $videoTitle,
            $videoURL,
            $commentTxt,
            $level,
            (int)($row['total_replies'] ?? 0),
            (int)($row['likes']    ?? 0),
            (int)($row['dislikes'] ?? 0),
        ]);
        if (!empty($row['responses'])) {
            foreach ($row['responses'] as $reply) {
                if (!is_array($reply)) { continue; }
                csvRow($fh, $reply, $level + 1);
            }
        }
    }

    foreach ($comments as $row) {
        if (!is_array($row)) { continue; }
        csvRow($out, $row, 0);
    }
    fclose($out);
    exit;
}

// ── HTML export ───────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileBase . '.html"');

function renderRepliesRows($replies, $includeImages, $depth = 1) {
    $html = '';
    if (empty($replies)) {
        return $html;
    }
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '&#x21b3; ';
    foreach ($replies as $reply) {
        if (!is_array($reply)) {
            continue;
        }
        $date        = htmlspecialchars($reply['created'] ?? '', ENT_QUOTES, 'UTF-8');
        $author      = htmlspecialchars(strip_tags($reply['identification'] ?? ($reply['name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $photoURL    = safeURL($reply['userPhotoURL'] ?? ($reply['photo'] ?? ''));
        $commentHTML = $reply['commentWithLinks'] ?? nl2br(htmlspecialchars($reply['commentPlain'] ?? '', ENT_QUOTES, 'UTF-8'));
        if (!$includeImages) {
            $commentHTML = preg_replace('/<img[^>]+>/is', '<span class="img-placeholder">&#x1F5BC; [image]</span>', $commentHTML);
        }
        $commentHTML = strip_tags($commentHTML, '<a><img><br><strong><b><em><i>');
        $likes       = (int)($reply['likes'] ?? 0);
        $dislikes    = (int)($reply['dislikes'] ?? 0);

        $photoCell = '';
        if ($includeImages) {
            $photoCell = '<td style="vertical-align:middle;text-align:center;">'
                . ($photoURL ? '<img src="' . $photoURL . '" alt="" style="width:32px;height:32px;border-radius:50%;"/>' : '')
                . '</td>';
        }
        $html .= '<tr style="background:#f9f9f9;">';
        $html .= $photoCell;
        $html .= '<td style="color:#888;font-size:0.85em;">' . $date . '</td>';
        $html .= '<td>' . $author . '</td>';
        $html .= '<td colspan="2" style="color:#555;">' . $indent . $commentHTML . '</td>';
        $html .= '<td style="text-align:center;color:#888;">&#x2194;</td>';
        $html .= '<td style="text-align:center;">&#x1F44D; ' . $likes . ' / &#x1F44E; ' . $dislikes . '</td>';
        $html .= '</tr>';

        if (!empty($reply['responses'])) {
            $html .= renderRepliesRows($reply['responses'], $includeImages, $depth + 1);
        }
    }
    return $html;
}


?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?> – <?php echo $username; ?></title>
<style>
  body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 20px; background: #fff; }
  h1 { font-size: 1.4em; margin-bottom: 4px; }
  .meta { color: #888; font-size: 0.85em; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #4a4a4a; color: #fff; padding: 8px 10px; text-align: left; font-size: 0.9em; }
  td { padding: 8px 10px; border-bottom: 1px solid #e0e0e0; vertical-align: top; font-size: 0.9em; }
  tr:hover td { background: #f5f5f5; }
  .video-title { font-weight: bold; }
  .video-link { font-size: 0.8em; color: #1a73e8; word-break: break-all; }
  td img.chatImage, td img { max-width: 220px; max-height: 180px; border-radius: 4px; display: block; margin: 4px 0; }
  .img-placeholder { display: inline-block; padding: 2px 6px; background: #f0f0f0; border: 1px dashed #bbb; border-radius: 4px; color: #888; font-size: 0.8em; }
  .empty { text-align: center; padding: 40px; color: #aaa; }
  @media print { body { margin: 10px; } }
</style>
</head>
<body>
<h1>&#x1F4AC; <?php echo htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></h1>
<p class="meta">
    <?php echo $username; ?> &nbsp;&bull;&nbsp; <?php echo $exportDate; ?>
    &nbsp;&bull;&nbsp; <?php echo count($comments); ?> <?php echo __('comments'); ?>
</p>
<?php if (empty($comments)): ?>
<p class="empty">&#x1F4AC; <?php echo __('No comments found.'); ?></p>
<?php else: ?>
<table>
  <thead>
    <tr>
      <?php if ($includeImages): ?><th style="width:44px;"><?php echo __('Photo'); ?></th><?php endif; ?>
      <th style="width:130px;"><?php echo __('Date'); ?></th>
      <th style="width:140px;"><?php echo __('Author'); ?></th>
      <th><?php echo __('Video'); ?></th>
      <th><?php echo __('Comment'); ?></th>
      <th style="width:60px;text-align:center;"><?php echo __('Replies'); ?></th>
      <th style="width:120px;text-align:center;"><?php echo __('Likes / Dislikes'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($comments as $row):
    if (!is_array($row)) { continue; }
    $date        = htmlspecialchars($row['created'] ?? '', ENT_QUOTES, 'UTF-8');
    $author      = htmlspecialchars(strip_tags($row['identification'] ?? ($row['name'] ?? '')), ENT_QUOTES, 'UTF-8');
    $photoURL    = safeURL($row['userPhotoURL'] ?? ($row['photo'] ?? ''));
    $commentHTML = $row['commentWithLinks'] ?? nl2br(htmlspecialchars($row['commentPlain'] ?? '', ENT_QUOTES, 'UTF-8'));
    if (!$includeImages) {
        $commentHTML = preg_replace('/<img[^>]+>/is', '<span class="img-placeholder">&#x1F5BC; [image]</span>', $commentHTML);
    }
    $commentHTML = strip_tags($commentHTML, '<a><img><br><strong><b><em><i>');
    $videoTitle  = '';
    $videoURL    = '';
    if (!empty($row['video'])) {
        $videoTitle = htmlspecialchars($row['video']['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $videoURL   = safeURL(shortVideoURL($row['videos_id'] ?? ($row['video']['id'] ?? 0)));
    }
    $totalReplies = (int)($row['total_replies'] ?? 0);
    $likes        = (int)($row['likes'] ?? 0);
    $dislikes     = (int)($row['dislikes'] ?? 0);
?>
    <tr>
      <?php if ($includeImages): ?>
      <td style="text-align:center;vertical-align:middle;">
        <img src="<?php echo $photoURL; ?>" alt="" style="width:40px;height:40px;border-radius:50%;"/>
      </td>
      <?php endif; ?>
      <td style="white-space:nowrap;"><?php echo $date; ?></td>
      <td><?php echo $author; ?></td>
      <td>
        <div class="video-title"><?php echo $videoTitle; ?></div>
        <?php if (!empty($videoURL)): ?>
        <div class="video-link"><a href="<?php echo $videoURL; ?>" target="_blank"><?php echo $videoURL; ?></a></div>
        <?php endif; ?>
      </td>
      <td><?php echo $commentHTML; ?></td>
      <td style="text-align:center;"><?php echo $totalReplies; ?></td>
      <td style="text-align:center;">&#x1F44D; <?php echo $likes; ?> / &#x1F44E; <?php echo $dislikes; ?></td>
    </tr>
<?php
    // Render nested replies
    if (!empty($row['responses'])) {
        echo renderRepliesRows($row['responses'], $includeImages, 1);
    }
endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</body>
</html>
