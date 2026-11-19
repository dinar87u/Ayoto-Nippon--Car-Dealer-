<?php

use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CarModelController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\LeasingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PriceListController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\SparepartAdminController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestLeadController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SimulasiController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\SparepartOrderController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\TestDriveController;
use App\Http\Controllers\VerifyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/mobil-baru', [CatalogController::class, 'home'])->name('mobil-baru');
Route::get('/katalog', [CatalogController::class, 'index'])->name('katalog.index');
Route::get('/mobil/{brand}/{model}', [CatalogController::class, 'model'])->name('katalog.model');
Route::get('/mobil/{slug}', [CatalogController::class, 'show'])->name('katalog.show');

Route::get('/simulasi-kredit', [SimulasiController::class, 'form'])->name('simulasi.form');
Route::post('/simulasi-kredit', [SimulasiController::class, 'hitung'])->name('simulasi.hitung');

// Test drive & tangkap niat SPK terbuka untuk tamu (lead dulu, akun belakangan).
Route::get('/test-drive', [TestDriveController::class, 'create'])->name('test-drive.create');
Route::post('/test-drive', [TestDriveController::class, 'store'])->middleware('throttle:10,1')->name('test-drive.store');
Route::get('/ajukan-spk', [GuestLeadController::class, 'create'])->name('spk.guest');
Route::post('/ajukan-spk', [GuestLeadController::class, 'store'])->middleware('throttle:6,1')->name('spk.guest.store');
Route::get('/ajukan-spk/terima-kasih', [GuestLeadController::class, 'sukses'])->name('spk.guest.sukses');

Route::middleware('auth')->group(function () {
    Route::get('/servis-booking', [ServiceController::class, 'create'])->name('servis.create');
    Route::post('/servis-booking', [ServiceController::class, 'store'])->name('servis.store');
    Route::get('/servis/invoice/{service}', [ServiceController::class, 'invoice'])->name('servis.invoice');

    Route::post('/sparepart/{sparepart}/pesan', [SparepartController::class, 'pesan'])->name('sparepart.pesan');

    Route::get('/pesanan-saya', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan-saya/spk', [PesananController::class, 'spk'])->name('pesanan.spk');
    Route::get('/pesanan-saya/test-drive', [PesananController::class, 'testDrive'])->name('pesanan.test-drive');
    Route::get('/pesanan-saya/servis', [PesananController::class, 'servis'])->name('pesanan.servis');
    Route::get('/pesanan-saya/sparepart', [PesananController::class, 'sparepart'])->name('pesanan.sparepart');
    Route::patch('/pesanan-saya/test-drive/{testDrive}/batal', [PesananController::class, 'batalTestDrive'])->name('pesanan.test-drive.batal');
    Route::patch('/pesanan-saya/test-drive/{testDrive}/jadwal', [PesananController::class, 'jadwalTestDrive'])->name('pesanan.test-drive.jadwal');
    Route::patch('/pesanan-saya/servis/{service}/batal', [PesananController::class, 'batalServis'])->name('pesanan.servis.batal');
    Route::patch('/pesanan-saya/servis/{service}/jadwal', [PesananController::class, 'jadwalServis'])->name('pesanan.servis.jadwal');
    Route::get('/sparepart/{sparepart}/beli', [SparepartOrderController::class, 'checkout'])->name('sparepart.order.checkout');
    Route::post('/sparepart/{sparepart}/beli', [SparepartOrderController::class, 'store'])->name('sparepart.order.store');
    Route::get('/sparepart/order/{no_spo}', [SparepartOrderController::class, 'show'])->where('no_spo', 'SPO-.*')->name('sparepart.order.show');
    Route::post('/sparepart/order/{no_spo}/upload', [SparepartOrderController::class, 'upload'])->name('sparepart.order.upload');
});

Route::get('/sparepart', [SparepartController::class, 'index'])->name('sparepart.index');

Route::get('/promo', [CmsController::class, 'promo'])->name('promo.index');
Route::get('/cabang', [CmsController::class, 'cabang'])->name('cabang.index');
Route::get('/faq', [CmsController::class, 'faq'])->name('faq.index');
Route::get('/sitemap.xml', [CmsController::class, 'sitemap'])->name('sitemap');
Route::get('/info/{slug}', [CmsController::class, 'info'])->name('info.show');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/data-diri', [ProfileController::class, 'dataDiri'])->name('profile.data-diri');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/spk/create', [SpkController::class, 'create'])->name('spk.create');
    Route::post('/spk/create', [SpkController::class, 'store'])->middleware('throttle:10,1')->name('spk.store');
    Route::get('/spk/{no_spk}', [SpkController::class, 'show'])->where('no_spk', 'SPK-.*')->name('spk.show');
    Route::post('/spk/{no_spk}/upload-bukti', [SpkController::class, 'upload'])->middleware('throttle:10,1')->name('spk.upload');
    Route::get('/invoice/{invoice}', [VerifyController::class, 'invoice'])->name('invoice.download');
});

