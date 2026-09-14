# Laravel Modules Generator

Package cung cấp các Artisan command để tạo cấu trúc module cho ứng dụng Laravel.

## Yêu cầu

- PHP 8.2 trở lên.
- Laravel 10, 11, 12 hoặc 13.

Phiên bản package phải được cài bằng tag ổn định. Ví dụ: `^1.0` sẽ chỉ nhận các bản tương thích theo Semantic Versioning; tránh dùng `dev-main` trong production.

## Cài đặt từ VCS riêng

Thêm repository thật vào `composer.json` của ứng dụng:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/<username>/laravel-modules.git"
    }
]
```

Sau khi repository đã có tag `v1.0.0`, cài đặt bằng:

```bash
composer require hieu-dev-92264/laravel-modules:^1.0
```

## Cấu hình

```bash
php artisan vendor:publish --tag=modules-config
```

Các cấu hình chính:

- `base_path`: nơi chứa modules, mặc định `app/Modules`.
- `namespace`: namespace của code sinh ra, mặc định `App\Modules`.
- `aliases`: ánh xạ tên rút gọn module.
- `api_prefix`: prefix cho route API, mặc định `api`.
- `stubs_path`: nơi ưu tiên đọc stub đã publish, mặc định `stubs/modules` trong ứng dụng.

Để tuỳ chỉnh template:

```bash
php artisan vendor:publish --tag=modules-stubs
```

Sau khi publish, generator sẽ ưu tiên từng file ở `stubs/modules`. Nếu một file hoặc thư mục này chưa tồn tại, package dùng stub nội bộ tương ứng.

## Commands

```bash
php artisan make:module Billing
php artisan module:controller InvoiceController Billing
php artisan module:dto CreateInvoiceData Billing
php artisan module:model Invoice Billing
php artisan module:service InvoiceService Billing
php artisan module:repo InvoiceRepository Billing
php artisan module:migration create_invoices_table Billing --create=invoices
php artisan module:migration add_status_to_invoices Billing --table=invoices
```

Tên module, controller, DTO, model, service và repository chỉ chấp nhận chữ và số sau khi được chuẩn hoá thành PascalCase. Migration chỉ chấp nhận chữ thường, số và dấu gạch dưới đơn. Không thể dùng đồng thời `--create` và `--table`.

## API response

`ApiResponse` trả về một contract thống nhất:

```json
{
    "message": "Success",
    "status_code": 200,
    "metadata": null,
    "path": "/api/billing/invoices",
    "timestamp": "2026-09-14T00:00:00.000000Z"
}
```

Exception debug chỉ được thêm khi `app.debug=true`; trace đã được lọc để không đưa argument nhạy cảm vào response. Exception handler của ứng dụng nên dùng cùng các trường `message`, `status_code` và `metadata` để giữ một API contract duy nhất.

## Kiểm tra package

```bash
composer validate --strict
composer dump-autoload --optimize --strict-psr
composer test
```

Xem `report.md` để biết các lỗi đã phát hiện, nguyên nhân và cách khắc phục.
