# Laravel Modules Generator

Package nội bộ cung cấp bộ công cụ Artisan (Generator) để tự động hóa việc xây dựng cấu trúc Module cá nhân hóa trong các dự án Laravel.

## 1. Cài đặt (Private Repository)

Vì đây là package lưu trữ nội bộ (không công khai trên Packagist), bạn cần trỏ trực tiếp dự án về kho lưu trữ Git trước khi cài đặt.

**Bước 1:** Thêm block `repositories` vào file `composer.json` của dự án Laravel (dự án gốc):

```json
"repositories": [
    {
        "type": "vcs",
        "url": "[https://github.com/](https://github.com/)<username-cua-ban>/laravel-modules"
    }
]
```

**Bước 2:** Chạy lệnh cài đặt qua Composer:

```bash
composer require hieu-dev-92264/laravel-modules
```

## 2. Cấu hình (Configuration)

Mặc định, package đã có thể hoạt động ngay mà không cần cấu hình. Tuy nhiên, để linh hoạt điều chỉnh theo từng dự án, bạn nên xuất file cấu hình ra ngoài:

```bash
php artisan vendor:publish --tag="modules-config"
```

**File `config/modules.php` sinh ra có tác dụng gì?**
* **Thay đổi đường dẫn (`base_path` & `namespace`):** Mặc định các module được tạo ra ở `app/Modules`. Bạn có thể đổi vị trí này nếu dự án cấu trúc theo kiến trúc khác.
* **Thiết lập bí danh (`aliases`):** Cho phép định nghĩa các phím tắt (alias) để gọi lệnh nhanh hơn. Ví dụ, nếu bạn cấu hình `'org' => 'Organization'`, thay vì phải gõ dài dòng `php artisan make:module Organization`, bạn chỉ cần gõ `php artisan make:module org`.

## 3. Danh sách câu lệnh (Commands)

Bộ lệnh Artisan này giúp bạn sinh mã nguồn chuẩn hóa, giảm thiểu tối đa các thao tác lặp đi lặp lại.

### Tạo Module hoàn chỉnh
Lệnh này tự động tạo ra một thư mục module mới kèm theo toàn bộ hệ thống file cấu trúc bên trong (Controllers, Models, Repositories, Services, Routes, v.v.).

```bash
php artisan make:module {TênModule_hoặc_Alias}
```
*Ví dụ: `php artisan make:module UserManagement`*

### Tạo các thành phần đơn lẻ
Trong quá trình phát triển, nếu bạn cần tạo thêm một class hoặc một tầng cụ thể vào module đã tồn tại, hãy dùng các lệnh sau:

* **Tạo Controller:**
  ```bash
  php artisan make:module:controller {TênModule}
  ```
* **Tạo Service & Service Interface:** (Xử lý logic nghiệp vụ)
  ```bash
  php artisan make:module:service {TênModule}
  ```
* **Tạo Repository & Repository Interface:** (Giao tiếp với Database)
  ```bash
  php artisan make:module:repository {TênModule}
  ```
* **Tạo DTO (Data Transfer Object):**
  ```bash
  php artisan make:module:dto {TênModule}
  ```
* **Tạo Migration:**
  ```bash
  php artisan make:module:migration {TênModule}
  ```

## 4. Tùy biến mã nguồn (Stubs Customization)

Toàn bộ code được sinh ra từ lệnh Artisan dựa trên các file mẫu (stubs). Nếu dự án hiện tại của bạn có chuẩn code riêng, bạn có thể xuất các file mẫu này ra dự án để tự do chỉnh sửa:

```bash
php artisan vendor:publish --tag="modules-stubs"
```
Các file mẫu sẽ được đẩy ra thư mục `stubs/modules/` ở gốc dự án. Kể từ thời điểm này, các lệnh `make:module` sẽ ưu tiên sinh code theo file stub mới nhất mà bạn vừa chỉnh sửa.
