# TLS certificate for the proxy container

This directory is bind-mounted read-only into the `proxy` container at
`/etc/caddy/certs`. Caddy expects `fullchain.pem` and `privkey.pem` here — see
`TLS_CERT_FILE`/`TLS_KEY_FILE` in `compose.prod.yaml`.

Nothing is generated in this directory automatically, and nothing in it is
committed (see `.gitignore`) — it holds a private key.

## Why a certificate is placed here instead of Caddy fetching its own

Caddy's automatic HTTPS needs to reach `acme-v02.api.letsencrypt.org` directly
from this host. Where that's blocked — documented in
`infrastructure/production/README.md` for the case this repo was built
against — it never succeeds, and no CDN in front is a workaround either if the
CDN's edge can't route to the origin at all (also documented there).

## Getting a certificate

Run this from a workstation that can reach both Let's Encrypt and this
zone's DNS provider — it does not need to reach the origin server at all,
because DNS-01 proves domain control via a TXT record, not an HTTP callback:

```bash
certbot certonly --manual --preferred-challenges dns \
    --agree-tos -m you@example.com \
    -d shop.example.com -d admin.example.com
```

Certbot pauses per domain asking you to publish a
`_acme-challenge.<domain> TXT "<value>"` record. Create it with your DNS
provider, wait for it to resolve (`dig TXT _acme-challenge.<domain>`), then
continue. The result is a single certificate covering both names at
`/etc/letsencrypt/live/shop.example.com/{fullchain,privkey}.pem`.

Copy those two files here as `fullchain.pem` and `privkey.pem`, then:

```bash
docker compose -f compose.prod.yaml --env-file .env.production \
    up -d --pull never --force-recreate proxy
```

## Renewal

Let's Encrypt certificates are valid 90 days. Nothing renews this
automatically — repeat the steps above before it expires and restart `proxy`.
Automating it needs either a DNS provider API certbot has a plugin for (so the
TXT record can be created without a human) or DNS-01 propagation delegated
to a script; neither is wired up here.
