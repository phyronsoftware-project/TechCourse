# Bakong KHQR Can Scan Guide

## Purpose

This document explains how to make Bakong KHQR scan correctly in a Laravel project and avoid the common `QR Code has expired` problem.

It is prepared so you can:

- keep it as local project documentation
- reuse it in another project
- give it to Codex later as implementation guidance

## Main Problem

When using a local KHQR generation package, the QR may be generated in a format that Bakong banking apps reject.

Common symptom:

- phone scans the QR
- Bakong app shows `QR Code has expired`

## Real Root Cause

The issue was not:

- Bakong token
- Laravel timezone
- server UTC setting

The real cause was:

- dynamic KHQR `tag 99` structure was not aligned with the latest Bakong format

Latest-compatible dynamic KHQR requires:

- `creationTimestamp`
- `expirationTimestamp`

inside tag `99` as nested sub-tags.

## Working Fix

### 1. Use KHR for Bakong KHQR

Bakong KHQR should use:

- currency: `KHR`

Do not send decimal KHR values.

Normalize amount to whole riel:

- `5000`
- `12000`
- `41000`

Not:

- `5000.50`
- `99.99`

### 2. Generate per-order dynamic KHQR

Each payment should create a fresh QR for that order.

Recommended fields:

- amount
- currency = `KHR`
- bill number / payment number
- order number
- merchant account ID
- merchant name
- merchant city

### 3. Fix dynamic tag `99`

Dynamic KHQR must include:

- `00` = creation timestamp
- `01` = expiration timestamp

Both values should be milliseconds timestamp.

Example concept:

```text
99[length]
  00[length][creationTimestampMs]
  01[length][expirationTimestampMs]
```

After changing the payload:

- rebuild CRC

### 4. Use backend verification only

Payment success must happen only after backend verifies with Bakong Open API.

Do not trust frontend payment success.

Recommended verification references:

- `md5`
- `hash`
- `instruction_ref`
- `external_ref`

## Recommended Laravel Flow

### Create payment

Backend creates:

- payment record
- payment number
- KHQR string
- KHQR md5
- `expired_at`

### Frontend

Frontend should:

1. request payment creation
2. display QR
3. let user scan and pay
4. poll payment status every 5-10 seconds

### Backend status check

Backend should:

1. check if already success
2. check if expired
3. call Bakong Open API
4. compare amount/currency
5. mark success only when confirmed

## Important Config

Example `.env` values:

```env
BAKONG_OPEN_API_BASE_URL=https://api-bakong.nbc.org.kh
BAKONG_OPEN_API_TOKEN=your_real_token
BAKONG_KHQR_ACCOUNT_ID=your_account@bank
BAKONG_KHQR_MERCHANT_NAME="Your Merchant"
BAKONG_KHQR_MERCHANT_CITY="Phnom Penh"
BAKONG_KHQR_APP_NAME=YourApp
BAKONG_KHQR_APP_ICON_URL=https://your-domain.com/logo.png
BAKONG_KHQR_CALLBACK_URL=https://your-domain.com
BAKONG_KHQR_TOKEN=${BAKONG_OPEN_API_TOKEN}
BAKONG_KHQR_MODE=generated
BAKONG_KHQR_DYNAMIC_EXPIRE_MINUTES=10
```

## What To Avoid

Do not:

- fake payment success
- trust frontend success
- expose Bakong token to frontend
- use decimal KHR amount
- reuse stale QR for too long
- use static KHQR if you need exact order matching

## If You Want Static KHQR

Static KHQR can scan, but it is weak for automatic order matching.

If using static KHQR, you need extra matching logic such as:

- unique amount per order
- manual transaction reference input
- admin confirmation

For real automated product/course payment, dynamic KHQR is better.

## Codex Prompt For Another Project

Use this prompt in another Laravel project:

```text
Implement real Bakong KHQR dynamic payment integration in this Laravel project.

Requirements:
- Use KHR only for KHQR
- Normalize KHR amount to whole riel
- Generate per-order dynamic KHQR
- Ensure tag 99 contains:
  - 00 creationTimestamp
  - 01 expirationTimestamp
- Recalculate CRC after rewriting timestamp payload
- Store payment_no, khqr_string, khqr_md5, expired_at
- Verify payment only from backend with Bakong Open API
- Mark success only after backend confirmation
- Never trust frontend success
- Never expose Bakong token to frontend
- Add logs without logging secret token
- Keep old unrelated code unchanged
```

## Local Commands

After config/code changes:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

Docker:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan optimize:clear
```

## Final Note

If Bakong app still shows expired after this fix, compare the generated KHQR string field-by-field with the latest official Bakong SDK output.
