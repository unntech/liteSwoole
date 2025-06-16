CHANGELOG
=========

### v2.0.1 `2025-06-15`
* 从`unntech/liteapi`引用版本, 优化PHP8强类型（严格模式），更多使用PHP8的新特性
* 增加 Model支持，把WebSocket消息结构统一，并规范uri字段值，把WebSocket消息也采用Controller处理，方便代码编写及读取
* 完善 authorize 签发 access_token 示例