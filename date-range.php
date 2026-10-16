<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
function dashboardDateRange(array $query, DateTimeImmutable $today): array {
    $parse = static function ($value): DateTimeImmutable {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
            throw new InvalidArgumentException('Укажите даты в формате ГГГГ-ММ-ДД.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Указана несуществующая дата.');
        }
        return $date;
    };
    $from = $parse($query['from'] ?? $today->modify('first day of this month')->format('Y-m-d'));
    $to = $parse($query['to'] ?? $today->format('Y-m-d'));
    if ($from > $to) { throw new InvalidArgumentException('Дата «с» должна быть не позже даты «по».'); }
    $days = $from->diff($to)->days + 1;
    return ['from' => $from, 'to' => $to, 'previousFrom' => $from->modify('-'.$days.' days'),
        'previousTo' => $from->modify('-1 day')];
}
