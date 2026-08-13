# API

A JSON API authenticated with bearer tokens. Every path below is relative to `/api`,
every request and response body is `application/json`, and
[/openapi.json](/openapi.json) is the generated contract.

## The envelope

Every response the API produces is the same object:

```json
{
  "success": true,
  "message": "UserShowResponse",
  "data": {},
  "type": "UserShowResponse"
}
```

## Errors

Errors use the same envelope with `success: false` and `type: "error"`:

```json
{
  "success": false,
  "message": "unauthorized",
  "errors": ["unauthorized"],
  "type": "error"
}
```

## Authentication

There is no login endpoint and no session: a bearer token is the only credential the
API accepts. Send it on every authenticated request:

```
Authorization: Bearer <token>
```

Get your first token from the web UI, under Settings → Credentials, where you can name
it and give it an optional expiry. The plain-text token is shown once and stored only as
a hash, so it cannot be read back — issue a new one instead.

Once you hold a token you can manage tokens without the UI: `GET /api/user/tokens` lists
them, `GET /api/user/tokens/{token}` reads one, and `POST /api/user/tokens` issues
another, which also accepts `abilities` and an `expires_at`. Tokens default to all
abilities and no expiry. `DELETE /api/user/tokens/{token}` revokes one by id, including
the token making the call.

## Abilities

An ability is one method reaching one path, written `GET:/api/user` — the path exactly as
the document keys it, so a templated segment stays templated: `DELETE:/api/user/tokens/{token}`.
A token granted `*` reaches everything, which is what a token is issued with unless you
say otherwise.

A request the token was not granted is refused with a `403` and
`"message": "missing_ability"` before it reaches the endpoint, so nothing is read, written
or validated. Abilities are set on a token when it is issued, and changed afterwards from
the web UI under Settings → Credentials, where each token has a grid of every endpoint
against every method it answers.

`GET /api/authenticated` reports whether the token you sent is currently accepted.

