# Server OAuth

Flarum 2.0.2 / PHP 8.3 的 OAuth2 服务端扩展。

## 使用

后台「扩展 → Server OAuth」中管理应用、权限和令牌。

- 授权端点：`/oauth/authorize`
- 令牌端点：`/oauth/token`
- 用户信息：`/api/user?access_token=...`

授权码模式示例：

```
GET /oauth/authorize?client_id=CLIENT_ID&response_type=code&redirect_uri=https://example.com/callback&scope=user.read&state=STATE
POST /oauth/token
client_id=CLIENT_ID&client_secret=CLIENT_SECRET&grant_type=authorization_code&code=CODE&redirect_uri=https://example.com/callback
```

## 许可

MIT
