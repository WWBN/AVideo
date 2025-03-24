<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:filterAndroidBackgroundLayout\\(\\) has parameter \\$layouts with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:filterIosAttachments\\(\\) has parameter \\$attachments with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:filterWebButtons\\(\\) has parameter \\$buttons with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:normalizeButtons\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:normalizeButtons\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:normalizeFilters\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\NotificationResolver\\:\\:normalizeFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/NotificationResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\SegmentResolver\\:\\:normalizeFilters\\(\\) has parameter \\$values with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/SegmentResolver.php',
];
$ignoreErrors[] = [
	// identifier: missingType.iterableValue
	'message' => '#^Method OneSignal\\\\Resolver\\\\SegmentResolver\\:\\:normalizeFilters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Resolver/SegmentResolver.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$success of class OneSignal\\\\Response\\\\Segment\\\\CreateSegmentResponse constructor expects bool, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/CreateSegmentResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#2 \\$id of class OneSignal\\\\Response\\\\Segment\\\\CreateSegmentResponse constructor expects non\\-empty\\-string, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/CreateSegmentResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$success of class OneSignal\\\\Response\\\\Segment\\\\DeleteSegmentResponse constructor expects bool, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/DeleteSegmentResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$callback of function array_map expects \\(callable\\(mixed\\)\\: mixed\\)\\|null, Closure\\(array\\)\\: OneSignal\\\\Response\\\\Segment\\\\Segment given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$totalCount of class OneSignal\\\\Response\\\\Segment\\\\ListSegmentsResponse constructor expects int\\<0, max\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#2 \\$array of function array_map expects array, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#2 \\$offset of class OneSignal\\\\Response\\\\Segment\\\\ListSegmentsResponse constructor expects int\\<0, 2147483648\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#3 \\$limit of class OneSignal\\\\Response\\\\Segment\\\\ListSegmentsResponse constructor expects int\\<0, 2147483648\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#4 \\$segments of class OneSignal\\\\Response\\\\Segment\\\\ListSegmentsResponse constructor expects list\\<OneSignal\\\\Response\\\\Segment\\\\Segment\\>, array\\<OneSignal\\\\Response\\\\Segment\\\\Segment\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Response/Segment/ListSegmentsResponse.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
