# Complex Manager update hosting

This directory contains the self-hosted WordPress update endpoint. Upload
update.php to wp.casasoft.com/complex-manager/ so installed plugins can
contact:

https://wp.casasoft.com/complex-manager/update.php

The updater follows the same version and info POST contract as CASAWP. It
always distributes latest.zip; versioned ZIPs are retained only as archives.

## Publishing a release

1. Update the plugin header, VERSION constant and the version/changelog in
   update.php.
2. Commit the release, then create a ZIP whose top-level directory is exactly
   complex-manager/ and whose plugin file is
   complex-manager/complex-manager.php.
3. If latest.zip exists, rename it to complex-manager-X.Y.Z.zip, using its
   current version.
4. Upload the new ZIP as latest.zip and upload the revised update.php.
5. On a staging WordPress site, trigger a plugin update check and confirm the
   offered version, details modal and update download URL.

The update client never downloads the archived ZIPs. They are there solely for
rollback and release history.
