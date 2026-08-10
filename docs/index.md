# ICTCore

ICTCore is an open source unified communications framework for developers and
integrators. It gives you one REST API and one set of terminology for voice
calls, fax, SMS and email, so a single application can answer a call, send a
fax and email the result without you learning three different stacks.

It's written in PHP and runs on Linux. Underneath it drives FreeSWITCH for
telephony, Kannel for SMS and Sendmail for email, but your code never talks to
those directly. You create a contact, pick a program, and fire a transmission.

## What you can build with it

Auto attendants, fax to email, email to fax, click to call, voice broadcasting,
appointment reminders, missed call alerts, and anything else that mixes those
channels together. The framework ships several programs for the common
scenarios, and you can add your own when none of them fit.

Three products are already built on top of it:

- [ICTFax](https://ictfax.com) for fax server deployments
- [ICTPBX](https://ictpbx.com) for multi-tenant IP PBX
- [ICTDialer](https://github.com/ictinnovations/ictdialer), the open source auto dialer

## Start here

New to ICTCore? Read these in order.

1. [Features](features.md) covers what the framework does.
2. [Basic components](intro_components.md) explains the five objects you'll
   work with every day: account, contact, message, program and transmission.
   Read this one before you write any code, because the rest of the docs
   assume it.
3. [REST API overview](api.md) gets you making calls.

## Try it in two minutes

The Docker image bundles Apache, PHP, FreeSWITCH and MariaDB, so there's
nothing else to install.

```bash
docker run -d --name ictcore \
  -p 8080:80 \
  -p 5060:5060/tcp -p 5060:5060/udp \
  -p 16384-16484:16384-16484/udp \
  ictinnovations/ictcore:latest
```

First boot takes about two minutes while the database is created and the schema
loads. After that the API answers on `http://localhost:8080/api/`.

!!! warning "Publish the RTP range"

    If you skip the `16384-16484/udp` mapping your calls will connect and then
    sit there with no audio. It's the single most common first-run problem.

For a production install from RPM packages, see
[system dependencies](dependencies.md) and the
[README](https://github.com/ictinnovations/ictcore#install).

## There are no webhooks

ICTCore has no outbound webhook or event stream, so nothing pushes a delivery
result back to you. Poll the transmission status until it leaves `pending`,
`processing`, `scheduled` or `ready`. Plan your integration around polling from
the start rather than discovering this later.

## Tools that talk to ICTCore

- [Postman collection](https://www.postman.com/tahiralmas/ict-innovations-apis/collection/57267778-45130f78-067b-4513-8cbb-5a3d6e4ed7f1)
  with every endpoint, including the six-call fax sequence in the order you
  need to run it
- [n8n-nodes-ictcore](https://www.npmjs.com/package/n8n-nodes-ictcore) for n8n workflows
- [pbx-mcp](https://github.com/ictinnovations/pbx-mcp), an MCP server that lets
  AI assistants inspect a live Asterisk or FreeSWITCH box
- [ansible-role-ictpbx](https://galaxy.ansible.com/ui/standalone/roles/tahiralmas/ictpbx/)
  to install the ICTPBX stack with one play

## Getting help

Report bugs and ask questions on
[GitHub issues](https://github.com/ictinnovations/ictcore/issues). Commercial
support is available from [ICT Innovations](https://www.ictinnovations.com).
