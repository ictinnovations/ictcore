# Running ICTCore in Docker

This directory builds a single container with everything ICTCore needs: Apache
with mod_php, FreeSWITCH, and MariaDB. It's the fastest way to get a working
REST API you can send requests at.

There is no published `ictinnovations/ictcore` image. ICTCore is a framework
rather than a finished product, so the ready-made images are the ones that sit
on top of it, [ictfax](https://github.com/ictinnovations/ictfax) and
[ictdialer](https://github.com/ictinnovations/ictdialer). Each builds this same
stack and adds its own dashboard. Build from here when you want the bare API.

## Quick start

```bash
docker build -f docker/Dockerfile -t ictcore:dev .

docker run -d --name ictcore \
  -p 8080:80 \
  -p 5060:5060/tcp -p 5060:5060/udp \
  -p 16384-16484:16384-16484/udp \
  ictcore:dev
```

Give it about two minutes on first boot. The entrypoint has to initialise the
database and load ten schema files before Apache is any use. After that the API
answers on `http://localhost:8080/api/`.

Publish the RTP range. If you skip it your calls will connect and then sit
there in silence, which is the most common first-run complaint we get.

## Two databases, your choice

Leave `DB_HOST` alone and the container runs its own MariaDB. That's fine for
trying things out, less fine for anything you care about, since the data lives
inside the container.

Point `DB_HOST` at a real server and the bundled MariaDB never starts. The
entrypoint removes it from supervisord instead of leaving a second idle
database burning memory. See `docker-compose.yml` for a two-service setup.

```bash
docker compose up -d
```

## Environment variables

| Variable | Default | What it does |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | Anything other than localhost switches off the bundled database |
| `DB_PORT` | `3306` | |
| `DB_NAME` | `ictfax` | |
| `DB_USER` | `ictfaxuser` | |
| `DB_PASS` | generated | Required when `DB_HOST` is external. Generated and logged when the database is local |
| `DB_ROOT_PASS` | empty | Only used for provisioning the bundled database |
| `ICTCORE_HOST` | `localhost` | Goes into the `website` section of `ictcore.conf` |
| `FS_ESL_PASSWORD` | `ClueCon` | Written to both sides of the FreeSWITCH event socket. Change it |

## Volumes

- `/var/lib/mysql` for the bundled database
- `/usr/ictcore/data` for received faxes, recordings and uploads
- `/usr/ictcore/log` for application logs

## Where the packages come from

PHP comes from remi. FreeSWITCH comes from two Fedora Copr repositories,
`beaveryoga/FreeSWITCH-1.10.12` for the switch and `beaveryoga/broadvoice` for
the libraries EL8 has no package for (sofia-sip, spandsp3, libks2 and
signalwire-client-c2).

SignalWire moved their own EL8 packages behind a paid token. The okay.com.mx
mirror that people reach for next signs every RPM with key `C261E286186F7970`,
which it does not ship and no keyserver carries, so nothing from it can be
verified. Copr is HTTPS, GPG signed and hosted on Fedora infrastructure, and it
still saves the forty minute source compile.

PHP is pinned to 7.4 through the `PHP_STREAM` build argument. That isn't
nostalgia. `composer.lock` pins Twig 1.35 and Swiftmailer 5.4, and both of them
fatal on PHP 8. Once those dependencies are updated, change the argument:

```bash
docker build -f docker/Dockerfile --build-arg PHP_STREAM=remi-8.3 -t ictcore:dev .
```

## What's inside

supervisord runs the processes, because systemd in a container causes more
problems than it solves:

| Process | Priority | Notes |
|---|---|---|
| mariadb | 10 | Removed at startup when `DB_HOST` is external |
| freeswitch | 20 | |
| httpd | 30 | prefork MPM with mod_php |
| ictcore-cron | 40 | `bin/cron.php` in a sleep loop, since upstream runs it from crontab every minute |

## Health

The container reports healthy when `GET /api/` returns anything below a 500.
An unrouted request to the API root answers 404 by design, so a stricter check
would flag a perfectly good container as broken.