// Operasional penjualan & prospek: Sales + Admin Cabang + Manajemen
Route::middleware(['auth', 'role:sales|admin-cabang|pimpinan|superadmin|owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/pesanan', [OrderController::class, 'index'])->name('pesanan.index');
    Route::patch('/pesanan/{order}', [OrderController::class, 'update'])->name('pesanan.update');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/followup', [LeadController::class, 'followup'])->name('leads.followup');
    Route::get('/test-drives', [TestDriveController::class, 'index'])->name('test-drives.index');
    Route::patch('/test-drives/{testDrive}', [TestDriveController::class, 'update'])->name('test-drives.update');
    Route::get('/units', [UnitController::class, 'index'])->name('units.index');
});

// Keuangan SPK, BAST, kelola unit, OTR cabang, promo & konten: Admin Cabang + Manajemen (tanpa Sales)
Route::middleware(['auth', 'role:admin-cabang|pimpinan|superadmin|owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verify', [VerifyController::class, 'index'])->name('verify.index');
    Route::patch('/verify/{order}', [VerifyController::class, 'update'])->name('verify.update');
    Route::get('/verify/{order}/file/{jenis}', [VerifyController::class, 'file'])->name('verify.file');
    Route::patch('/orders/{order}/bast', [VerifyController::class, 'bast'])->name('orders.bast');
    Route::get('/invoices/{invoice}', [VerifyController::class, 'invoice'])->name('invoices.download');
    Route::resource('/units', UnitController::class)->except(['index', 'show', 'destroy']);
    Route::resource('/price-lists', PriceListController::class)->except(['show', 'destroy']);
    Route::resource('/bank-accounts', BankAccountController::class)->except(['show', 'destroy']);
    Route::resource('/promos', PromoController::class)->except(['show', 'destroy']);
    Route::resource('/contents', ContentController::class)->except(['show', 'destroy']);
});

// Master global & manajemen akun: khusus Superadmin / Owner
Route::middleware(['auth', 'role:superadmin|owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('/brands', BrandController::class)->except(['show', 'destroy']);
    Route::resource('/models', CarModelController::class)->except(['show', 'destroy']);
    Route::resource('/variants', VariantController::class)->except(['show', 'destroy']);
    Route::resource('/colors', ColorController::class)->except(['show', 'destroy']);
    Route::resource('/leasings', LeasingController::class)->except(['show', 'destroy']);
    Route::resource('/branches', BranchController::class)->except(['show', 'destroy']);
    Route::resource('/hero-slides', HeroSlideController::class)->except(['show', 'destroy']);
    Route::resource('/users', UserController::class)->except(['show', 'destroy']);
});

// Laporan cabang & ekspor: Pimpinan, Admin Cabang, Service Advisor, Superadmin, Owner
Route::middleware(['auth', 'role:pimpinan|superadmin|owner|admin-cabang|service-advisor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{jenis}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::get('/laporan/{jenis}/export', [LaporanController::class, 'export'])->name('laporan.export');
});

// Bengkel - kelola sparepart & konfirmasi order part: Service Advisor + Admin Cabang + Manajemen (tanpa Mekanik & Sales)
Route::middleware(['auth', 'role:service-advisor|admin-cabang|pimpinan|superadmin|owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('/spareparts', SparepartAdminController::class)->except(['show', 'destroy']);
    Route::get('/sparepart-orders', [SparepartOrderController::class, 'index'])->name('sparepart-orders.index');
    Route::patch('/sparepart-orders/{order}', [SparepartOrderController::class, 'confirm'])->name('sparepart-orders.confirm');
});

// Bengkel - pengerjaan & booking servis: Service Advisor + Mekanik + Admin Cabang + Manajemen
Route::middleware(['auth', 'role:service-advisor|mekanik|admin-cabang|pimpinan|superadmin|owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::post('/services/{service}/detail', [ServiceController::class, 'addDetail'])->name('services.detail');
    Route::delete('/services/{service}/detail/{detail}', [ServiceController::class, 'destroyDetail'])->name('services.detail.destroy');
    Route::patch('/services/{service}/tanggapi', [ServiceController::class, 'tanggapi'])->name('services.tanggapi');
});

require __DIR__.'/auth.php';
