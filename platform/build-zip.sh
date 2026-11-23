#!/usr/bin/env bash
# Builds the upload package for shared hosting.
#   ./build-zip.sh  ->  dist/wwm-platform.zip
#
# What is left out: the live config, anything git, editor leftovers, and the dev-only tooling.
set -euo pipefail
cd "$(dirname "$0")"

OUT=dist
NAME=wwm-platform
rm -rf "$OUT/$NAME" "$OUT/$NAME.zip"
mkdir -p "$OUT/$NAME"

# Everything the server needs, and nothing it does not.
rsync -a \
	--exclude '.git*' \
	--exclude 'dist' \
	--exclude 'node_modules' \
	--exclude 'build-zip.sh' \
	--exclude 'smoke.php' \
	--exclude 'config/config.php' \
	--exclude 'public/assets/uploads/*' \
	--exclude 'storage/drive' \
	--exclude '*.log' \
	--exclude '.DS_Store' \
	--exclude '._*' \
	./ "$OUT/$NAME/"

# The installer writes this; it must not ship with one already in place.
rm -f "$OUT/$NAME/config/config.php"

# The stylesheet and script that browsers download go out without their explanatory comments
# (the copies in the repository keep them). Less to download, and much less for anyone to read.
php -r '
$css = $argv[1]; $js = $argv[2];
$c = file_get_contents($css);
$c = preg_replace("~/\*.*?\*/~s", "", $c);
$c = preg_replace("~\s*\n\s*~", "\n", $c);
$c = preg_replace("~\s*([{};])\s*~", "$1", $c);
file_put_contents($css, trim($c) . "\n");
$out = []; $inBlock = false;
foreach (explode("\n", file_get_contents($js)) as $line) {
	$t = trim($line);
	if ($inBlock) { if (strpos($t, "*/") !== false) { $inBlock = false; } continue; }
	if ($t === "" || strpos($t, "//") === 0) { continue; }
	if (strpos($t, "/*") === 0) { if (strpos($t, "*/") === false) { $inBlock = true; } continue; }
	$out[] = $t;
}
file_put_contents($js, implode("\n", $out) . "\n");
' "$OUT/$NAME/public/assets/css/wwm.css" "$OUT/$NAME/public/assets/js/wwm.js"

# A writable spot for uploads, in case the host does not create it.
mkdir -p "$OUT/$NAME/public/assets/uploads"
cat > "$OUT/$NAME/public/assets/uploads/.htaccess" <<'HTA'
# Uploads are files, never programs. Even if something unexpected lands here, it cannot run.
<FilesMatch "\.(php|phar|phtml|php[0-9]|pl|py|cgi|sh)$">
	Require all denied
	<IfModule !mod_authz_core.c>
		Order deny,allow
		Deny from all
	</IfModule>
</FilesMatch>
HTA

# Zip the CONTENTS, not the folder. Wrapping them in a wwm-platform/ directory meant File Manager's
# Extract made public_html/wwm-platform/..., so .htaccess and install.php never sat at the domain's
# root and the site did not load. The trailing "." also picks up dotfiles such as .htaccess.
( cd "$OUT/$NAME" && zip -qr "../$NAME.zip" . -x '*.DS_Store' )
rm -rf "$OUT/$NAME"

echo "Built $OUT/$NAME.zip"
du -h "$OUT/$NAME.zip" | cut -f1
