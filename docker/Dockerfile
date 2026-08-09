FROM rockylinux:8

# PHP 7.4 matches the versions pinned in composer.lock (twig 1.35, swiftmailer
# 5.4). Both are pre-PHP-8 and will fatal on 8.x. Bump this once the vendor
# dependencies are modernised.
ARG PHP_STREAM=remi-7.4
ARG FREESWITCH_VERSION=1.10.12

LABEL org.opencontainers.image.title="ictcore" \
      org.opencontainers.image.description="ICTCore unified communications framework: one REST API over voice, fax, SMS and email." \
      org.opencontainers.image.vendor="ICT Innovations" \
      org.opencontainers.image.licenses="MPL-2.0" \
      org.opencontainers.image.source="https://github.com/ictinnovations/ictcore" \
      org.opencontainers.image.url="https://ictcore.org"

# ── base repos ───────────────────────────────────────────────
# FreeSWITCH comes from Fedora Copr. SignalWire moved their own EL8 packages
# behind a paid token, and the okay.com.mx mirror that filled that gap signs
# every RPM with a key it never publishes, so neither can be verified. Copr is
# HTTPS, GPG signed and hosted on Fedora infrastructure.
#
# beaveryoga/broadvoice carries the libraries FreeSWITCH links against that EL8
# has no package for: sofia-sip, spandsp3, libks2 and signalwire-client-c2.
#
# PowerTools ships disabled on Rocky 8 and it is the only place libtiff-tools
# lives. The fax pipeline shells out to tiff2pdf and tiffcp, so it is required.
RUN dnf -y install dnf-utils epel-release wget curl unzip which procps-ng \
 && dnf -y config-manager --set-enabled powertools \
 && dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm \
 && curl -fsSL -o /etc/yum.repos.d/copr-freeswitch.repo \
      https://copr.fedorainfracloud.org/coprs/beaveryoga/FreeSWITCH-1.10.12/repo/epel-8/beaveryoga-FreeSWITCH-1.10.12-epel-8.repo \
 && curl -fsSL -o /etc/yum.repos.d/copr-freeswitch-deps.repo \
      https://copr.fedorainfracloud.org/coprs/beaveryoga/broadvoice/repo/epel-8/beaveryoga-broadvoice-epel-8.repo \
 && sed -i 's/^skip_if_unavailable=True/skip_if_unavailable=False/' /etc/yum.repos.d/copr-freeswitch*.repo \
 && dnf -y module reset php \
 && dnf -y module enable php:${PHP_STREAM} \
 && dnf -y module enable mariadb:10.11 \
 && dnf clean all

# ── runtime packages ─────────────────────────────────────────
RUN dnf -y install --setopt=install_weak_deps=False \
      httpd mod_ssl \
      php php-cli php-json php-gd php-mysqlnd php-imap php-xml php-mbstring php-process \
      php-pecl-imagick \
      mariadb-server mariadb \
      freeswitch freeswitch-config-vanilla freeswitch-lang-en \
      freeswitch-application-conference freeswitch-application-db \
      freeswitch-application-expr freeswitch-application-fifo \
      freeswitch-application-hash freeswitch-application-fsv \
      freeswitch-application-valet_parking freeswitch-application-httapi \
      freeswitch-application-voicemail freeswitch-application-curl \
      freeswitch-application-enum freeswitch-application-esf \
      freeswitch-codec-opus freeswitch-codec-passthru-g729 \
      freeswitch-codec-passthru-g723_1 freeswitch-codec-passthru-amr \
      freeswitch-format-native-file freeswitch-format-local-stream \
      freeswitch-format-tone-stream freeswitch-lua \
      sox libtiff-tools ghostscript ImageMagick \
      supervisor openssl \
 && dnf clean all && rm -rf /var/cache/dnf

# RHEL-family packaging hides the daemon in /usr/libexec and the name has moved
# between mysqld and mariadbd across versions. Resolve it once here so
# supervisord has a stable path and the build fails loudly if it disappears.
RUN MYSQLD=$(ls /usr/libexec/mariadbd /usr/libexec/mysqld /usr/sbin/mariadbd /usr/sbin/mysqld 2>/dev/null | head -1) \
 && test -n "$MYSQLD" \
 && ln -sf "$MYSQLD" /usr/local/bin/ictcore-mysqld \
 && echo "mariadb daemon: $MYSQLD"

