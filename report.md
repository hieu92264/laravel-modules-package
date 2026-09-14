# Báo cáo rà soát và khắc phục

Ngày rà soát: 2026-09-14.

## Phạm vi và cách kiểm tra

Đã kiểm tra cấu trúc PSR-4, metadata Composer, toàn bộ command/stub, service provider và trait. Kiểm tra tự động của package nằm tại `tests/verify.php`; ngoài ra cần chạy `composer validate --strict`, `composer dump-autoload --optimize --strict-psr` và PHP lint trước khi phát hành.

## Lỗi đã sửa

| Mức độ | Lỗi và nguyên nhân | Cách sửa |
| --- | --- | --- |
| Critical | `ApiResponse` khai báo namespace `...\Traits` nhưng nằm trong `src/traits`. PSR-4 biến namespace thành đường dẫn phân biệt hoa/thường; trên Linux Composer tìm `src/Traits/ApiResponse.php` và không tìm thấy. Optimized autoload đã báo class bị bỏ qua. | Đổi toàn bộ thư mục thành `src/Traits`, sửa namespace `HasBaseMetadata` và import trong `BaseModel` theo `...\Traits`. Test kiểm tra đường dẫn/namespace của các class trọng yếu. |
| Critical | Model stub import `HieuDev92264\LaravelModules\Bases\BaseModel`, trong khi class tồn tại ở namespace `...\Base\BaseModel`. Bất kỳ model sinh ra nào cũng lỗi class-not-found khi được autoload. | Sửa import trong `src/stubs/model.stub` thành `...\Base\BaseModel`. |
| High | README nói stub đã publish được ưu tiên, nhưng `stubsBasePath()` luôn trả về thư mục stub trong package. Người dùng publish rồi sửa file vẫn nhận code cũ. | Thêm `modules.stubs_path`, mặc định `base_path('stubs/modules')`. Generator ưu tiên stub đã publish theo từng file; nếu một file chưa được publish thì fallback về stub nội bộ. README được sửa để mô tả đúng hành vi. |
| High | `composer.json` không khai báo PHP hay Illuminate dependencies và đặt `minimum-stability: dev`. Composer không thể giải quyết/kiểm tra đúng môi trường hỗ trợ; package có thể bị cài vào Laravel/PHP không tương thích. | Khai báo PHP `^8.2` và các component Illuminate 10–13 được dùng trực tiếp; bỏ `minimum-stability: dev`. PHP 8.2 là cần thiết vì DTO stub dùng `readonly class`. |
| High | Base model và model stub dùng method `casts()`. Cách khai báo này phù hợp Laravel 11+, nhưng package khai báo hỗ trợ Laravel 10 sẽ tạo model không tương thích với API casts cũ. | Đổi model stub về property `$casts`; trait gọi `mergeCasts()` để luôn thêm casts metadata mà không ghi đè casts của model con. Vì vậy Laravel 10–13 cùng dùng được. |
| Medium | Debug response đưa nguyên `Throwable::getTrace()` vào JSON. `args` trong trace có thể chứa token, mật khẩu, request payload hoặc object không serialize được. | Chỉ trả tối đa năm frame với `file`, `line`, `class`, `type`, `function`; không trả `args`. Contract `message`, `status_code`, `metadata`, `path`, `timestamp` được giữ nguyên. Đồng thời reject HTTP status code ngoài 100–599 sớm, với lỗi rõ ràng. |
| Medium | Generator chỉ coi tên rỗng là không hợp lệ. Ký tự đặc biệt có thể lọt vào tên class/file tạo code PHP không hợp lệ hoặc khó bảo trì. | Sau chuẩn hoá, module/class chỉ được chấp nhận PascalCase chữ-số; migration chỉ chấp nhận chữ thường, số và `_` đơn. Thông báo Artisan giải thích quy tắc. |
| Medium | `module:migration` cho phép truyền cùng lúc `--create` và `--table`. Code cũ âm thầm ưu tiên `--create`, làm migration tạo sai kiểu so với ý định người dùng. | Command báo lỗi và dừng khi hai option cùng có mặt. |
| Low | Thứ tự `glob()` cho route/migration không được đảm bảo trên mọi filesystem. Cùng một route name hoặc migration path có thể có kết quả không lặp lại giữa máy CI/Linux/Windows. | Sort đường dẫn migration và route theo chuỗi trước khi register. Blueprint macro cũng được đăng ký trước khi đăng ký migration paths. |
| Low | `vendor/` và autoloader sinh bởi Composer bị commit. Chúng dễ stale, làm diff nhiễu, và không đại diện dependency của máy consumer. | Xoá `vendor/` khỏi Git và thêm `/vendor/` vào `.gitignore`. Consumer phải dùng Composer để cài dependencies. |
| Low | README có encoding hỏng, URL VCS không phải URL JSON hợp lệ và nhiều command không khớp signature thực tế (`make:module:controller` thay vì `module:controller`). | Viết lại README UTF-8, dùng URL VCS hợp lệ, nêu rõ command, version pin, config, response contract và lệnh kiểm tra. |

## Điều chưa thể tự động đồng bộ

Exception handler thuộc ứng dụng consumer, không nằm trong package này. Trait hiện chuẩn hoá response controller theo `message`, `status_code`, `metadata`, `path`, `timestamp`; để đồng nhất hoàn toàn, exception handler của từng ứng dụng phải trả đúng cùng contract. Package không nên tự thay đổi global handler của ứng dụng vì đó là thay đổi behavior ngoài phạm vi package.

## Hướng dẫn phát hành ổn định

1. Cài dependency development/CI và chạy các lệnh kiểm tra bên dưới.
2. Commit toàn bộ thay đổi.
3. Tạo tag SemVer, ví dụ `v1.0.0`, trên commit đã kiểm tra.
4. Consumer cài bằng constraint ổn định, ví dụ `composer require hieu-dev-92264/laravel-modules:^1.0`.
5. Khi có thay đổi breaking, tăng major version; không pin `dev-main` cho production.

## Lệnh xác minh trước release

```bash
composer validate --strict
composer dump-autoload --optimize --strict-psr
composer test
```

`composer test` chạy các regression check cho PSR-4, model stub, lựa chọn published stub, response contract và Composer metadata. Nó không thay thế integration test trong một ứng dụng Laravel thật; CI nên bổ sung test tạo module trong Laravel 10, 11, 12 và 13 nếu package cam kết hỗ trợ toàn bộ dải này.
