<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Şubeler (Multi-branch destek)
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 5)->default('TR');
            $table->string('currency', 5)->default('TRY');
            $table->string('timezone', 50)->default('Europe/Istanbul');
            $table->string('language', 5)->default('tr');
            $table->string('logo')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kullanıcılara şube bağlantısı
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('role')->default('staff'); // super_admin, admin, technician, staff
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
        });

        // Müşteriler
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->nullable(); // Müşteri kodu
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('company_name')->nullable();
            $table->string('type')->default('individual'); // individual, corporate
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 5)->default('TR');
            $table->string('tax_number')->nullable();
            $table->string('tax_office')->nullable();
            $table->string('id_number')->nullable(); // TC Kimlik
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Cihaz Kategorileri
        Schema::create('device_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->default('fa-laptop');
            $table->string('color', 20)->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Cihazlar
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand');       // Marka: Apple, Samsung, Dell...
            $table->string('model');       // Model: iPhone 15, Galaxy S24...
            $table->string('serial_no')->nullable();
            $table->string('imei')->nullable();
            $table->string('barcode')->nullable();
            $table->year('manufacture_year')->nullable();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->string('storage')->nullable();  // 256GB, 512GB...
            $table->string('condition')->default('good'); // good, fair, poor
            $table->boolean('is_under_warranty')->default(false);
            $table->date('warranty_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Teknisyenler
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('speciality')->nullable(); // uzmanlık alanı
            $table->decimal('hourly_rate', 10, 2)->default(0); // saatlik ücret
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Servis Talepleri / İş Emirleri
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_no', 30)->unique(); // SRV-2026-00001
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status')->default('pending');
            // pending, diagnosed, in_progress, waiting_part, ready, delivered, cancelled

            $table->string('priority')->default('normal'); // low, normal, high, urgent

            $table->string('type')->default('repair'); // repair, maintenance, installation, inspection

            $table->text('problem_description'); // Müşterinin aktardığı sorun
            $table->text('diagnosis')->nullable(); // Teknisyen teşhisi
            $table->text('solution')->nullable(); // Uygulanan çözüm
            $table->text('internal_notes')->nullable();

            $table->string('device_condition_in')->nullable(); // Teslim alım durumu
            $table->text('accessories_received')->nullable(); // Teslim alınan aksesuarlar

            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);

            $table->datetime('received_at'); // Cihaz teslim alım tarihi
            $table->datetime('estimated_completion_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('delivered_at')->nullable();

            $table->boolean('customer_approval')->default(false); // Müşteri onayı
            $table->string('customer_signature')->nullable(); // İmza dosyası

            $table->timestamps();
            $table->softDeletes();
        });

        // Servis Notları & Durum Günlüğü
        Schema::create('service_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('note'); // note, status_change, customer_contact
            $table->text('note');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->boolean('is_visible_to_customer')->default(false); // müşteriye görünürlük
            $table->timestamps();
        });

        // Yedek Parça & Stok
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model_compatibility')->nullable(); // hangi cihazlarla uyumlu
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(5); // minimum stok uyarısı
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->string('unit', 20)->default('adet');
            $table->string('location')->nullable(); // depo rafı
            $table->string('supplier')->nullable();
            $table->string('warranty_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Yedek Parça Kullanım Kaydı
        Schema::create('spare_part_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('used'); // used, returned, added, adjusted
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Faturalar
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invoice_no', 30)->unique();
            $table->string('type')->default('service'); // service, product, combined
            $table->string('status')->default('draft'); // draft, sent, paid, partial, cancelled
            $table->string('currency', 5)->default('TRY');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(20); // KDV
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fatura Kalemleri
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('service'); // service, part, other
            $table->foreignId('spare_part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 20)->default('adet');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(20);
            $table->decimal('total', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Ödemeler
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ref_no', 50)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash'); // cash, card, bank, online
            $table->string('currency', 5)->default('TRY');
            $table->datetime('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('spare_part_usages');
        Schema::dropIfExists('spare_parts');
        Schema::dropIfExists('service_notes');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('technicians');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('device_categories');
        Schema::dropIfExists('customers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'role', 'phone', 'is_active']);
        });
        Schema::dropIfExists('branches');
    }
};
