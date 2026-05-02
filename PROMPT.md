# Transaction Modal Prompt

## Overview

Create a **Transaction Modal** for adding a new transaction. The modal should support both **auto-fill via slip upload** and **manual input**.

---

## Features

### 1. 📷 Upload Slip (Auto-fill)

* Provide an area (dropzone or button) for users to upload a **bank slip image**.
* On upload, call the API:

```
POST https://api.easyslip.com/v2/verify/bank
```

#### Headers

```
Authorization: Bearer SLIP_VERIFY_API_KEY
Content-Type: multipart/form-data
```

#### Body

```
image: File
```

#### Behavior

* When the API responds successfully:

    * Extract and auto-fill the following fields:

        * Amount → `data.amountInSlip` or `rawSlip.amount.amount`
        * Date → `rawSlip.date`
        * Sender Name → `rawSlip.sender.account.name.th`
        * Sender Bank → `rawSlip.sender.bank.short`
        * Receiver Name → `rawSlip.receiver.account.name.th`
        * Transaction Reference → `rawSlip.transRef`

---

### 2. ✍️ Manual Input

Allow users to manually input transaction details:

* Amount
* Date
* Sender Name
* Sender Bank
* Receiver Name
* Transaction Reference

---

## Behavior

* If upload succeeds → auto-fill fields, but users can still edit values.
* If upload fails → show an error and allow manual input.
* Show loading state during upload.
* Validate inputs before submission.

---

## UX/UI Suggestions

* Show slip image preview after upload.
* Auto-fill immediately or provide a "Use this data" button.
* Highlight auto-filled fields.
* Ensure responsive design (mobile-friendly).

---

## Optional Enhancements

* Prevent duplicate uploads (debounce/throttle).
* Support drag & drop upload.
* Display parsing confidence (if available).
