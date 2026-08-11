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

`POST /api/login` with `email`, `password` and `device_name` returns a token in
`data.token`. Send it on every authenticated request:

```
Authorization: Bearer <token>
```

The plain-text token is returned once and stored only as a hash, so it cannot be read
back — issue a new one instead. `POST /api/logout` revokes the token used to make the
call; `DELETE /api/user/tokens/{token}` revokes any other token by id.

A token can be issued directly with `POST /api/user/tokens`, which also accepts
`abilities` and an `expires_at`. Tokens default to all abilities and no expiry.

