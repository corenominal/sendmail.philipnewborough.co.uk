# sendmail.philipnewborough.co.uk

A CodeIgniter 4 application that provides a centralised email queuing and delivery service. Other applications submit email messages via a REST API; messages are stored in a database queue and dispatched to recipients by a scheduled CLI command.

## Features

- **REST API** — accepts incoming email messages via `POST /api/message`, authenticated with a master API key
- **Message queue** — stores messages in a MySQL `messages` table with UUID tracking, soft deletes, and timestamps
- **CLI processor** — a cron-driven command (`cli/sendmail process`) that processes up to 10 pending messages per run, with a built-in rate limit of 80 messages per 60 minutes
- **Admin panel** — a protected web UI at `/admin` with DataTables server-side pagination, live stats (pending, sent today, sent this month, total), per-message actions (view, resend, delete), and a bulk "Delete Pending" action to clear all unsent messages from the queue in one step
- **`Sendmail` library** — a fluent PHP client library for submitting messages to this service from other applications

## API

### Submit a message

```
POST /api/message
Content-Type: application/json
apikey: <master-key>
```

**Required fields:** `from`, `to`, `subject`, `body`, `mailtype` (`text` or `html`)  
**Optional fields:** `cc`, `bcc`

Returns `201` with the created message object on success.

## CLI

Process the queue (typically invoked via cron):

```bash
sudo -u _www php /path/to/public/index.php cli/sendmail process
```

## Configuration

Copy `env` to `.env` and set the following values:

| Key | Description |
|---|---|
| `app.apiKeys.masterKey` | API key required by all API consumers |
| `app.urls.sendmail` | Base URL of this service (used by the `Sendmail` library) |
| `database.*` | Database connection settings |

## Requirements

- PHP 8.1+
- MySQL / MariaDB
- Composer

## Installation

```bash
composer install
php spark migrate
```