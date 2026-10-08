<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('BAVARIA_DASHBOARD_ALLOWED', true);
function MakeTimeStamp($value) { return strtotime($value); }
final class TaskResult {
    private array $rows;
    public function __construct(array $rows) { $this->rows = $rows; }
    public function Fetch() { return array_shift($this->rows) ?: false; }
}
final class CTasks {
    public static array $rows = [];
    public static int $queries = 0;
    public static bool $limitSupported = true;
    public static function GetList($order, $filter, $select, $params) {
        self::$queries++;
        if ($params['USER_ID'] !== 5 || $params['CHECK_PERMISSIONS'] !== 'Y' || $filter['CHECK_PERMISSIONS'] !== 'Y') {
            throw new RuntimeException('Permission check missing');
        }
        $rows = array_values(array_filter(self::$rows, fn($row) => $row['ID'] > $filter['>ID']));
        return new TaskResult(self::$limitSupported ? array_slice($rows, 0, $params['NAV_PARAMS']['nTopCount']) : $rows);
    }
}
for ($id = 1; $id <= 2607; $id++) {
    CTasks::$rows[] = ['ID' => $id, 'TITLE' => 'Task '.$id, 'RESPONSIBLE_ID' => $id % 2 ? 5 : 6,
        'DEADLINE' => $id % 3 === 0 ? '' : '2000-01-01 12:00:00', 'STATUS' => $id % 4 === 0 ? 5 : 2];
}
require dirname(__DIR__).'/task-data.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$page = dashboardReadTasks();
check($page['summary']['total'] === 2607, 'Full total must exceed old 2000 limit');
check($page['summary']['done'] === 651, 'Completed count');
check($page['summary']['overdue'] === 1304, 'Overdue count excludes done and no-deadline tasks');
check(count($page['tasks']) === 50 && $page['hasMore'] && $page['cursor'] === 50, 'First page size');
check(CTasks::$queries === 6, 'Keyset batches execute');
$next = dashboardReadTasks([], $page['cursor']);
check(count($next['tasks']) === 50 && $next['tasks'][0]['id'] === 51, 'Second page without duplicate rows');
check($next['summary'] === $page['summary'], 'Summary independent of cursor');
$last = dashboardReadTasks([], 2600);
check(count($last['tasks']) === 7 && !$last['hasMore'], 'Last page');
$employee = dashboardReadTasks(['manager' => 5]);
check($employee['summary']['total'] === 1304 && $employee['summary']['done'] === 0, 'Employee filtering');
$completed = dashboardReadTasks(['state' => 'done']);
check($completed['summary']['total'] === 651 && $completed['summary']['overdue'] === 0, 'Completed filter');
$none = dashboardReadTasks(['manager' => 99]);
check($none['tasks'] === [] && $none['summary']['total'] === 0 && !$none['hasMore'], 'Empty result');
$search = dashboardReadTasks(['search' => 'Task 2607']);
check($search['summary']['total'] === 1 && $search['tasks'][0]['id'] === 2607, 'Title search');
CTasks::$limitSupported = false;
$unlimited = dashboardReadTasks();
check($unlimited['summary'] === $page['summary'] && count($unlimited['tasks']) === 50, 'Totals stay correct if Bitrix ignores the row limit');
echo "PASS: 2607 tasks, totals, overdue semantics, employee/status filters, cursor pages, permission parameters. Mock API; live Bitrix still requires validation.\n";
