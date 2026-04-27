# DESIGN.md — Slip Management Application

## Overview

แอปพลิเคชันสำหรับจัดการสลิปโอนเงิน โดยใช้ OCR ดึงข้อมูลอัตโนมัติ พร้อมระบบวิเคราะห์รายจ่ายและจัดการกระเป๋าเงินหลายบัญชี

---

## Tech Stack

| Layer         | Technology               |
|---------------|--------------------------|
| Backend       | Laravel (PHP)            |
| Frontend      | Laravel Blade + Alpine.js |
| UI Framework  | TailwindCSS v3+          |
| OCR Engine    | (e.g. Google Vision API / Tesseract) |
| Charts        | Chart.js หรือ ApexCharts |
| Database      | MySQL / PostgreSQL        |

---

## Responsive Design

รองรับ 3 breakpoints หลักตาม TailwindCSS convention:

| Breakpoint | ขนาดหน้าจอ       | คำอธิบาย                          |
|------------|------------------|------------------------------------|
| `sm`       | ≥ 640px          | Mobile (portrait)                  |
| `md`       | ≥ 768px          | Tablet (portrait / landscape)      |
| `lg`       | ≥ 1024px         | Desktop                            |
| `xl`       | ≥ 1280px         | Desktop wide                       |

### แนวทาง Layout

- **Mobile-first**: เขียน base style สำหรับ mobile ก่อน แล้ว override ด้วย `md:` และ `lg:`
- **Navigation**:
  - Mobile → Bottom Tab Bar (5 icons)
  - Tablet → Side Drawer แบบ collapsible
  - Desktop → Fixed Sidebar
- **Dashboard Grid**:
  - Mobile → 1 column
  - Tablet → 2 columns
  - Desktop → 3–4 columns
- **Slip Upload Zone**: แสดงเป็น full-width card บน mobile, modal/panel บน desktop
- **Data Table (Transaction List)**:
  - Mobile → Card list แทน table
  - Tablet/Desktop → Standard table พร้อม sticky header

---

## Theme System (Light & Dark)

ใช้ TailwindCSS `dark:` variant ร่วมกับ `class` strategy (toggle `dark` class บน `<html>`) เพื่อรองรับทั้ง Light และ Dark theme

### เปิดใช้งานใน `tailwind.config.js`

```js
module.exports = {
  darkMode: 'class',
  // ...
}
```

### Color Tokens (CSS Variables)

```css
/* resources/css/app.css */
:root {
  /* Light Theme */
  --color-bg-base:       #F8FAFC;
  --color-bg-surface:    #FFFFFF;
  --color-bg-subtle:     #F1F5F9;
  --color-border:        #E2E8F0;

  --color-text-primary:  #0F172A;
  --color-text-secondary:#475569;
  --color-text-muted:    #94A3B8;

  --color-primary:       #3B82F6;   /* Blue 500 */
  --color-primary-hover: #2563EB;
  --color-success:       #10B981;
  --color-warning:       #F59E0B;
  --color-danger:        #EF4444;
}

.dark {
  /* Dark Theme */
  --color-bg-base:       #0F172A;
  --color-bg-surface:    #1E293B;
  --color-bg-subtle:     #334155;
  --color-border:        #334155;

  --color-text-primary:  #F8FAFC;
  --color-text-secondary:#CBD5E1;
  --color-text-muted:    #64748B;

  --color-primary:       #60A5FA;   /* Blue 400 */
  --color-primary-hover: #3B82F6;
  --color-success:       #34D399;
  --color-warning:       #FBBF24;
  --color-danger:        #F87171;
}
```

### Theme Toggle

- เก็บ preference ใน `localStorage` (`theme: 'light' | 'dark'`)
- ตรวจ `prefers-color-scheme` เป็น default หากยังไม่มีการตั้งค่า
- Toggle button อยู่บน Navbar (ทุก breakpoint)

```js
// Alpine.js snippet
document.addEventListener('alpine:init', () => {
  Alpine.store('theme', {
    dark: localStorage.theme === 'dark' ||
          (!localStorage.theme && window.matchMedia('(prefers-color-scheme: dark)').matches),
    toggle() {
      this.dark = !this.dark
      localStorage.theme = this.dark ? 'dark' : 'light'
      document.documentElement.classList.toggle('dark', this.dark)
    }
  })
})
```

---

## Features & UI Specification

---

### 1. ระบบ OCR และการดึงข้อมูลจากสลิป

#### 1.1 Slip Upload

**Component**: `SlipUploadCard`

