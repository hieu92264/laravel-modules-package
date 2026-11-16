# Laravel Modules Generator

Laravel Modules Generator giúp tạo cấu trúc module thống nhất, các thành phần của module, migration và Postman Collection cho ứng dụng Laravel.

## Yêu cầu

- PHP 8.2+ cho Laravel 10–12; PHP 8.3+ cho Laravel 13.
- Laravel 10, 11, 12 hoặc 13.
- Extension GD (`ext-gd`) khi dùng tính năng chuyển đổi sang WebP.

Cài đặt bản phát hành ổn định đã được gắn tag trong môi trường production:

```bash
composer require hieu-dev-92264/laravel-modules:^1.0
```

Nếu dùng repository VCS riêng tư, hãy thêm URL Git thực tế vào mục `repositories` trong `composer.json` của ứng dụng trước khi cài package.

## Cập nhật package trong các ứng dụng đang sử dụng

Composer chỉ cập nhật đến các phiên bản đã được gắn tag và push lên remote. Vì vậy, trước hết cần tạo một tag mới từ commit đã kiểm thử ở repository package:

```bash
git tag v1.0.1
git push origin v1.0.1
```

Sau đó, trong từng ứng dụng Laravel đang sử dụng package, chạy:

```bash
composer clear-cache
composer update hieu-dev-92264/laravel-modules --with-all-dependencies
composer show hieu-dev-92264/laravel-modules
php artisan optimize:clear
```

Lệnh `composer show` cần hiển thị phiên bản tag mới, ví dụ `v1.0.1`. Nếu Composer báo không có gì để cập nhật, hãy kiểm tra:

- Tag mới đã được push đúng remote mà ứng dụng khai báo trong `repositories`.
- Phiên bản yêu cầu trong `composer.json` của ứng dụng là `"hieu-dev-92264/laravel-modules": "^1.0"`.
- Không dùng nhánh `dev-main` trong production; hãy phát hành một tag mới cho mỗi bản ổn định.

Khi cập nhật, không chạy `vendor:publish --tag=modules-config --force` nếu chưa sao lưu hoặc so sánh cấu hình hiện tại, vì lệnh này sẽ ghi đè `config/modules.php` đã được tuỳ chỉnh. Tương tự, chỉ dùng `--force` khi publish stubs nếu muốn thay thế các stub đã chỉnh sửa.

## Cấu hình và stubs

```bash
php artisan vendor:publish --tag=modules-config
php artisan vendor:publish --tag=modules-stubs
```

`modules.php` cấu hình đường dẫn gốc của module, namespace được sinh, aliases, tiền tố API và vị trí các stub đã publish. Mỗi stub đã publish được ưu tiên sử dụng; stub từ package là phương án dự phòng.

## Các lệnh

```bash
php artisan make:module Billing
php artisan module:controller InvoiceController Billing
php artisan module:dto CreateInvoiceData Billing
php artisan module:model Invoice Billing
php artisan module:service InvoiceService Billing
php artisan module:repo InvoiceRepository Billing
php artisan module:migration create_invoices_table Billing --create=invoices
php artisan module:migration add_status_to_invoices Billing --table=invoices
php artisan module:postman --output=postman_api.json
```

Tên module và class được chuẩn hoá sang PascalCase, chỉ được chứa chữ cái và số. Tên migration được chuẩn hoá sang snake_case viết thường. Các generator không ghi đè file đã tồn tại trừ khi dùng `--force`. Lệnh migration không cho phép dùng đồng thời `--create` và `--table`.

## Tạo Postman Collection

`module:postman` quét các route đã đăng ký có namespace controller khớp với `modules.namespace`, nhóm chúng theo module và ghi ra tệp Postman Collection v2.1 JSON. Các route dùng middleware xác thực được cấu hình sẽ nhận xác thực bearer token tương ứng.

```php
// config/modules.php
'postman' => [
    'auth_middleware' => ['auth', 'auth:*'],
    'auth_type' => 'bearer',
    'token_variable' => 'access_token',
    'base_url_variable' => 'base_url',
],
```

Base URL của collection kết hợp `app.url` và `modules.api_prefix`; các tham số route Laravel không bắt buộc sẽ trở thành tham số Postman dạng `:parameter`.

## Upload file và WebP

`HieuDev92264\LaravelModules\Common\FileHelper` lưu `UploadedFile` vào thư mục cục bộ và trả về metadata:

```php
use HieuDev92264\LaravelModules\Common\FileHelper;

$file = FileHelper::save($request->file('document'), storage_path('app/uploads'));
$image = FileHelper::saveAsWebp($request->file('avatar'), storage_path('app/uploads'));

// path, filename, original_name, extension, mime_type, size
```

`saveAsWebp()` nhận các ảnh hợp lệ được driver `buglinjo/laravel-webp` đã cấu hình hỗ trợ. Hãy publish và cấu hình dependency này trước khi dùng tính năng chuyển đổi:

```bash
php artisan vendor:publish --provider="Buglinjo\LaravelWebp\WebpServiceProvider" --tag=config
```

Driver `php-gd` mặc định cần `ext-gd`; hoặc có thể cấu hình driver `cwebp` cùng đường dẫn file thực thi trong `config/laravel-webp.php`.

## API response

Trait controller được sinh trả về response theo một cấu trúc nhất quán:

```json
{
  "message": "Success",
  "status_code": 200,
  "metadata": null,
  "path": "/api/billing/invoices",
  "timestamp": "2026-11-16T00:00:00.000000Z"
}
```

Thông tin chẩn đoán exception chỉ được bổ sung khi `app.debug=true`; các đối số của trace không bao giờ được trả về.

## Kiểm tra khi phát triển

Thư viện này chủ ý không commit `composer.lock`; CI sẽ resolve dependency cho từng phiên bản Laravel được hỗ trợ.

```bash
composer validate --strict
composer dump-autoload --optimize --strict-psr
composer test:lint
composer test
composer audit
```

GitHub Actions chạy bộ kiểm tra này cho Laravel 10–13. Laravel 13 được kiểm tra với PHP 8.3; Laravel 10–12 được kiểm tra với PHP 8.2.

Laravel 10 vẫn được kiểm tra khả năng tương thích với package, nhưng dòng dependency này không còn nhận bản vá cho các cảnh báo bảo mật hiện tại. Cổng kiểm tra bảo mật của CI vì vậy audit theo dependency baseline Laravel 13; các ứng dụng đang dùng Laravel 10 nên lên kế hoạch nâng cấp.
