> Этот сервис: `auth-service` является частью приложения [placemarkers-demo-workstation](https://github.com/Vlad812/placemarkers-demo-workstation).

# API Documentation: Auth Service

**Стек технологий:**
- PHP 8.5
- Symfony 8
- RoadRunner
- PostgreSQL
- Doctrine ORM
- LexikJWTAuthenticationBundle
- RabbitMQ

Сервис аутентификации и управления учётными записями. Отвечает за регистрацию, вход, подтверждение email, сброс пароля и выпуск пары access/refresh токенов. Пользователи и refresh-токены хранятся в **собственной базе данных** PostgreSQL (Database per Service) через **Doctrine ORM**.

Access token (JWT) выпускается и проверяется через **LexikJWTAuthenticationBundle**. Refresh-токены хранятся на сервере и объединяются в **token family**: при каждом обновлении выполняется rotation (старый токен помечается использованным, новый выдаётся в том же семействе). Повторное использование уже отозванного refresh-токена считается возможным угоном — отзывается всё семейство токенов.

События для email-уведомлений (регистрация, сброс пароля) публикуются в **RabbitMQ**.

**Формат ошибок (общий):**

```json
{
  "message": "Текст ошибки"
}
```

---

## Вход (Login)

Аутентифицирует пользователя и возвращает пару JWT-токенов.

**URL:** `/login`  
**Метод:** `POST`  
**Авторизация:** Не требуется (недоступен для уже авторизованных пользователей)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `email` | `string` | **Да** | Email пользователя. | `"user@example.com"` |
| `password` | `string` | **Да** | Пароль. | `"secret123"` |

#### Пример запроса

```http
POST /login HTTP/1.1
Content-Type: application/json
```

```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```

### Responses (Ответы)

#### 🟢 200 OK — Успешный вход

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "a1b2c3d4e5f6789012345678901234567890abcdef...",
  "expires_in": 3600,
  "refresh_expires_in": 2592000,
  "token_type": "Bearer"
}
```

| Поле | Тип | Описание |
| :--- | :--- | :--- |
| `access_token` | `string` | JWT access token |
| `refresh_token` | `string` | Refresh token для обновления сессии |
| `expires_in` | `integer` | Время жизни access token в секундах (по умолчанию 3600) |
| `refresh_expires_in` | `integer` | Время жизни refresh token в секундах (по умолчанию 2592000) |
| `token_type` | `string` | Тип токена (`Bearer`) |

#### 🔴 401 Unauthorized — Ошибка аутентификации

```json
{
  "message": "Invalid credentials"
}
```

```json
{
  "message": "Please confirm your email before signing in."
}
```

#### 🔴 403 Forbidden — Уже авторизован

```json
{
  "message": "Already authenticated. Log out before signing in again."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Invalid email format"
}
```

---

## Регистрация (Signup)

Создаёт нового пользователя и отправляет письмо для подтверждения email.

**URL:** `/signup`  
**Метод:** `POST`  
**Авторизация:** Не требуется (недоступен для уже авторизованных пользователей)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `email` | `string` | **Да** | Email пользователя. | `"user@example.com"` |
| `password` | `string` | **Да** | Пароль (минимум 8 символов). | `"secret123"` |

#### Пример запроса

```http
POST /signup HTTP/1.1
Content-Type: application/json
```

```json
{
  "email": "user@example.com",
  "password": "secret123"
}
```

### Responses (Ответы)

#### 🟢 201 Created — Успешная регистрация

```json
{
  "id": "123e4567-e89b-12d3-a456-426614174000",
  "email": "user@example.com",
  "message": "User registered successfully. Check your email to confirm the account."
}
```

#### 🔴 401 Unauthorized — Пользователь уже существует

```json
{
  "message": "User with this email already exists"
}
```

#### 🔴 403 Forbidden — Уже авторизован

```json
{
  "message": "Already authenticated. Log out before registering a new account."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Invalid email format"
}
```

```json
{
  "message": "Password too short"
}
```

---

## Обновление токена (Refresh Token)

Выдаёт новую пару access/refresh токенов по действующему refresh token (rotation).

**URL:** `/refresh`  
**Метод:** `POST`  
**Авторизация:** Не требуется

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `refresh_token` | `string` | **Да** | Действующий refresh token. | `"a1b2c3d4..."` |

#### Пример запроса

```http
POST /refresh HTTP/1.1
Content-Type: application/json
```

```json
{
  "refresh_token": "a1b2c3d4e5f6789012345678901234567890abcdef..."
}
```

### Responses (Ответы)

#### 🟢 200 OK — Токены обновлены

```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "f9e8d7c6b5a432109876543210fedcba98765432...",
  "expires_in": 3600,
  "refresh_expires_in": 2592000,
  "token_type": "Bearer"
}
```

#### 🔴 401 Unauthorized — Невалидный или использованный токен

```json
{
  "message": "Token is invalid"
}
```

```json
{
  "message": "Token has been reused - possible theft detected"
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Array does not have key \"refresh_token\""
}
```

---

## Выход (Logout)

Завершает сессию: отзывает refresh token (одно устройство) или все токены пользователя (все устройства).

**URL:** `/logout`  
**Метод:** `POST`  
**Авторизация:** Требуется (Bearer Token, `ROLE_USER`)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`
* `Authorization: Bearer <access_token>`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `refresh_token` | `string` | Нет | Refresh token текущего устройства. Если не передан — выход со всех устройств. | `"a1b2c3d4..."` |