- Drag-and-drop zone กว้างเต็มหน้าจอบน mobile
- รองรับ `image/jpeg`, `image/png`, `image/webp`
- Preview รูปภาพก่อน submit
- Progress bar ระหว่าง OCR processing (Polling หรือ WebSocket)

**Flow**:
```
User อัปโหลดรูป
  → Laravel รับไฟล์ (validation: max 10MB)
  → ส่งให้ OCR Service
  → Return extracted fields:
      - amount        (จำนวนเงิน)
      - datetime      (วันที่ + เวลา)
      - recipient     (ผู้รับเงิน / ร้านค้า)
      - sender        (ผู้โอน)
      - transaction_id (เลขอ้างอิง)
      - bank          (ธนาคาร)
  → แสดง Editable Review Form ก่อน save
```

**States**:
| State        | UI                                      |
|--------------|-----------------------------------------|
| Idle         | Upload zone พร้อม icon และ text         |
| Uploading    | Spinner + "กำลังวิเคราะห์สลิป..."      |
| Review       | Form แสดงข้อมูลที่ OCR ดึงมา (แก้ไขได้) |
| Success      | Toast + redirect ไปหน้า transaction     |
| Error (OCR)  | Alert พร้อมตัวเลือก "กรอกเอง"          |

#### 1.2 Duplicate Detection

- ตรวจสอบ `transaction_id` ก่อน save ทุกครั้ง
- หากซ้ำ → แสดง Warning Modal พร้อมลิงก์ไปหา transaction เดิม
- ไม่ block แต่ให้ผู้ใช้ยืนยันก่อน (เพราะอาจเป็นการโอนซ้ำจริง)

#### 1.3 E-Slip Verification *(Advanced)*

- ปุ่ม "ยืนยัน QR Code" บนหน้า Review Form
- Scan / decode QR จากรูปภาพ แล้วส่งไป verify กับ API ของธนาคาร (PromptPay Slip Verify)
- Badge แสดงสถานะ: `✓ ยืนยันแล้ว` / `⚠ ไม่สามารถยืนยัน` / `✗ ข้อมูลไม่ตรง`

---

### 2. การจัดหมวดหมู่และวิเคราะห์

#### 2.1 Auto-Categorization

**Category List (เริ่มต้น)**:

| ID  | หมวดหมู่       | Icon | ตัวอย่าง Keyword               |
|-----|----------------|------|-------------------------------|
| 1   | อาหาร & เครื่องดื่ม | 🍜 | 7-Eleven, MK, Grab Food       |
| 2   | ช้อปปิ้ง       | 🛍️ | Lazada, Shopee, Central       |
| 3   | เดินทาง        | 🚗 | Grab, BTS, PTT, Shell         |
| 4   | ค่าสาธารณูปโภค | 💡 | MEA, PWA, AIS, DTAC, True     |
| 5   | บันเทิง        | 🎬 | Netflix, Spotify, SF Cinema   |
| 6   | สุขภาพ         | 🏥 | โรงพยาบาล, Pharmacy, Watsons  |
| 7   | การเงิน        | 🏦 | ประกัน, กองทุน, เงินกู้        |
| 8   | อื่น ๆ         | 📌 | (fallback)                    |

**Rule Engine**: ใช้ keyword matching (case-insensitive) บนฟิลด์ `recipient` ก่อน OCR save โดยเก็บ rules ไว้ใน database เพื่อให้แก้ไขได้ง่าย

#### 2.2 Spending Dashboard

**Component**: `DashboardPage`

**Widgets**:

```
┌─────────────────────────────────────────────┐
│  ยอดใช้จ่ายเดือนนี้         ยอดคงเหลือรวม  │
│  ฿ 12,450.00               ฿ 38,200.00     │
├─────────────────┬───────────────────────────┤
│  Donut Chart    │  Bar Chart (รายสัปดาห์)   │
│  (หมวดหมู่)    │                            │
├─────────────────┴───────────────────────────┤
│  Top Spending Categories (Top 5)            │
│  รายการธุรกรรมล่าสุด (Last 5)              │
└─────────────────────────────────────────────┘
```

**Date Filter**: วันนี้ / สัปดาห์นี้ / เดือนนี้ / กำหนดเอง

**Responsive**:
- Mobile → Vertical stack, charts แบบ compact
- Tablet → 2-column grid
- Desktop → Side-by-side layout

#### 2.3 Budgeting

**Component**: `BudgetPage`

- กำหนด budget รายเดือน แยกตามหมวดหมู่
- Progress bar แสดง % การใช้จ่ายต่อ budget
- สีแสดงสถานะ: 🟢 ปกติ (<70%) → 🟡 ระวัง (70–90%) → 🔴 เกิน (>100%)
- แจ้งเตือน In-App Notification เมื่อใช้ครบ 80% และ 100%

