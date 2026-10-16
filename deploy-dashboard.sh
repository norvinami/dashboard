#!/usr/bin/env bash
# Updates only this dashboard, preserving the old directory outside the web root.
set -euo pipefail
source_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
target=/home/bitrix/www/dashboard-oksana-v1
files=(bootstrap.php index.php api.php view.php task-data.php date-range.php lead-data.php)
if [[ ! -d "$target" || -L "$target" ]]; then
  echo "Остановка: ожидается существующий каталог дашборда $target."
  exit 1
fi
for name in "${files[@]}"; do
  php -l "$source_dir/$name"
done
php "$source_dir/tests/tasks.php"
php "$source_dir/tests/leads-and-dates.php"
release=$(mktemp -d /home/bitrix/bavaria-dashboard-release.XXXXXX)
cp -a "$target/." "$release/"
for name in "${files[@]}"; do
  cp --remove-destination -T "$source_dir/$name" "$release/$name"
  chmod 644 "$release/$name"
done
chmod --reference="$target" "$release"
backup_root=$(mktemp -d /home/bitrix/bavaria-dashboard-backup.XXXXXX)
restore_on_failure() {
  if [[ ! -e "$target" && -d "$backup_root/dashboard" ]]; then
    mv -T "$backup_root/dashboard" "$target"
    echo "Предыдущая версия восстановлена."
  fi
}
trap restore_on_failure EXIT
mv -T "$target" "$backup_root/dashboard"
mv -T -n "$release" "$target"
test ! -d "$release"
echo "Дашборд обновлён. Резервная копия: $backup_root/dashboard"