#### Пример запроса

```http
POST /logout HTTP/1.1
Content-Type: application/json
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

```json
{
  "refresh_token": "a1b2c3d4e5f6789012345678901234567890abcdef..."
}
```

### Responses (Ответы)

#### 🟢 200 OK — Успешный выход

```json
{
  "message": "Logged out successfully"
}
```

#### 🔴 401 Unauthorized — Не авторизован

```json
{
  "message": "Authentication required."
}
```

---

## Подтверждение email (Confirm Email)

Подтверждает адрес электронной почты по токену из письма.

**URL:** `/confirm-email/{token}`  
**Метод:** `GET`  
**Авторизация:** Не требуется

### Request (Запрос)

#### Параметры пути (Path Parameters)

| Параметр | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `token` | `string` | **Да** | Токен подтверждения из email. | `"a1b2c3d4e5f6..."` |

#### Пример запроса

```http
GET /confirm-email/a1b2c3d4e5f6789012345678901234567890abcdef HTTP/1.1
```

### Responses (Ответы)

#### 🟢 200 OK — Email подтверждён

```json
{
  "message": "Email confirmed successfully"
}
```

#### 🔴 401 Unauthorized — Невалидный токен

```json
{
  "message": "Email confirmation token is invalid"
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Value \"\" is empty, but non empty value was expected."
}
```

---

## Запрос сброса пароля (Forgot Password)

Отправляет инструкции по сбросу пароля на email, если аккаунт существует. Ответ одинаковый в обоих случаях (защита от перебора email).

**URL:** `/forgot-password`  
**Метод:** `POST`  
**Авторизация:** Не требуется (недоступен для уже авторизованных пользователей)

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `email` | `string` | **Да** | Email пользователя. | `"user@example.com"` |

#### Пример запроса

```http
POST /forgot-password HTTP/1.1
Content-Type: application/json
```

```json
{
  "email": "user@example.com"
}
```

### Responses (Ответы)

#### 🟢 200 OK — Запрос принят

```json
{
  "message": "Если аккаунт с таким email существует, инструкции отправлены на почту."
}
```

#### 🔴 403 Forbidden — Уже авторизован

```json
{
  "message": "Already authenticated. Log out before requesting a password reset."
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Invalid email format"
}
```

---

## Сброс пароля (Reset Password)

Устанавливает новый пароль по токену из письма.

**URL:** `/reset-password`  
**Метод:** `POST`  
**Авторизация:** Не требуется

### Request (Запрос)

#### Заголовки (Headers)
* `Content-Type: application/json`

#### Тело запроса (JSON Body)

| Поле | Тип | Обязательное | Описание | Пример |
| :--- | :--- | :---: | :--- | :--- |
| `token` | `string` | **Да** | Токен сброса пароля из email. | `"a1b2c3d4..."` |
| `password` | `string` | **Да** | Новый пароль (минимум 8 символов). | `"newsecret123"` |

#### Пример запроса

```http
POST /reset-password HTTP/1.1
Content-Type: application/json
```

```json
{
  "token": "a1b2c3d4e5f6789012345678901234567890abcdef",
  "password": "newsecret123"
}
```

### Responses (Ответы)

#### 🟢 200 OK — Пароль изменён

```json
{
  "message": "Пароль успешно изменён."
}
```

#### 🔴 401 Unauthorized — Невалидный токен

```json
{
  "message": "Password reset token is invalid"
}
```

#### 🔴 422 Unprocessable Entity — Ошибка валидации

```json
{
  "message": "Password too short"
}
```

---

## Проверка состояния сервиса (Health Check)

Проверяет доступность сервиса. Используется для мониторинга и оркестрации (Docker, Kubernetes).

**URL:** `/health`  
**Метод:** `GET`  
**Авторизация:** Не требуется

### Request (Запрос)

#### Пример запроса

```http
GET /health HTTP/1.1
```

### Responses (Ответы)

#### 🟢 200 OK — Сервис доступен

```json
{
  "status": "ok"
}
```
