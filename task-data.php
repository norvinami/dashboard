<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) {
    http_response_code(403);
    exit;
}
/** Stream every permitted task without retaining the full list in PHP or the browser. */
function dashboardTaskRows(): Generator {
    $after = 0;
    while (true) {
        $result = CTasks::GetList(
            ['ID' => 'ASC'], ['CHECK_PERMISSIONS' => 'Y', '>ID' => $after],
            ['ID', 'TITLE', 'RESPONSIBLE_ID', 'DEADLINE', 'STATUS'],
            ['USER_ID' => 5, 'CHECK_PERMISSIONS' => 'Y', 'NAV_PARAMS' => ['nTopCount' => 500]]
        );
        if (!is_object($result)) {
            throw new RuntimeException('Task query failed');
        }
        $count = 0;
        while ($row = $result->Fetch()) {
            $id = (int)$row['ID'];
            if ($id <= $after) {
                throw new RuntimeException('Task cursor did not advance');
            }
            $after = $id;
            $count++;
            $deadline = $row['DEADLINE'] ? MakeTimeStamp($row['DEADLINE']) : false;
            $done = (int)$row['STATUS'] === 5;
            yield ['id' => $id, 'title' => (string)$row['TITLE'],
                'manager' => (int)$row['RESPONSIBLE_ID'],
                'due' => $deadline ? date('Y-m-d', $deadline) : '',
                'done' => $done, 'overdue' => !$done && $deadline && $deadline < time()];
        }
        if ($count < 500) {
            return;
        }
    }
}
function dashboardTaskMatches(array $task, array $filter): bool {
    if (isset($filter['manager']) && $task['manager'] !== $filter['manager']) {
        return false;
    }
    $state = $filter['state'] ?? '';
    if ($state === 'overdue' && !$task['overdue']) { return false; }
    if ($state === 'open' && ($task['done'] || $task['overdue'])) { return false; }
    if ($state === 'done' && !$task['done']) { return false; }
    return empty($filter['search']) || mb_stripos($task['title'], $filter['search']) !== false;
}
function dashboardReadTasks(array $filter = [], int $cursor = 0): array {
    $summary = ['total' => 0, 'overdue' => 0, 'done' => 0, 'employees' => []];
    $rows = [];
    $hasMore = false;
    $lastId = $cursor;
    foreach (dashboardTaskRows() as $task) {
        if (!dashboardTaskMatches($task, $filter)) { continue; }
        $id = $task['manager'];
        if (!isset($summary['employees'][$id])) {
            $summary['employees'][$id] = ['total' => 0, 'overdue' => 0, 'done' => 0];
        }
        $summary['total']++;
        $summary['overdue'] += (int)$task['overdue'];
        $summary['done'] += (int)$task['done'];
        $summary['employees'][$id]['total']++;
        $summary['employees'][$id]['overdue'] += (int)$task['overdue'];
        $summary['employees'][$id]['done'] += (int)$task['done'];
        if ($task['id'] > $cursor) {
            if (count($rows) < 50) {
                $rows[] = $task;
                $lastId = $task['id'];
            } else {
                $hasMore = true;
            }
        }
    }
    return ['tasks' => $rows, 'summary' => $summary, 'cursor' => $lastId, 'hasMore' => $hasMore];
}
