<?php
/** Strict report dates; accept the legacy datepicker format for existing clients. */
function reportDateRange(array $input)
{
    $dates = [];
    foreach (['dateFrom', 'dateTo'] as $key) {
        $value = $input[$key] ?? null;
        $date = false;
        if (is_string($value) && preg_match('/\A(?:\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4})\z/', $value)) {
            foreach (['Y-m-d', 'm/d/Y'] as $format) {
                $candidate = DateTimeImmutable::createFromFormat('!' . $format, $value);
                if ($candidate && $candidate->format($format) === $value) {
                    $date = $candidate;
                    break;
                }
            }
        }
        if (!$date) {
            throw new InvalidArgumentException('Choose a valid start and end date.');
        }
        $dates[] = $date;
    }
    if ($dates[0] > $dates[1]) {
        throw new InvalidArgumentException('The start date must be on or before the end date.');
    }
    return [$dates[0]->format('Y-m-d 00:00:00'), $dates[1]->format('Y-m-d 23:59:59')];
}

function reportRequestDateRange(array $input)
{
    try {
        return reportDateRange($input);
    } catch (InvalidArgumentException $exception) {
        http_response_code(400);
        header('Content-Type: application/json');
        exit(json_encode(['error' => true, 'msg' => __($exception->getMessage()), 'data' => []]));
    }
}