# ── composer ─────────────────────────────────────────────────
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ── ICTCore itself, from this repository ─────────────────────
WORKDIR /usr/ictcore
COPY composer.json composer.lock ./
# composer update rather than install: the committed composer.lock predates the
# dompdf requirement in composer.json, so install refuses to run. Drift is bounded
# because composer.json caps twig at 1.x and swiftmailer at 5.x, which are the two
# that decide PHP 7.4 compatibility.
#
# policy.advisories.block=false because composer 2.9 refuses to resolve any package
# carrying a published advisory, and every version this codebase pins is old enough
# to have one. Turning the block off keeps the build honest about what it installs
# instead of silently upgrading past the PHP 7.4 ceiling. Modernising twig,
# swiftmailer, dompdf and php-jwt is the real fix and it belongs in composer.json.
RUN composer config --no-plugins policy.advisories.block false \
 && composer update --no-dev --no-interaction --no-progress --prefer-dist --ignore-platform-reqs \
 && composer clear-cache

COPY bin/   bin/
COPY core/  core/
COPY data/  data/
COPY db/    db/
COPY etc/   etc/
COPY docs/  docs/
COPY wwwroot/ wwwroot/

# ── wiring ───────────────────────────────────────────────────
# etc/http/ictcore.conf uses php_admin_value, which only exists under mod_php.
# Hence mod_php with the prefork MPM rather than php-fpm; swapping to fpm means
# rewriting those directives as PHP_ADMIN_VALUE in a pool file first.
RUN groupadd -f ictcore && id -u ictcore >/dev/null 2>&1 || useradd -g ictcore -M -s /sbin/nologin ictcore

RUN cp etc/http/ictcore.conf /etc/httpd/conf.d/ictcore.conf \
 && cp etc/php/ictcore.ini  /etc/php.d/99-ictcore.ini \
 && sed -i 's/^User .*/User ictcore/;s/^Group .*/Group ictcore/' /etc/httpd/conf/httpd.conf \
 && sed -i 's|#LoadModule mpm_prefork_module|LoadModule mpm_prefork_module|' /etc/httpd/conf.modules.d/00-mpm.conf \
 && sed -i 's|^LoadModule mpm_event_module|#LoadModule mpm_event_module|' /etc/httpd/conf.modules.d/00-mpm.conf \
 && mkdir -p /var/spool/mail/ictcore cache log \
 && chown -R ictcore:ictcore /usr/ictcore /var/spool/mail/ictcore

# ICTCore expects its FreeSWITCH fragments to appear inside the FreeSWITCH
# config tree; the upstream installer symlinks rather than copies so that an
# ICTCore upgrade takes effect without touching FreeSWITCH.
RUN for d in dialplan directory sip_profiles; do \
      mkdir -p /etc/freeswitch/$d; \
      ln -sf /usr/ictcore/etc/freeswitch/$d/ictcore.xml /etc/freeswitch/$d/ictcore.xml; \
    done

COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/mariadb.ini      /etc/supervisord.d/mariadb.ini
COPY docker/entrypoint.sh    /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

VOLUME ["/var/lib/mysql", "/usr/ictcore/data", "/usr/ictcore/log"]

# 80/443 web + REST, 5060 SIP, 8021 FreeSWITCH event socket,
# 16384-16484 RTP. Publish the RTP range too or you get signalling
# with no audio, which is the classic first-run complaint.
EXPOSE 80/tcp 443/tcp 5060/tcp 5060/udp 8021/tcp 16384-16484/udp

# The REST router answers an unrouted GET /api/ with 404, so a plain curl -f
# would report a working container as unhealthy. Anything under 500 means
# Apache and PHP are both alive; 000 or 5xx means they are not.
HEALTHCHECK --interval=30s --timeout=5s --start-period=120s --retries=3 \
  CMD code=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/api/) \
      && [ "$code" -ge 200 ] && [ "$code" -lt 500 ]

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf", "-n"]
