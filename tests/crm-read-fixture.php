<?php
if (PHP_SAPI!=='cli') { http_response_code(403); exit; }
class CCrmPerms {
    public static ?int $override=null;
    public function __construct(private int $id) {}
    public static function GetUserPermissions($id) { return new self(self::$override??(int)$id); }
    public function GetUserID() { return $this->id; }
}
function verifyDashboardReadOptions(array $options): void {
    if (!isset($options['PERMS'])||!($options['PERMS'] instanceof CCrmPerms)||$options['PERMS']->GetUserID()!==5) {
        throw new RuntimeException('Fixed owner read permissions missing');
    }
}