---

### 3. การจัดการบัญชีและกระเป๋าเงิน

#### 3.1 Multi-Wallet Support

**Component**: `WalletPage`

**Wallet Types**:
- ธนาคาร (Bank Account) — แสดง logo ธนาคาร
- e-Wallet — PromptPay, TrueMoney, Line Pay
- เงินสด (Cash)

**Wallet Card** แสดง:
- ชื่อบัญชี / ประเภท
- ยอดคงเหลือปัจจุบัน
- รายการล่าสุด 3 รายการ
- ปุ่ม `ดูทั้งหมด` / `ปรับยอด`

**เลือก Wallet ตอน Upload Slip**: Dropdown/Selector ในหน้า Review Form

#### 3.2 Balance Adjustment

**Component**: `BalanceAdjustModal`

- กรอกยอดจริงจากหน้า App ธนาคาร
- ระบบบันทึกเป็น `adjustment` transaction (ไม่ใช่ expense/income)
- แสดง history การปรับยอด เพื่อ audit trail

---

## Page Structure

```
/                       → Redirect → /dashboard
/dashboard              → Dashboard (summary + charts)
/slips                  → รายการสลิปทั้งหมด
/slips/upload           → อัปโหลดสลิปใหม่
/slips/{id}             → รายละเอียดสลิป
/transactions           → รายการธุรกรรมทั้งหมด
/categories             → จัดการหมวดหมู่
/budgets                → ตั้งงบประมาณ
/wallets                → จัดการกระเป๋าเงิน
/wallets/{id}           → รายละเอียด wallet
/settings               → ตั้งค่า (theme, profile, notifications)
```

---

## Component Library (Shared UI)

| Component        | คำอธิบาย                                          |
|------------------|---------------------------------------------------|
| `<AppButton>`    | Primary / Secondary / Danger / Ghost variants     |
| `<AppInput>`     | Text input พร้อม label, error state, icon         |
| `<AppModal>`     | Dialog/Sheet รองรับ mobile swipe-to-dismiss       |
| `<AppToast>`     | Notification toast (success / warning / error)    |
| `<AppBadge>`     | Status badge (category, verification status)      |
| `<AppCard>`      | Surface container พร้อม shadow และ border         |
| `<AppSkeleton>`  | Loading placeholder                               |
| `<AppChart>`     | Wrapper สำหรับ Chart.js / ApexCharts              |
| `<ThemeToggle>`  | Light/Dark toggle button                          |

---

## Accessibility

- ใช้ semantic HTML (`<nav>`, `<main>`, `<section>`, `<article>`)
- `aria-label` บนทุก icon-only button
- Focus ring visible บนทุก interactive element
- Color contrast ratio ≥ 4.5:1 (WCAG AA) ทั้ง Light และ Dark theme
- Form fields มี `<label>` และ `aria-describedby` สำหรับ error messages

---

## File Structure (Frontend)

```
resources/
├── css/
│   └── app.css               # CSS variables + Tailwind imports
├── js/
│   ├── app.js                # Alpine.js init + theme store
│   └── components/
│       ├── slip-upload.js
│       └── charts.js
└── views/
    ├── layouts/
    │   ├── app.blade.php     # Main layout (nav, sidebar, theme)
    │   └── auth.blade.php
    ├── dashboard/
    │   └── index.blade.php
    ├── slips/
    │   ├── index.blade.php
    │   ├── upload.blade.php
    │   └── show.blade.php
    ├── wallets/
    ├── budgets/
    ├── categories/
    └── settings/
```

---

## Design Tokens Summary

| Token               | Light             | Dark              |
|---------------------|-------------------|-------------------|
| Background base     | `#F8FAFC`         | `#0F172A`         |
| Surface             | `#FFFFFF`         | `#1E293B`         |
| Border              | `#E2E8F0`         | `#334155`         |
| Text primary        | `#0F172A`         | `#F8FAFC`         |
| Text secondary      | `#475569`         | `#CBD5E1`         |
| Primary action      | `#3B82F6`         | `#60A5FA`         |
| Success             | `#10B981`         | `#34D399`         |
| Warning             | `#F59E0B`         | `#FBBF24`         |
| Danger              | `#EF4444`         | `#F87171`         |
| Border radius base  | `0.5rem` (8px)    | —                 |
| Border radius large | `1rem` (16px)     | —                 |
| Shadow sm           | `0 1px 3px rgba(0,0,0,.1)` | `0 1px 3px rgba(0,0,0,.4)` |
