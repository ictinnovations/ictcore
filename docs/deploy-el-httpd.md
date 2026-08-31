# Apache (httpd) on RHEL / Rocky / Alma 8 and 9

On EL8/EL9 the packaged `httpd.service` ships with `ProtectSystem` enabled, which
makes `/usr` read-only inside Apache's own mount namespace. ICTCore stores data and
logs under `/usr/ictcore/data` and `/usr/ictcore/log`, so document upload and
conversion fail there even though the disk is writable and the Unix permissions are
correct. It is a systemd mount-namespace restriction, not a permissions problem, so
`chmod`/`chown` will not help.

Fix by installing the drop-in shipped at `etc/http/ictcore-httpd-rw.conf`:

    install -D -m 0644 etc/http/ictcore-httpd-rw.conf \
      /etc/systemd/system/httpd.service.d/ictcore-rw.conf
    systemctl daemon-reload
    systemctl restart httpd

Verify with:

    systemctl show httpd -p ReadWritePaths

This only applies where httpd runs under systemd (EL8/EL9 hosts); it is not needed
for the Docker image, which does not run Apache under this unit.
