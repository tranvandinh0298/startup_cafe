# Order lifecycle

## 1. Overview

### Success flow

draft → confirmed → paid → completed

### Cancel flow

draft → confirmed → paid → cancelled → completed

### Step meaning

| Status    | Ý nghĩa        | Có trừ kho? | Có rollback?    |
| --------- | -------------- | ----------- | --------------- |
| draft     | POS đang nhập  | ❌          | ❌              |
| confirmed | Order logic OK | ❌          | ❌              |
| paid      | Đã thu tiền    | ⏳ (đang)   | ✅              |
| completed | Trừ kho xong   | ✅ (đã)     | ⚠️ có điều kiện |
| cancelled | Hủy            | ❌          | ❌              |

> Rule #1:
> Kho CHỈ bị thay đổi trong paid → completed

## 2. Luồng chuẩn (canonical flow)

### 2.1 POS load

ProductAvailabilityService
→ hiển thị món + available_qty

### 2.2 Staff tạo order

draft
→ add items
→ update quantities (UI capped)

### 2.3 Trước khi thanh toán

OrderFeasibilityService
→ if IMPOSSIBLE → block
→ if REQUIRES_OPEN / RISKY → warning

### 2.4 Thanh toán

draft / confirmed
→ paid

### 2.5 Trừ kho (atomic)

InventoryConsumeService
→ inventory_actions (consume)
→ inventory_batches update
→ order.inventory_consumed_at set
→ order.status = completed

> Rule #2:
> Nếu consume fail → rollback toàn bộ transaction, order vẫn là paid nhưng inventory chưa đổi.

## 3. Rollback rules (cực quan trọng)

Khi được rollback?

-   order.inventory_consumed_at != null
-   order.status IN (paid, completed)
-   Chưa qua:
    -   kiểm kê
    -   chốt ca
    -   expire batch liên quan

Khi rollback:

-   KHÔNG xóa consume action
-   Ghi action mới rollback
-   Hoàn kho đúng batch

> Rule 3:
> InventoryAction là sổ cái. Không bao giờ xóa.

## 4. Những thứ TUYỆT ĐỐI KHÔNG LÀM

-   ❌ Trừ kho ở confirmed
-   ❌ Tự mở batch khi bán
-   ❌ Auto-modify order quantity
-   ❌ Dựa vào ProductAvailability để quyết định pay (chỉ để UI)

Nếu ai phá 1 trong các rule này → hệ thống sẽ sai ngầm.
