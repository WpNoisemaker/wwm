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
	--exclude 'config/config.php' \
	--exclude '*.log' \
	--exclude '.DS_Store' \
	--exclude '._*' \
	./ "$OUT/$NAME/"

# The installer writes this; it must not ship with one already in place.
rm -f "$OUT/$NAME/config/config.php"

# A writable spot for uploads, in case the host does not create it.
mkdir -p "$OUT/$NAME/public/assets/uploads"
printf 'Require all denied\n' > "$OUT/$NAME/public/assets/uploads/.htaccess-DENY-PHP"
cat > "$OUT/$NAME/public/assets/uploads/.htaccess" <<'HTA'
# Uploads are files, never programs. Even if something unexpected lands here, it cannot run.
<FilesMatch "\.(php|phar|phtml|pl|py|cgi|sh)$">
	Require all denied
	<IfModule !mod_authz_core.c>
		Order deny,allow
		Deny from all
	</IfModule>
</FilesMatch>
Options -ExecCGI -Indexes
AddHandler cgi-script .php .phtml .phar
HTA
rm -f "$OUT/$NAME/public/assets/uploads/.htaccess-DENY-PHP"

# Zip the CONTENTS, not the folder. Wrapping them in a wwm-platform/ directory meant File Manager's
# Extract made public_html/wwm-platform/..., so .htaccess and install.php never sat at the domain's
# root and the site did not load. The trailing "." also picks up dotfiles such as .htaccess.
( cd "$OUT/$NAME" && zip -qr "../$NAME.zip" . -x '*.DS_Store' )
rm -rf "$OUT/$NAME"

echo "Built $OUT/$NAME.zip"
du -h "$OUT/$NAME.zip" | cut -f1
