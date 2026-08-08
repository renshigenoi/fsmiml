<template>
    <div class="fsm-app">

            <!-- ============ UPDATE BANNER (hanya muncul di dalam APK) ============ -->
            <div v-if="updateInfo" class="update-banner" @click="!updateDownloading && downloadUpdate()">
                <span v-if="updateDownloading">⬇️ Mengunduh pembaruan… {{ updateProgress }}%</span>
                <span v-else>🔄 Versi baru {{ updateInfo.version }} tersedia — ketuk untuk unduh</span>
                <button v-if="!updateInfo.required && !updateDownloading" type="button" class="ub-close"
                    @click.stop="updateInfo = null">✕</button>
            </div>

            <!-- ============ OFFLINE BANNER ============ -->
            <div v-if="!online" class="offline-banner">📡 Offline — data akan dimuat ulang saat online</div>

            <!-- ============ PIN LOCK SCREEN ============ -->
            <div v-if="token && view === 'lock'" class="lock-screen">
                <div class="lock-logo-wrap">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="lock-title">Masukkan PIN</div>
                <div class="lock-sub">Buka aplikasi FSM Teknisi</div>
                <div class="pin-dots">
                    <span v-for="i in 6" :key="i" class="pin-dot"
                        :class="{ filled: i <= pinEntry.length }"></span>
                </div>
                <div class="pin-error">{{ pinError }}</div>
                <div class="pin-pad">
                    <button v-for="n in [1,2,3,4,5,6,7,8,9]" :key="n" type="button" class="pin-key"
                        @click="pinKey(String(n))">{{ n }}</button>
                    <button v-if="biometricAvailable && bioEnabled" type="button" class="pin-key pin-key-bio"
                        @click="tryBiometric">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 10a2 2 0 0 0-2 2c0 1.5.2 3-.5 4.5" />
                            <path d="M8 11a4 4 0 0 1 6.9-2.8" />
                            <path d="M14.5 12c0 2.5-.3 4.4-1 6" />
                            <path d="M6 13c.3-1 .4-2 .4-3a5.6 5.6 0 0 1 9-4.4" />
                            <path d="M17.6 8.5c.3 1 .4 2 .4 3.5 0 1.4-.1 2.8-.4 4" />
                            <path d="M4.5 9.5A8 8 0 0 1 16 4" />
                        </svg>
                    </button>
                    <button v-else type="button" class="pin-key"
                        style="background:transparent;border-color:transparent;"></button>
                    <button type="button" class="pin-key" @click="pinKey('0')">0</button>
                    <button type="button" class="pin-key pin-key-back" @click="pinBack">⌫</button>
                </div>
                <div class="lock-actions">
                    <button v-if="biometricAvailable && bioEnabled" type="button" class="sec-back sec-back-icon"
                        @click="tryBiometric">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 10a2 2 0 0 0-2 2c0 1.5.2 3-.5 4.5" />
                            <path d="M8 11a4 4 0 0 1 6.9-2.8" />
                            <path d="M14.5 12c0 2.5-.3 4.4-1 6" />
                            <path d="M6 13c.3-1 .4-2 .4-3a5.6 5.6 0 0 1 9-4.4" />
                            <path d="M17.6 8.5c.3 1 .4 2 .4 3.5 0 1.4-.1 2.8-.4 4" />
                            <path d="M4.5 9.5A8 8 0 0 1 16 4" />
                        </svg>
                        <span>Buka dengan Sidik Jari</span>
                    </button>
                    <button type="button" class="sec-back" @click="softLogout">Ganti Akun / Keluar</button>
                </div>
            </div>

            <!-- ============ FULLSCREEN BUAT PIN ============ -->
            <div v-if="view === 'setup-pin'" class="lock-screen">
                <div class="lock-logo-wrap">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="lock-title">Buat PIN Keamanan</div>
                <div class="lock-sub">
                    {{ pinSetup.stage === 'confirm' ? 'Ulangi PIN Baru (6 digit)' : 'Masukkan PIN Baru (6 digit)' }}
                </div>
                <div class="pin-dots">
                    <span v-for="i in 6" :key="i" class="pin-dot"
                        :class="{ filled: i <= (pinSetup.stage === 'confirm' ? pinSetup.confirm.length : pinSetup.first.length) }"></span>
                </div>
                <div class="pin-error">{{ pinSetup.error }}</div>
                <div class="pin-pad">
                    <button v-for="n in [1,2,3,4,5,6,7,8,9]" :key="n" type="button" class="pin-key"
                        @click="pinSetupKey(String(n))">{{ n }}</button>
                    <button type="button" class="pin-key"
                        style="background:transparent;border-color:transparent;"></button>
                    <button type="button" class="pin-key" @click="pinSetupKey('0')">0</button>
                    <button type="button" class="pin-key pin-key-back" @click="pinSetupBack">⌫</button>
                </div>
                <div class="lock-actions">
                    <button type="button" class="sec-back" @click="softLogout">Keluar</button>
                </div>
            </div>

            <!-- ============ FULLSCREEN KEAMANAN ============ -->
            <div v-if="view === 'security'" class="lock-screen">
                <div class="lock-logo-wrap">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="lock-title">Keamanan</div>
                <div class="lock-sub">Atur PIN &amp; biometrik</div>
                <div class="sec-list">
                    <button type="button" class="sec-item" @click="openChangePin">
                        <span class="sec-ico">🔢</span>
                        <span class="sec-label">Ganti PIN</span>
                        <span class="sec-chev">›</span>
                    </button>
                    <button type="button" class="sec-item" @click="openChangePass">
                        <span class="sec-ico">🔑</span>
                        <span class="sec-label">Ganti Password</span>
                        <span class="sec-chev">›</span>
                    </button>
                    <button v-if="isNativeApp" type="button" class="sec-item" @click="openBatterySettings">
                        <span class="sec-ico">🔋</span>
                        <span class="sec-label">Optimasi Baterai</span>
                        <span class="sec-chev">›</span>
                    </button>
                    <div v-if="biometricAvailable" class="sec-item sec-item-static">
                        <span class="sec-ico">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 10a2 2 0 0 0-2 2c0 1.5.2 3-.5 4.5" />
                                <path d="M8 11a4 4 0 0 1 6.9-2.8" />
                                <path d="M14.5 12c0 2.5-.3 4.4-1 6" />
                                <path d="M6 13c.3-1 .4-2 .4-3a5.6 5.6 0 0 1 9-4.4" />
                                <path d="M17.6 8.5c.3 1 .4 2 .4 3.5 0 1.4-.1 2.8-.4 4" />
                                <path d="M4.5 9.5A8 8 0 0 1 16 4" />
                            </svg>
                        </span>
                        <span class="sec-label">Buka dengan Sidik Jari</span>
                        <button type="button" class="bio-switch" :class="{ on: bioEnabled }" @click="toggleBio">
                            <span class="bio-knob"></span>
                        </button>
                    </div>
                    <button type="button" class="sec-back" @click="view = 'home'">
                        ‹ Kembali
                    </button>
                </div>
            </div>

            <!-- ============ FULLSCREEN GANTI PIN ============ -->
            <div v-if="view === 'change-pin'" class="lock-screen">
                <div class="lock-logo-wrap">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="lock-title">Ganti PIN</div>
                <div class="lock-sub">
                    {{ pinChange.stage === 'old' ? 'PIN Lama (6 digit)' : (pinChange.stage === 'new' ? 'PIN Baru (6 digit)' : 'Ulangi PIN Baru') }}
                </div>
                <div class="pin-dots">
                    <span v-for="i in 6" :key="i" class="pin-dot"
                        :class="{ filled: i <= pinChangeCurrent().length }"></span>
                </div>
                <div class="pin-error">{{ pinChange.error }}</div>
                <div class="pin-pad">
                    <button v-for="n in [1,2,3,4,5,6,7,8,9]" :key="n" type="button" class="pin-key"
                        @click="pinChangeKey(String(n))">{{ n }}</button>
                    <button type="button" class="pin-key"
                        style="background:transparent;border-color:transparent;"></button>
                    <button type="button" class="pin-key" @click="pinChangeKey('0')">0</button>
                    <button type="button" class="pin-key pin-key-back" @click="pinChangeBack">⌫</button>
                </div>
                <div class="lock-actions">
                    <button type="button" class="sec-back" @click="view = 'security'">‹ Kembali</button>
                </div>
            </div>

            <!-- ============ FULLSCREEN GANTI PASSWORD ============ -->
            <div v-if="view === 'change-pass'" class="lock-screen">
                <div class="lock-logo-wrap">
                    <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                </div>
                <div class="lock-title">Ganti Password</div>
                <div class="lock-sub">Password minimal 6 karakter</div>
                <div class="pass-form">
                    <div class="pass-field">
                        <label class="pass-label">Password Saat Ini</label>
                        <div class="pass-input-wrap">
                            <input class="pass-input pass-input-text" :type="showPassCurrent ? 'text' : 'password'"
                                v-model.trim="passModal.current" placeholder="Password lama"
                                autocomplete="current-password">
                            <button type="button" class="pass-eye" @click="showPassCurrent = !showPassCurrent"
                                :aria-label="showPassCurrent ? 'Sembunyikan' : 'Tampilkan'">
                                <span v-if="showPassCurrent">🙈</span><span v-else>👁️</span>
                            </button>
                        </div>
                    </div>
                    <div class="pass-field">
                        <label class="pass-label">Password Baru</label>
                        <div class="pass-input-wrap">
                            <input class="pass-input pass-input-text" :type="showPassNext ? 'text' : 'password'"
                                v-model.trim="passModal.next" placeholder="Minimal 6 karakter"
                                autocomplete="new-password">
                            <button type="button" class="pass-eye" @click="showPassNext = !showPassNext"
                                :aria-label="showPassNext ? 'Sembunyikan' : 'Tampilkan'">
                                <span v-if="showPassNext">🙈</span><span v-else>👁️</span>
                            </button>
                        </div>
                    </div>
                    <div class="pass-field">
                        <label class="pass-label">Ulangi Password Baru</label>
                        <div class="pass-input-wrap">
                            <input class="pass-input pass-input-text" :type="showPassConfirm ? 'text' : 'password'"
                                v-model.trim="passModal.confirm" placeholder="Ketik ulang" autocomplete="new-password">
                            <button type="button" class="pass-eye" @click="showPassConfirm = !showPassConfirm"
                                :aria-label="showPassConfirm ? 'Sembunyikan' : 'Tampilkan'">
                                <span v-if="showPassConfirm">🙈</span><span v-else>👁️</span>
                            </button>
                        </div>
                    </div>
                    <div v-if="passModal.error" class="pin-error">{{ passModal.error }}</div>
                    <button class="btn-login" style="margin-top:8px;" :disabled="busy" @click="submitPasswordChange">
                        <span v-if="busy">⏳ Menyimpan…</span>
                        <span v-else>Simpan Password</span>
                    </button>
                </div>
                <div class="lock-actions" style="margin-top:18px;">
                    <button type="button" class="sec-back" @click="view = 'security'">‹ Kembali</button>
                </div>
            </div>

            <!-- ================================================
                     LOGIN SCREEN
                ================================================ -->
            <div v-if="view === 'login'" class="login-screen">
                <div class="login-top">
                    <div class="login-logo-wrap">
                        <img src="/assets/images/iml-logo.png" alt="Indo Motor Lestari">
                    </div>
                    <h1>FSM Teknisi</h1>
                    <p class="login-tagline">Sistem Manajemen Field Service<br>Indo Motor Lestari</p>
                </div>

                <div class="login-card">
                    <label>Email</label>
                    <div class="input-wrap">
                        <span class="input-icon">📧</span>
                        <input type="email" v-model.trim="loginForm.email" placeholder="nama@indomotorlestari.co.id"
                            autocomplete="email" inputmode="email">
                    </div>
                    <label>Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input :type="showLoginPass ? 'text' : 'password'" v-model="loginForm.password"
                            placeholder="••••••••" autocomplete="current-password" @keyup.enter="doLogin"
                            class="input-has-eye">
                        <button type="button" class="login-eye" @click="showLoginPass = !showLoginPass"
                            :aria-label="showLoginPass ? 'Sembunyikan' : 'Tampilkan'">
                            <span v-if="showLoginPass">🙈</span><span v-else>👁️</span>
                        </button>
                    </div>
                    <div class="login-actions-row">
                        <button class="btn-login" :disabled="busy" @click="doLogin" id="btn-login">
                            <span v-if="busy">⏳ Memeriksa akun…</span>
                            <span v-else>Masuk ke Akun</span>
                        </button>
                        <button v-if="pinEnabled && token" type="button" class="btn-pin-quick" :disabled="busy"
                            :title="biometricAvailable ? 'Buka dengan sidik jari' : 'Masuk dengan PIN'"
                            @click="quickUnlock">
                            <svg v-if="biometricAvailable && bioEnabled" viewBox="0 0 24 24" width="24" height="24"
                                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 10a2 2 0 0 0-2 2c0 1.5.2 3-.5 4.5" />
                                <path d="M8 11a4 4 0 0 1 6.9-2.8" />
                                <path d="M14.5 12c0 2.5-.3 4.4-1 6" />
                                <path d="M6 13c.3-1 .4-2 .4-3a5.6 5.6 0 0 1 9-4.4" />
                                <path d="M17.6 8.5c.3 1 .4 2 .4 3.5 0 1.4-.1 2.8-.4 4" />
                                <path d="M4.5 9.5A8 8 0 0 1 16 4" />
                            </svg>
                            <span v-else>🔐</span>
                        </button>
                    </div>
                    <div v-if="loginError" class="login-error">
                        <span>⚠️</span><span>{{ loginError }}</span>
                    </div>
                </div>
                <div style="text-align:center; margin-top:20px; font-size:12px; color:var(--on-dark-2);">
                    Indo Motor Lestari © 2026 · FSM Mobile v2
                </div>
            </div>

            <!-- ================================================
                     MAIN APP CONTENT
                ================================================ -->
            <template v-else>
                <div class="app-main-content">

                    <!-- ========== HOME VIEW ========== -->
                    <div v-if="view === 'home'">
                        <div class="ptr-indicator" :class="{ active: pullDistance > 0, refreshing: refreshing }">
                            <span>{{ refreshing ? 'Memuat…' : (pullDistance >= 70 ? 'Lepaskan untuk muat ulang' : 'Tarik untuk muat ulang') }}</span>
                        </div>

                        <!-- ============ INSTALL MODAL (FLOATING TOP) ============ -->
                        <div v-if="installBannerVisible" class="install-sheet">
                            <div class="is-icon">📲</div>
                            <div class="is-body">
                                <div class="is-title">Pasang FSM Teknisi</div>
                                <div class="is-desc" v-if="isIosBrowser">
                                    Gunakan menu ⋮ / Bagikan browser → Tambahkan ke Layar Utama
                                </div>
                                <div class="is-desc" v-else-if="installVisible">
                                    Akses lebih cepat & tampilan aplikasi penuh.
                                </div>
                                <div class="is-desc" v-else-if="appDownloadUrl">
                                    Tersedia versi aplikasi (APK) untuk diunduh.
                                </div>
                                <div class="is-desc" v-else>
                                    Klik ⋮ pada browser → Tambahkan ke layar utama.
                                </div>
                                <div class="is-actions">
                                    <button v-if="!isIosBrowser && !installVisible && appDownloadUrl" type="button"
                                        class="is-btn" @click="downloadApp">Download</button>
                                    <button v-else-if="!isIosBrowser && installVisible" type="button" class="is-btn"
                                        @click="installApp">Pasang Sekarang</button>
                                    <button type="button" class="is-later" @click="dismissInstall">Nanti</button>
                                </div>
                            </div>
                        </div>

                        <div class="app-header">
                            <div class="app-header-inner">
                                <div class="logo-chip">
                                    <img src="/assets/images/iml-logo.png" alt="IML">
                                </div>
                                <div class="header-title">
                                    <strong>FSM Teknisi</strong>
                                    <span>{{ todayLabel }}</span>
                                </div>
                                <button class="icon-btn" @click="refresh" title="Muat ulang">⟳</button>
                            </div>
                        </div>

                        <div class="greet-band">
                            <h2>Halo, {{ firstName }}! 👋</h2>
                            <p>{{ greetingLine }}</p>
                        </div>

                        <!-- SEGMENTED TABS SWITCHER -->
                        <div class="tab-switcher-wrapper">
                            <div class="tab-switcher">
                                <button class="tab-btn" :class="{ active: activeTab === 'processing' }"
                                    @click="activeTab = 'processing'">
                                    <span>🚀 Sedang Diproses</span>
                                    <span class="tab-count">{{ activeOrders . length }}</span>
                                </button>
                                <button class="tab-btn" :class="{ active: activeTab === 'history' }"
                                    @click="activeTab = 'history'">
                                    <span>🗂️ Riwayat</span>
                                    <span class="tab-count">{{ historyOrders . length }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- HORIZONTAL SCROLL CHIP FILTERS (SCROLL-X) -->
                        <div class="scroll-x-bar">
                            <button class="filter-chip" :class="{ active: subFilter === 'all' }"
                                @click="subFilter = 'all'">
                                Semua ({{ activeOrders.length }})
                            </button>
                            <template v-if="activeTab === 'processing'">
                                <button class="filter-chip" :class="{ active: subFilter === 'waiting_acceptance' }"
                                    @click="subFilter = 'waiting_acceptance'">
                                    Konfirmasi ({{ pendingOrders . length }})
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'on_the_way' }"
                                    @click="subFilter = 'on_the_way'">
                                    Perjalanan ({{ onTheWayOrders.length }})
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'arrived' }"
                                    @click="subFilter = 'arrived'">
                                    Sudah Tiba ({{ arrivedOrders.length }})
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'installation' }"
                                    @click="subFilter = 'installation'">
                                    Pemasangan ({{ installationOrders.length }})
                                </button>
                            </template>
                            <template v-else>
                                <button class="filter-chip" :class="{ active: subFilter === 'finished' }"
                                    @click="subFilter = 'finished'">
                                    Selesai
                                </button>
                                <button class="filter-chip" :class="{ active: subFilter === 'rejected' }"
                                    @click="subFilter = 'rejected'">
                                    Ditolak
                                </button>
                            </template>
                        </div>

                        <div v-if="loading" class="loading">
                            <div class="spinner"></div>
                        </div>

                        <template v-else>
                            <!-- TAB: SEDANG DIPROSES -->
                            <div v-if="activeTab === 'processing'" class="section">
                                <div v-if="filteredProcessingOrders.length === 0" class="empty">
                                    <span class="big">🎉</span>
                                    Tidak ada tugas dalam kategori ini.
                                </div>
                                <button v-for="wo in filteredProcessingOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge"
                                            :class="statusBadge(wo.status)">{{ statusLabel(wo . status) }}</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">
                                        <span>📍 {{ wo . service_location ? wo . service_location . address : '-' }}</span>
                                    </div>
                                </button>
                            </div>

                            <!-- TAB: RIWAYAT -->
                            <div v-if="activeTab === 'history'" class="section">
                                <div class="search-bar">
                                    <span class="search-ico">🔍</span>
                                    <input type="text" v-model.trim="searchQuery" placeholder="Cari SPK / customer..."
                                        autocomplete="off">
                                    <button v-if="searchQuery" type="button" class="search-clear"
                                        @click="searchQuery = ''">✕</button>
                                </div>
                                <div class="scroll-x-bar" style="margin-top:-4px;">
                                    <button class="filter-chip" :class="{ active: historyRange === '7' }"
                                        @click="historyRange = '7'">7 Hari</button>
                                    <button class="filter-chip" :class="{ active: historyRange === '30' }"
                                        @click="historyRange = '30'">30 Hari</button>
                                    <button class="filter-chip" :class="{ active: historyRange === 'all' }"
                                        @click="historyRange = 'all'">Semua</button>
                                </div>
                                <div v-if="filteredHistoryOrders.length === 0" class="empty">
                                    <span class="big">📂</span>
                                    Belum ada riwayat pekerjaan.
                                </div>
                                <button v-for="wo in filteredHistoryOrders" :key="wo.id" class="wo-card"
                                    @click="openDetail(wo)" style="opacity:.88;">
                                    <div class="row1">
                                        <span class="number">{{ wo . number }}</span>
                                        <span class="badge"
                                            :class="statusBadge(historyStatus(wo))">{{ historyStatusLabel(wo) }}</span>
                                    </div>
                                    <div class="cust">{{ wo . customer ? wo . customer . name : 'Customer' }}</div>
                                    <div class="sub">📅 {{ fmtDate(wo . scheduled_start_at) }}</div>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- ========== DETAIL VIEW ========== -->
                    <div v-else-if="view === 'detail' && current">
                        <div class="detail-head">
                            <div class="detail-head-inner">
                                <button class="back-btn" @click="goHome">←</button>
                                <div>
                                    <div class="d-num">{{ current . number }}</div>
                                    <div class="d-sub">{{ current . work_type || 'Pemasangan' }}</div>
                                </div>
                                <div style="flex:1"></div>
                                <span class="badge"
                                    :class="statusBadge(current.status)">{{ statusLabel(current . status) }}</span>
                            </div>
                        </div>

                        <div class="detail-body">
                            <div class="status-banner" :style="bannerStyle">
                                <div class="s-label">{{ statusLabel(current . status) }}</div>
                                <div class="s-hint">{{ statusHint }}</div>
                            </div>

                            <div class="card">
                                <h4>📄 Informasi Pekerjaan</h4>
                                <div class="kv"><span class="k">Customer</span><span
                                        class="v">{{ current . customer ? current . customer . name : '-' }}</span></div>
                                <div class="kv" v-if="current.customer && current.customer.phone">
                                    <span class="k">Telepon</span>
                                    <span class="v"><a class="phone-link" :href="'tel:' + current.customer.phone">📞
                                            {{ current . customer . phone }}</a></span>
                                </div>
                                <div class="kv"><span class="k">Jadwal</span><span
                                        class="v">{{ fmtDateTime(current . scheduled_start_at) }}</span></div>
                            </div>

                            <div class="card" v-if="current.items && current.items.length">
                                <h4>📦 Item Pekerjaan</h4>
                                <div v-for="it in current.items" :key="it.id" class="wo-item">
                                    <div>
                                        <div class="wo-item-name">{{ it.product_name || '-' }}</div>
                                    </div>
                                    <span class="wo-item-qty">× {{ it.quantity }}</span>
                                </div>
                            </div>

                            <div class="card">
                                <h4>📍 Lokasi Pemasangan</h4>
                                <div style="font-size:14px;font-weight:600;margin-bottom:10px;">
                                    {{ current . service_location ? current . service_location . address : '-' }}
                                </div>
                                <div v-if="current.service_location && current.service_location.latitude">
                                    <div id="detail-map"></div>
                                    <div v-if="gpsState === 'active'" class="gps-pill">
                                        <span class="live-dot"></span> GPS aktif · posisi terkirim {{ gpsSentLabel ||
                                            'sebentar lagi' }}
                                    </div>
                                    <div v-else-if="gpsState === 'starting'" class="gps-pill">Menyiapkan GPS…</div>
                                    <div v-else-if="gpsState === 'error'" class="gps-pill"
                                        style="background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.35);color:var(--red-500);">
                                        GPS terkendala — periksa izin lokasi browser
                                    </div>
                                    <a class="btn-maps-action" :href="getNavigationUrl(current.service_location)"
                                        target="_blank" rel="noopener">
                                        🗺️ Buka Navigasi (Google Maps)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="sticky-actions-bar" v-if="actionList.length">
                            <button v-for="act in actionList" :key="act.key" class="action-btn"
                                :class="act.cls" :disabled="busy" @click="runAction(act)">
                                {{ act . label }}
                            </button>
                        </div>
                    </div>

                    <!-- ========== BOTTOM NAVIGATION BAR ========== -->
                    <nav class="bottom-nav" v-if="view === 'home'">
                        <button class="nav-item active">
                            <span class="nav-icon">🏠</span>
                            <span>Beranda</span>
                        </button>
                        <button class="nav-item" @click="view = 'security'">
                            <span class="nav-icon">🔐</span>
                            <span>Keamanan</span>
                        </button>
                        <button class="nav-item" @click="lockApp">
                            <span class="nav-icon">🚪</span>
                            <span>Kunci Layar</span>
                        </button>
                    </nav>
                </div>
            </template>

            <!-- TOAST -->
            <div v-if="toast.show" class="toast" :class="toast.type">{{ toast . message }}</div>

            <!-- ========== MODAL ALASAN (TOLAK / KENDALA) ========== -->
            <div v-if="modal.show" class="modal-backdrop" @click.self="modal.show = false">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <div class="modal-head">
                        <div class="modal-title-box">
                            <div class="modal-icon-badge">⚠️</div>
                            <div>
                                <h3>{{ modal . title }}</h3>
                                <p class="desc">{{ modal . desc }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="chip-row">
                        <button v-for="c in modal.chips" :key="c" type="button" class="chip"
                            :class="{ selected: modal.reason === c }" @click="modal.reason = c">{{ c }}</button>
                    </div>
                    <div class="reason-box">
                        <textarea v-model.trim="modal.reason" rows="3"
                            placeholder="Tulis alasan / kendala kamu di sini…"></textarea>
                    </div>
                    <div v-if="modal.error"
                        style="background:var(--red-100); color:var(--red-700); padding:10px 12px; border-radius:var(--r-sm); font-size:12.5px; margin-bottom:12px; border:1px solid #fecdd3;">
                        ⚠️ {{ modal . error }}
                    </div>
                    <div class="modal-actions">
                        <button class="cancel" type="button" @click="modal.show = false">Batal</button>
                        <button class="ok-red" type="button" :disabled="busy || !modal.reason" @click="submitReason">
                            {{ busy ? 'Mengirim…' : 'Kirim' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========== MODAL SELESAIKAN PEMASANGAN (FOTO) ========== -->
            <div v-if="finishSheet.show" class="modal-backdrop" @click.self="closeFinishSheet">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <div class="modal-head">
                        <div class="modal-title-box">
                            <div class="modal-icon-badge">📸</div>
                            <div>
                                <h3>Selesaikan Pemasangan</h3>
                                <p class="desc">Lampirkan foto hasil pemasangan (min. 1, maks. {{ maxPhotos }}) sebagai bukti.</p>
                            </div>
                        </div>
                    </div>

                    <div class="photo-grid">
                        <div v-for="(p, i) in finishSheet.photos" :key="i" class="photo-thumb">
                            <img :src="p.preview" alt="foto">
                            <button type="button" class="photo-del" @click="removeFinishPhoto(i)" title="Hapus">✕</button>
                        </div>
                        <button v-if="finishSheet.photos.length < maxPhotos" type="button" class="photo-add"
                            @click="addFinishPhoto('camera')">
                            <span>📷</span><span>Kamera</span>
                        </button>
                        <button v-if="finishSheet.photos.length < maxPhotos" type="button" class="photo-add"
                            @click="addFinishPhoto('gallery')">
                            <span>🖼️</span><span>Galeri</span>
                        </button>
                    </div>
                    <p style="font-size:12px;color:var(--ink-2);margin:0 0 12px;">
                        {{ finishSheet.photos.length }} / {{ maxPhotos }} foto dipilih
                    </p>

                    <div class="reason-box">
                        <textarea v-model="finishSheet.note" rows="2"
                            placeholder="Catatan hasil pemasangan (opsional)…"></textarea>
                    </div>

                    <div v-if="finishSheet.error"
                        style="background:var(--red-100); color:var(--red-700); padding:10px 12px; border-radius:var(--r-sm); font-size:12.5px; margin-bottom:12px; border:1px solid #fecdd3;">
                        ⚠️ {{ finishSheet.error }}
                    </div>
                    <div class="modal-actions">
                        <button class="cancel" type="button" :disabled="finishSheet.uploading" @click="closeFinishSheet">Batal</button>
                        <button class="ok-green" type="button"
                            :disabled="finishSheet.uploading || finishSheet.photos.length < 1" @click="submitFinish">
                            {{ finishSheet.uploading ? 'Mengunggah…' : '🎉 Selesaikan' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========== MODAL KONFIRMASI (CUSTOM) ========== -->
            <div v-if="confirmBox.show" class="modal-backdrop">
                <div class="modal">
                    <div class="modal-grip"></div>
                    <div class="modal-head">
                        <div class="modal-title-box">
                            <div class="modal-icon-badge">❓</div>
                            <div>
                                <h3>{{ confirmBox.title }}</h3>
                            </div>
                        </div>
                    </div>
                    <p style="font-size:14px;color:var(--ink-2);margin:0 0 6px;">{{ confirmBox.message }}</p>
                    <div class="modal-actions">
                        <button class="cancel" type="button" @click="confirmBox.show = false">Batal</button>
                        <button class="ok-red" type="button" @click="confirmOk">Ya</button>
                    </div>
                </div>
            </div>

    </div>
</template>

<script>
import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { BiometricAuth } from '@aparajita/capacitor-biometric-auth';
import { BatteryOptimization } from '@capawesome-team/capacitor-android-battery-optimization';
import { KeepAwake } from '@capacitor-community/keep-awake';
import { CapacitorUpdater } from '@capgo/capacitor-updater';
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { FileOpener } from '@capacitor-community/file-opener';
import { registerPlugin } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import { API_V1 } from './composables/api';

// Plugin background geolocation (jalan walau app di-minimize, via foreground service).
const BackgroundGeolocation = registerPlugin('BackgroundGeolocation');

// Perbaiki ikon marker default Leaflet saat dibundel Vite (URL default-nya tidak resolve).
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Sediakan Leaflet secara global agar kode peta yang memakai `L.` tetap berfungsi.
window.L = L;

            const STATUS_META = {
                draft: {
                    label: 'Draf',
                    color: '#64748b'
                },
                waiting_acceptance: {
                    label: 'Menunggu Konfirmasi',
                    color: '#d97706'
                },
                accepted: {
                    label: 'Diterima',
                    color: '#0284c7'
                },
                on_the_way: {
                    label: 'Dalam Perjalanan',
                    color: '#7c3aed'
                },
                arrived: {
                    label: 'Sudah Tiba',
                    color: '#0369a1'
                },
                installation: {
                    label: 'Sedang Pemasangan',
                    color: '#4f46e5'
                },
                finished: {
                    label: 'Selesai',
                    color: '#059669'
                },
                rejected: {
                    label: 'Ditolak',
                    color: '#e11d48'
                },
                superseded: {
                    label: 'Diambil Teknisi Lain',
                    color: '#64748b'
                },
                cancelled: {
                    label: 'Dibatalkan',
                    color: '#64748b'
                },
                failed: {
                    label: 'Gagal',
                    color: '#991b1b'
                },
            };
            const BADGE_CLASS = {
                waiting_acceptance: 'b-amber',
                accepted: 'b-sky',
                on_the_way: 'b-violet',
                arrived: 'b-blue',
                installation: 'b-indigo',
                finished: 'b-green',
                rejected: 'b-rose',
                superseded: 'b-gray',
                cancelled: 'b-gray',
                failed: 'b-red',
                draft: 'b-gray',
            };
            const HINTS = {
                waiting_acceptance: 'Konfirmasi dulu ya — apakah kamu bisa mengerjakan tugas ini?',
                accepted: 'Pekerjaan sudah kamu terima. Siapkan perjalananmu! 🗺️',
                on_the_way: 'GPS aktif — pelanggan bisa melihat posisimu. Hati-hati di jalan! 🛵',
                arrived: 'Kamu sudah di lokasi. Kabari pelanggan bahwa kamu sudah tiba.',
                installation: 'Fokus kerjakan yang terbaik. Pelanggan menantikan hasilnya! 🔧',
                finished: 'Kerja bagus! Pekerjaan selesai dengan sempurna. 👏',
            };

            function fsmCurrentEmail() {
                try {
                    const u = JSON.parse(localStorage.getItem('fsm_tech_user') || 'null');
                    if (u && u.email) return String(u.email).trim().toLowerCase();
                } catch (_) {}
                return '';
            }

            function fsmLocalPinKey(email) {
                const e = String(email || '').trim().toLowerCase();
                return 'fsm_pin' + (e ? '_' + e : '');
            }

            // Bersihkan kunci PIN lama (salt/hash) yang tidak terpakai lagi.
            (function cleanupOldPinKeys() {
                try {
                    Object.keys(localStorage).forEach(function (k) {
                        if (k === 'fsm_pin_salt' || k === 'fsm_pin_hash' ||
                            k.startsWith('fsm_pin_salt_') || k.startsWith('fsm_pin_hash_')) {
                            localStorage.removeItem(k);
                        }
                    });
                } catch (_) {}
            })();

            // Tangkap event install sedini mungkin (sebelum Vue mount), biar tidak terlewat.
            let fsmInstallEvent = null;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                fsmInstallEvent = e;
            });

            // Bandingkan versi "1.0" vs "1.0.0" dengan benar (angka per segmen).
            function isVersionNewer(server, native) {
                const parse = (v) => String(v || '0').split('.').map((n) => parseInt(n, 10) || 0);
                const a = parse(server);
                const b = parse(native);
                for (let i = 0; i < 3; i++) {
                    const x = a[i] || 0;
                    const y = b[i] || 0;
                    if (x > y) return true;
                    if (x < y) return false;
                }
                return false;
            }


export default {
                data() {
                    return {
                        token: localStorage.getItem('fsm_tech_token') || '',
                        user: JSON.parse(localStorage.getItem('fsm_tech_user') || 'null'),
                        view: (localStorage.getItem('fsm_tech_token') && localStorage.getItem(fsmLocalPinKey(fsmCurrentEmail())))
                            ? 'lock'
                            : (localStorage.getItem('fsm_tech_token') ? 'home' : 'login'),
                        activeTab: 'processing',
                        subFilter: 'all',
                        searchQuery: '',
                        historyRange: '7',
                        loginForm: {
                            email: '',
                            password: ''
                        },
                        loginError: '',
                        busy: false,
                        loading: true,
                        orders: [],
                        current: null,
                        toast: {
                            show: false,
                            message: '',
                            type: 'info'
                        },
                        updateInfo: null,
                        updateDownloading: false,
                        updateProgress: 0,
                        confirmBox: {
                            show: false,
                            title: '',
                            message: '',
                            onOk: null
                        },
                        pinEntry: '',
                        pinError: '',
                        pendingRelogin: false,
                        pinSetup: {
                            stage: 'first',
                            first: '',
                            confirm: '',
                            error: ''
                        },
                        biometricAvailable: false,
                        online: navigator.onLine,
                        installEvent: null,
                        installVisible: false,
                        iosInstallHint: false,
                        pullDistance: 0,
                        refreshing: false,
                        pullStartY: null,
                        locked: localStorage.getItem('fsm_locked') === '1',
                        installTimer: null,
                        installPromptInit: false,
                        isAndroidBrowser: false,
                        isIosBrowser: false,
                        bioEnabledState: false,
                        manualHint: false,
                        appDownloadUrl: '',
                        pinChange: {
                            stage: 'old',
                            old: '',
                            new: '',
                            confirm: '',
                            error: ''
                        },

                        // Password Modal Data & Eye Toggles
                        passModal: {
                            show: false,
                            current: '',
                            next: '',
                            confirm: '',
                            error: ''
                        },
                        showPassCurrent: false,
                        showPassNext: false,
                        showPassConfirm: false,
                        showLoginPass: false,
                        toastTimer: null,
                        pendingFcmToken: '',
                        modal: {
                            show: false,
                            mode: 'reject',
                            reason: '',
                            title: '',
                            desc: '',
                            chips: [],
                            error: ''
                        },
                        finishSheet: {
                            show: false,
                            photos: [],
                            note: '',
                            error: '',
                            uploading: false,
                        },
                        maxPhotos: 5,
                        gpsState: 'off',
                        gpsSentLabel: '',
                        lastGpsSentAt: 0,
                        wakeLockSentinel: null,
                        watchId: null,
                        bgWatcherId: null,
                        gpsTimer: null,
                        lastPos: null,
                        tripSessionId: null,
                        pollTimer: null,
                        mapInstance: null,
                        mapMarker: null,
                        mapPosMarker: null,
                    };
                },
                computed: {
                    todayLabel() {
                        return new Date().toLocaleDateString('id-ID', {
                            weekday: 'short',
                            day: 'numeric',
                            month: 'short'
                        });
                    },
                    pinEnabled() {
                        return !!(this.user && this.user.has_pin) || !!this.localPin();
                    },
                    bioEnabled() {
                        return this.bioEnabledState;
                    },
                    isNativeApp() {
                        return !!(window.Capacitor && window.Capacitor.isNativePlatform
                            && window.Capacitor.isNativePlatform());
                    },
                    installBannerVisible() {
                        return this.view === 'home'
                            && !this.isNativeApp
                            && !this.updateInfo
                            && (this.installVisible || this.iosInstallHint || this.manualHint);
                    },
                    firstName() {
                        return this.user ? (this.user.name || 'Teknisi').split(' ')[0] : 'Teknisi';
                    },
                    greetingLine() {
                        const h = new Date().getHours();
                        if (h < 11) return 'Pagi yang cerah, waktunya berkarya! ☀️';
                        if (h < 15) return 'Siang hari, tetap semangat kerjanya! ⚡';
                        if (h < 18) return 'Sore hari, tinggal sedikit lagi! 🌤️';
                        return 'Malam hari, kerja kerasmu luar biasa! 🌙';
                    },
                    pendingOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            return a && a.status === 'pending' && wo.status === 'waiting_acceptance';
                        }.bind(this));
                    },
                    onTheWayOrders() {
                        return this.activeOrders.filter(function(wo) {
                            return wo.status === 'on_the_way';
                        }.bind(this));
                    },
                    arrivedOrders() {
                        return this.activeOrders.filter(function(wo) {
                            return wo.status === 'arrived';
                        }.bind(this));
                    },
                    installationOrders() {
                        return this.activeOrders.filter(function(wo) {
                            return wo.status === 'installation';
                        }.bind(this));
                    },
                    activeOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            if (!a) return false;
                            if (['rejected', 'superseded'].includes(a.status)) return false;
                            return ['waiting_acceptance', 'accepted', 'on_the_way', 'arrived', 'installation']
                                .includes(wo.status);
                        }.bind(this));
                    },
                    historyOrders() {
                        return this.orders.filter(function(wo) {
                            const a = this.myAssignment(wo);
                            if (!a) return false;
                            return ['rejected', 'superseded'].includes(a.status) || ['finished', 'cancelled',
                                'failed'
                            ].includes(wo.status);
                        }.bind(this));
                    },
                    filteredProcessingOrders() {
                        if (this.subFilter === 'all') return this.activeOrders;
                        return this.activeOrders.filter(function(wo) {
                            return wo.status === this.subFilter;
                        }.bind(this));
                    },
                    filteredHistoryOrders() {
                        const q = this.searchQuery.trim().toLowerCase();
                        const cutoff = this.historyRange === '7'
                            ? Date.now() - 7 * 86400000
                            : (this.historyRange === '30' ? Date.now() - 30 * 86400000 : null);
                        return this.historyOrders.filter(function(wo) {
                            if (this.subFilter !== 'all' && this.historyStatus(wo) !== this.subFilter) return false;
                            if (q && !this.matchSearch(wo, q)) return false;
                            if (cutoff !== null) {
                                const d = wo.scheduled_start_at ? new Date(wo.scheduled_start_at).getTime() : 0;
                                if (d < cutoff) return false;
                            }
                            return true;
                        }.bind(this));
                    },
                    bannerStyle() {
                        const meta = STATUS_META[this.current ? this.current.status : 'draft'] || STATUS_META.draft;
                        return {
                            background: 'linear-gradient(135deg, ' + meta.color + 'ee, ' + meta.color + '99)'
                        };
                    },
                    statusHint() {
                        return HINTS[this.current ? this.current.status : 'draft'] || '';
                    },
                    onTrip() {
                        return this.orders.some(function(wo) {
                            const a = this.myAssignment(wo);
                            return wo.status === 'on_the_way' && a && a.status === 'accepted';
                        }.bind(this));
                    },
                    actionList() {
                        if (!this.current) return [];
                        const s = this.current.status;
                        const a = this.myAssignment(this.current);
                        const mine = a && a.status === 'accepted';
                        const list = [];
                        if (s === 'waiting_acceptance' && a && a.status === 'pending') {
                            list.push({
                                key: 'accept',
                                label: '✅ Terima Pekerjaan',
                                cls: 'green'
                            });
                            list.push({
                                key: 'reject',
                                label: 'Tolak Pekerjaan',
                                cls: 'ghost'
                            });
                        } else if (s === 'accepted' && mine) {
                            list.push({
                                key: 'start-trip',
                                label: '🚗 Mulai Perjalanan',
                                cls: 'violet'
                            });
                            list.push({
                                key: 'fail',
                                label: 'Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'on_the_way' && mine) {
                            list.push({
                                key: 'arrive',
                                label: '📍 Saya Sudah Tiba',
                                cls: 'blue'
                            });
                            list.push({
                                key: 'fail',
                                label: 'Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'arrived' && mine) {
                            list.push({
                                key: 'start-installation',
                                label: '🔧 Mulai Pemasangan',
                                cls: 'amber'
                            });
                            list.push({
                                key: 'fail',
                                label: 'Laporkan Kendala',
                                cls: 'ghost'
                            });
                        } else if (s === 'installation' && mine) {
                            list.push({
                                key: 'finish',
                                label: '🎉 Selesaikan Pemasangan',
                                cls: 'green'
                            });
                            list.push({
                                key: 'fail',
                                label: 'Laporkan Kendala',
                                cls: 'ghost'
                            });
                        }
                        return list;
                    }
                },
                watch: {
                    activeTab() {
                        this.subFilter = 'all';
                    },
                    installBannerVisible(visible) {
                        clearTimeout(this.installTimer);
                        if (visible) {
                            this.installTimer = setTimeout(() => {
                                this.installVisible = false;
                                this.iosInstallHint = false;
                                this.manualHint = false;
                            }, 10000);
                        }
                    },
                    user() {
                        this.syncBioState();
                    }
                },
                mounted() {
                    this.checkAppVersion();
                    this.detectBiometric();
                    this.syncBioState();
                    this.setupBackButton();
                    this.setupConnectivity();
                    this.registerPushNotifications();
                    if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
                        CapacitorUpdater.notifyAppReady(); // beritahu Capgo bundle berhasil dimuat
                    }
                    this.setupPullToRefresh();
                    if (this.token) {
                        if (this.locked) {
                            this.view = 'lock';
                        } else {
                            this.view = 'home';
                            this.loadOrders();
                            this.pollTimer = setInterval(() => this.loadOrders(true), 45000);
                        }
                    } else {
                        this.view = 'login';
                    }
                    if (false && 'serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/mobile/sw.js').catch(() => {});
                    }
                    this.maybeShowInstall();
                },
                methods: {
                    getNavigationUrl(loc) {
                        if (!loc || !loc.latitude) return '#';
                        return 'https://www.google.com/maps/dir/?api=1&destination=' + loc.latitude + ',' + loc
                            .longitude;
                    },
                    myAssignment(wo) {
                        if (!wo || !wo.assignments || !this.user) return null;
                        const techId = this.user.technician_id;
                        return wo.assignments.find(function(a) {
                            return a.technician_id === techId;
                        }) || null;
                    },
                    statusLabel(status) {
                        return (STATUS_META[status] || STATUS_META.draft).label;
                    },
                    statusBadge(status) {
                        return BADGE_CLASS[status] || 'b-gray';
                    },
                    historyStatus(wo) {
                        const a = this.myAssignment(wo);
                        if (a && a.status === 'superseded') return 'superseded';
                        if (a && a.status === 'rejected') return 'rejected';
                        return wo.status;
                    },
                    historyStatusLabel(wo) {
                        return this.statusLabel(this.historyStatus(wo));
                    },
                    fmtDate(val) {
                        return val ? new Date(val).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                    },
                    fmtDateTime(val) {
                        if (!val) return '-';
                        const d = new Date(val);
                        return d.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short'
                        }) + ' ' + d.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },
                    async api(path, options = {}) {
                        const config = {
                            method: options.method || 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                        };
                        if (this.token) config.headers.Authorization = 'Bearer ' + this.token;
                        if (options.body) config.body = JSON.stringify(options.body);
                        const res = await fetch(API_V1 + path, config);
                        if (res.status === 401) {
                            if (this.pinEnabled) {
                                this.pendingRelogin = true;
                                this.locked = true;
                                localStorage.setItem('fsm_locked', '1');
                                this.view = 'lock';
                                this.pinEntry = '';
                                this.pinError = '';
                                this.showToast('Sesi berakhir — masukkan PIN.', 'info');
                            } else {
                                this.forceLogout();
                            }
                            throw new Error('Sesi berakhir.');
                        }
                        const data = await res.json().catch(function() {
                            return {};
                        });
                        if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan.');
                        return data;
                    },
                    async apiUpload(path, formData) {
                        const config = {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData,
                        };
                        if (this.token) config.headers.Authorization = 'Bearer ' + this.token;
                        const res = await fetch(API_V1 + path, config);
                        if (res.status === 401) {
                            if (this.pinEnabled) {
                                this.pendingRelogin = true;
                                this.locked = true;
                                localStorage.setItem('fsm_locked', '1');
                                this.view = 'lock';
                                this.pinEntry = '';
                                this.pinError = '';
                                this.showToast('Sesi berakhir — masukkan PIN.', 'info');
                            } else {
                                this.forceLogout();
                            }
                            throw new Error('Sesi berakhir.');
                        }
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(data.message || 'Terjadi kesalahan.');
                        return data;
                    },
                    showToast(msg, type = 'info') {
                        this.toast = {
                            show: true,
                            message: msg,
                            type: type
                        };
                        clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(function() {
                            this.toast.show = false;
                        }.bind(this), 3000);
                    },
                    async doLogin() {
                        if (!this.loginForm.email || !this.loginForm.password) return;
                        this.busy = true;
                        try {
                            const res = await fetch(API_V1 + '/auth/login', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    email: this.loginForm.email,
                                    password: this.loginForm.password,
                                    device_name: 'fsm-mobile-web'
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok) throw new Error(data.message || 'Login gagal.');
                            this.token = data.token;
                            this.user = data.user;
                            localStorage.setItem('fsm_tech_token', this.token);
                            localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                            this.sendFcmToken();
                            this.view = 'home';
                            this.loadOrders();
                            this.pollTimer = setInterval(() => this.loadOrders(true), 45000);
                            const hasServerPin = !!(this.user && this.user.has_pin);
                            const hasLocalPin = !!this.localPin();
                            if (hasServerPin || hasLocalPin) {
                                this.view = 'lock';
                                this.pinEntry = '';
                                this.pinError = '';
                            } else {
                                this.promptPinSetup();
                            }
                        } catch (err) {
                            this.loginError = err.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                    async loadOrders(silent = false) {
                        if (!this.token) return;
                        if (!silent) this.loading = true;
                        try {
                            const data = await this.api('/work-orders');
                            this.orders = data.data || [];
                            await this.syncTracking();
                        } catch (err) {
                            if (!silent) this.showToast(err.message, 'error');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async refresh() {
                        this.showToast('Memuat ulang…');
                        await this.loadOrders();
                        this.showToast('Data terbaru dimuat ✓', 'success');
                    },
                    async openDetail(wo) {
                        this.view = 'detail';
                        this.current = wo;
                        try {
                            const data = await this.api('/work-orders/' + wo.id);
                            this.current = data.data || data;
                            const session = this.current.tracking_sessions
                                ? this.current.tracking_sessions.find(s => s.status === 'active')
                                : null;
                            if (this.current.status === 'on_the_way' && session) this.tripSessionId = session.id;
                            this.$nextTick(() => this.initMap());
                            await this.syncTracking();
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        }
                    },
                    goHome() {
                        this.view = 'home';
                        this.current = null;
                    },
                    forceLogout() {
                        this.stopGps();
                        this.token = '';
                        this.user = null;
                        this.view = 'login';
                        this.current = null;
                        this.locked = false;
                        localStorage.removeItem('fsm_locked');
                        localStorage.removeItem('fsm_tech_token');
                        localStorage.removeItem('fsm_tech_user');
                        if (this.pollTimer) {
                            clearInterval(this.pollTimer);
                            this.pollTimer = null;
                        }
                        this.pinEntry = '';
                        this.pinError = '';
                    },
                    async doLogout() {
                        try {
                            await this.api('/auth/logout', {
                                method: 'DELETE'
                            });
                        } catch (err) {}
                        this.forceLogout();
                    },
                    async checkAppVersion() {
                        try {
                            if (!window.Capacitor) return; // hanya berjalan di dalam APK
                            const native = await window.Capacitor.Plugins.App.getInfo();
                            const res = await fetch(API_V1 + '/app/version');
                            const data = await res.json();

                            // 1) BUNDLE (live update / OTA) — tukar UI tanpa install ulang.
                            //    Dicoba dulu; kalau ada bundle baru, app akan reload sendiri.
                            await this.applyLiveUpdate(data);

                            // 2) NATIVE — perubahan yang WAJIB install ulang APK.
                            if (!data.version || !isVersionNewer(data.version, native.version)) return;
                            this.updateInfo = {
                                version: data.version,
                                url: data.download_url || '',
                                required: !!data.update_required,
                            };
                        } catch (err) { /* bukan APK atau jaringan bermasalah — abaikan */ }
                    },
                    async applyLiveUpdate(data) {
                        // Live update bundle (Capgo self-hosted). Hanya di dalam APK.
                        try {
                            if (!window.Capacitor || !window.Capacitor.isNativePlatform
                                || !window.Capacitor.isNativePlatform()) return;
                            if (!data || !data.bundle_url || !data.bundle_version) return;
                            const applied = parseInt(localStorage.getItem('fsm_bundle_version') || '0', 10);
                            if (data.bundle_version <= applied) return; // sudah bundle terbaru
                            // Download bundle .zip dari server sendiri lalu aktifkan (auto-reload).
                            const version = String(data.bundle_version);
                            this.showToast('Mengunduh pembaruan v' + version + '…', 'info');
                            const bundle = await CapacitorUpdater.download({
                                url: data.bundle_url,
                                version,
                            });
                            // Tandai SEBELUM set() karena set() langsung me-reload app.
                            localStorage.setItem('fsm_bundle_version', version);
                            await CapacitorUpdater.set(bundle);
                        } catch (err) {
                            // Gagal OTA — hapus penanda agar coba lagi saat app dibuka berikutnya.
                            try {
                                const ver = String(data?.bundle_version ?? '');
                                if (ver && localStorage.getItem('fsm_bundle_version') === ver) {
                                    localStorage.removeItem('fsm_bundle_version');
                                }
                            } catch (_) { /* abaikan */ }
                            this.showToast('Pembaruan gagal, akan dicoba lagi nanti.', 'error');
                        }
                    },
                    async downloadUpdate() {
                        const url = this.updateInfo && this.updateInfo.url;
                        if (!url) return;

                        // Di luar APK (browser biasa) — cukup buka link.
                        const isNative = window.Capacitor && window.Capacitor.isNativePlatform
                            && window.Capacitor.isNativePlatform();
                        if (!isNative) {
                            window.open(url, '_system');
                            return;
                        }
                        if (this.updateDownloading) return; // cegah dobel-tap

                        this.updateDownloading = true;
                        this.updateProgress = 0;
                        let progressHandle = null;
                        try {
                            // Pantau progress unduhan (0–100%).
                            progressHandle = await Filesystem.addListener('progress', (status) => {
                                if (status && status.contentLength > 0) {
                                    this.updateProgress = Math.min(100,
                                        Math.round((status.bytes / status.contentLength) * 100));
                                }
                            });

                            const fileName = 'fsm-teknisi-' + (this.updateInfo.version || 'update') + '.apk';
                            // Unduh APK ke penyimpanan cache aplikasi.
                            const result = await Filesystem.downloadFile({
                                url,
                                path: fileName,
                                directory: Directory.Cache,
                                progress: true,
                            });

                            this.updateProgress = 100;

                            // Buka file APK → memicu dialog installer Android (tap "Install").
                            await FileOpener.open({
                                filePath: result.path || result.uri,
                                contentType: 'application/vnd.android.package-archive',
                            });
                            this.showToast('Unduhan selesai. Lanjutkan pemasangan di layar Android.', 'success');
                        } catch (err) {
                            // Gagal unduh/buka — fallback ke browser sistem (cara lama, tetap aman).
                            this.showToast('Membuka unduhan di browser…', 'info');
                            window.open(url, '_system');
                        } finally {
                            if (progressHandle && progressHandle.remove) {
                                try { await progressHandle.remove(); } catch (e) { /* abaikan */ }
                            }
                            this.updateDownloading = false;
                        }
                    },
                    async registerPushNotifications() {
                        try {
                            if (!window.Capacitor || !window.Capacitor.isNativePlatform
                                || !window.Capacitor.isNativePlatform()) return;

                            // Minta izin push notification
                            let permission = await PushNotifications.checkPermissions();
                            if (permission.receive !== 'granted') {
                                permission = await PushNotifications.requestPermissions();
                            }
                            if (permission.receive !== 'granted') return; // user menolak

                            await PushNotifications.register();

                            // Listener: token FCM dari Firebase/Google Play Services
                            PushNotifications.addListener('registration', async (token) => {
                                // Simpan dulu — token bisa terbit sebelum user login.
                                this.pendingFcmToken = token.value || '';
                                await this.sendFcmToken();
                            });

                            // Listener: notifikasi masuk saat app dibuka
                            PushNotifications.addListener('pushNotificationReceived', (notification) => {
                                const n = notification.notification || {};
                                const msg = [n.title, n.body].filter(Boolean).join(' — ');
                                if (msg) this.showToast(msg, 'info');
                                if (this.view === 'home') this.loadOrders();
                            });

                            // Listener: user tap notifikasi (app tertutup -> dibuka)
                            PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
                                // Navigasi langsung ke WO yang relevan atau refresh list.
                                const data = notification.notification.data;
                                if (data && data.work_order_id) {
                                    // Contoh: buka WO dengan cari dulu di list (bukan openDetail langsung karena perlu object wo).
                                    this.loadOrders().then(() => {
                                        const wo = this.orders.find(o => o.id === parseInt(data.work_order_id, 10));
                                        if (wo) this.openDetail(wo);
                                    });
                                } else {
                                    this.view = 'home';
                                    this.loadOrders();
                                }
                            });
                        } catch (err) { /* plugin tidak tersedia atau izin ditolak */ }
                    },
                    async sendFcmToken() {
                        if (!this.pendingFcmToken || !this.token) return;
                        const token = this.pendingFcmToken;
                        try {
                            await this.api('/device-tokens', {
                                method: 'POST',
                                body: {
                                    token,
                                    platform: 'android',
                                    device_name: 'FSM Teknisi Mobile',
                                },
                            });
                            this.pendingFcmToken = '';
                        } catch (err) { /* kirim ulang di kesempatan berikutnya */ }
                    },
                    doLoginWithPin() {
                        if (!this.token) return;
                        this.pendingRelogin = true;
                        this.locked = true;
                        localStorage.setItem('fsm_locked', '1');
                        this.view = 'lock';
                        this.pinEntry = '';
                        this.pinError = '';
                    },
                    quickUnlock() {
                        if (this.biometricAvailable && this.bioEnabled) {
                            this.tryBiometric();
                        } else {
                            this.doLoginWithPin();
                        }
                    },
                    lockApp() {
                        this.stopGps();
                        this.current = null;
                        this.orders = [];
                        if (this.pollTimer) {
                            clearInterval(this.pollTimer);
                            this.pollTimer = null;
                        }
                        this.pendingRelogin = false;
                        this.locked = true;
                        localStorage.setItem('fsm_locked', '1');
                        this.view = 'lock';
                        this.pinEntry = '';
                        this.pinError = '';
                    },
                    askConfirm(title, message, onOk) {
                        this.confirmBox = { show: true, title, message, onOk };
                    },
                    confirmOk() {
                        const fn = this.confirmBox.onOk;
                        this.confirmBox.show = false;
                        if (typeof fn === 'function') fn();
                    },
                    exitApp() {
                        if (window.Capacitor && window.Capacitor.Plugins.App &&
                            window.Capacitor.Plugins.App.exitApp) {
                            window.Capacitor.Plugins.App.exitApp();
                        }
                        this.confirmBox.show = false;
                    },
                    setupBackButton() {
                        if (!window.Capacitor || !window.Capacitor.Plugins.App ||
                            !window.Capacitor.Plugins.App.addListener) return;
                        window.Capacitor.Plugins.App.addListener('backButton', () => {
                            if (this.view === 'home' || this.view === 'lock' || this.view === 'login') {
                                this.askConfirm('Keluar Aplikasi', 'Yakin mau keluar aplikasi?', () => this.exitApp());
                            } else if (this.view === 'detail') {
                                this.goHome();
                            } else if (this.view === 'change-pin' || this.view === 'change-pass') {
                                this.view = 'security';
                            } else if (this.view === 'security') {
                                this.view = 'home';
                            }
                        });
                    },
                    softLogout() {
                        this.stopGps();
                        this.current = null;
                        this.orders = [];
                        if (this.pollTimer) {
                            clearInterval(this.pollTimer);
                            this.pollTimer = null;
                        }
                        this.view = 'login';
                    },
                    setupInstallPrompt() {
                        if (this.installPromptInit) return;
                        this.installPromptInit = true;

                        // Di dalam APK tidak perlu banner install (sudah jadi aplikasi).
                        if (this.isNativeApp) return;

                        // Jangan tampilkan jika sudah berjalan dalam mode PWA / Standalone.
                        const isStandalone = window.matchMedia('(display-mode: standalone)').matches
                            || window.navigator.standalone === true;
                        if (isStandalone) return;

                        this.isIosBrowser = /iphone|ipad|ipod/i.test(navigator.userAgent || '');
                        this.isAndroidBrowser = /android/i.test(navigator.userAgent || '');

                        // Ambil link download APK dari server (jika tersedia).
                        fetch(API_V1 + '/app/version')
                            .then(r => r.json())
                            .then(d => { this.appDownloadUrl = d.download_url || ''; })
                            .catch(() => {});

                        if (this.isIosBrowser) this.iosInstallHint = true;
                        if (fsmInstallEvent) {
                            this.installEvent = fsmInstallEvent;
                            fsmInstallEvent = null;
                            this.installVisible = true;
                        }
                        window.addEventListener('beforeinstallprompt', (e) => {
                            e.preventDefault();
                            this.installEvent = e;
                            this.installVisible = true;
                            this.manualHint = false;
                        });
                        // Fallback: kalau di browser mobile event tidak terpancing, tampilkan petunjuk manual.
                        const isMobileBrowser = /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent || '');
                        setTimeout(() => {
                            if (isMobileBrowser && !this.installEvent &&
                                !this.installVisible && !this.iosInstallHint && !this.manualHint) {
                                if (this.isIosBrowser) {
                                    this.iosInstallHint = true;
                                } else {
                                    this.manualHint = true;
                                }
                            }
                        }, 1500);
                        window.addEventListener('appinstalled', () => {
                            this.installVisible = false;
                            this.iosInstallHint = false;
                            this.manualHint = false;
                            this.installEvent = null;
                            clearTimeout(this.installTimer);
                        });
                    },
                    maybeShowInstall() {
                        // Jalankan setelah tampilan beranda benar-benar muncul (render + jeda singkat).
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (this.view === 'home') this.setupInstallPrompt();
                            }, 300);
                        });
                    },
                    downloadApp() {
                        if (this.appDownloadUrl) window.open(this.appDownloadUrl, '_blank');
                    },
                    async installApp() {
                        if (!this.installEvent) return;
                        clearTimeout(this.installTimer);
                        this.installEvent.prompt();
                        try {
                            await this.installEvent.userChoice;
                        } catch (_) {}
                        this.installVisible = false;
                        this.installEvent = null;
                    },
                    dismissInstall() {
                        clearTimeout(this.installTimer);
                        this.installVisible = false;
                        this.iosInstallHint = false;
                        this.manualHint = false;
                    },
                    setupConnectivity() {
                        window.addEventListener('online', () => {
                            this.online = true;
                            this.showToast('Koneksi kembali ✓', 'success');
                        });
                        window.addEventListener('offline', () => {
                            this.online = false;
                        });
                        // Saat aplikasi kembali ke foreground, kirim ulang lokasi terakhir kalau sempat basi.
                        document.addEventListener('visibilitychange', () => {
                            if (!document.hidden) this.catchUpLocation();
                        });
                    },
                    setupPullToRefresh() {
                        window.addEventListener('touchstart', (e) => {
                            if (this.view !== 'home' || this.refreshing) {
                                this.pullStartY = null;
                                return;
                            }
                            if (window.scrollY > 0) {
                                this.pullStartY = null;
                                return;
                            }
                            this.pullStartY = e.touches[0].clientY;
                        }, { passive: true });
                        window.addEventListener('touchmove', (e) => {
                            if (this.pullStartY === null) return;
                            const dy = e.touches[0].clientY - this.pullStartY;
                            this.pullDistance = dy > 0 ? Math.min(dy * 0.5, 110) : 0;
                        }, { passive: true });
                        window.addEventListener('touchend', () => {
                            if (this.pullStartY === null) return;
                            if (this.pullDistance >= 70 && !this.refreshing) {
                                this.refreshing = true;
                                this.loadOrders().finally(() => {
                                    this.refreshing = false;
                                    this.pullDistance = 0;
                                    this.showToast('Data terbaru dimuat ✓', 'success');
                                });
                            } else {
                                this.pullDistance = 0;
                            }
                            this.pullStartY = null;
                        }, { passive: true });
                    },
                    matchSearch(wo, q) {
                        const number = String(wo.number || '').toLowerCase();
                        const cust = String(wo.customer && wo.customer.name || '').toLowerCase();
                        return number.includes(q) || cust.includes(q);
                    },
                    currentEmail() {
                        return String((this.user && this.user.email) || this.loginForm.email || '').trim().toLowerCase();
                    },
                    localPin() {
                        return localStorage.getItem(fsmLocalPinKey(this.currentEmail()));
                    },
                    openChangePin() {
                        this.pinChange = { stage: 'old', old: '', new: '', confirm: '', error: '' };
                        this.view = 'change-pin';
                    },
                    pinChangeCurrent() {
                        return this.pinChange[this.pinChange.stage] || '';
                    },
                    pinChangeKey(digit) {
                        const field = this.pinChange.stage;
                        if (this.pinChange[field].length >= 6) return;
                        this.pinChange[field] += digit;
                        this.pinChange.error = '';
                        if (this.pinChange[field].length === 6) {
                            if (this.pinChange.stage === 'old') this.pinChange.stage = 'new';
                            else if (this.pinChange.stage === 'new') this.pinChange.stage = 'confirm';
                            else this.submitChangePin();
                        }
                    },
                    pinChangeBack() {
                        const field = this.pinChange.stage;
                        this.pinChange[field] = this.pinChange[field].slice(0, -1);
                        this.pinChange.error = '';
                    },
                    async submitChangePin() {
                        const oldPin = this.pinChange.old;
                        const newPin = this.pinChange.new;
                        const confirm = this.pinChange.confirm;
                        if (!/^\d{6}$/.test(oldPin) || !/^\d{6}$/.test(newPin) || newPin !== confirm) {
                            this.pinChange.error = 'PIN tidak valid atau tidak sama.';
                            this.pinChange.stage = 'confirm';
                            this.pinChange.confirm = '';
                            return;
                        }
                        const status = await this.serverPinCheck(oldPin);
                        if (status !== 'ok' && this.localPin() !== oldPin) {
                            this.pinChange.error = status === 'wrong'
                                ? 'PIN lama salah.'
                                : 'Tidak dapat verifikasi — periksa koneksi.';
                            this.pinChange.stage = 'old';
                            this.pinChange.old = '';
                            return;
                        }
                        try {
                            await this.api('/auth/pin', { method: 'POST', body: { pin: newPin } });
                            localStorage.setItem(fsmLocalPinKey(this.currentEmail()), newPin);
                            this.showToast('PIN berhasil diganti ✅', 'success');
                            this.view = 'home';
                        } catch (err) {
                            this.pinChange.error = 'Gagal menyimpan PIN baru.';
                            this.pinChange.stage = 'new';
                            this.pinChange.new = '';
                            this.pinChange.confirm = '';
                        }
                    },
                    toggleBio() {
                        const key = 'fsm_bio_' + this.currentEmail();
                        const next = !this.bioEnabledState;
                        this.bioEnabledState = next;
                        if (next) localStorage.setItem(key, '1');
                        else localStorage.removeItem(key);
                    },
                    syncBioState() {
                        this.bioEnabledState = localStorage.getItem('fsm_bio_' + this.currentEmail()) === '1';
                    },
                    async serverPinCheck(pin) {
                        if (!this.token) return 'error';
                        try {
                            const res = await fetch(API_V1 + '/auth/pin/verify', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + this.token
                                },
                                body: JSON.stringify({ pin }),
                            });
                            if (res.ok) return 'ok';
                            return res.status === 422 ? 'wrong' : 'error';
                        } catch (_) {
                            return 'error';
                        }
                    },
                    promptPinSetup() {
                        this.pinSetup = { stage: 'first', first: '', confirm: '', error: '' };
                        this.view = 'setup-pin';
                    },
                    pinSetupKey(digit) {
                        if (this.busy) return;
                        if (this.pinSetup.stage === 'first') {
                            if (this.pinSetup.first.length >= 6) return;
                            this.pinSetup.first += digit;
                            this.pinSetup.error = '';
                            if (this.pinSetup.first.length === 6) {
                                this.pinSetup.stage = 'confirm';
                            }
                        } else {
                            if (this.pinSetup.confirm.length >= 6) return;
                            this.pinSetup.confirm += digit;
                            this.pinSetup.error = '';
                            if (this.pinSetup.confirm.length === 6) {
                                this.submitPinSetup();
                            }
                        }
                    },
                    pinSetupBack() {
                        if (this.busy) return;
                        if (this.pinSetup.stage === 'confirm') {
                            this.pinSetup.confirm = this.pinSetup.confirm.slice(0, -1);
                        } else {
                            this.pinSetup.first = this.pinSetup.first.slice(0, -1);
                        }
                        this.pinSetup.error = '';
                    },
                    async submitPinSetup() {
                        const first = this.pinSetup.first;
                        const confirm = this.pinSetup.confirm;
                        if (!/^\d{6}$/.test(first)) {
                            this.pinSetup.error = 'PIN harus 6 digit angka.';
                            this.pinSetup.stage = 'first';
                            this.pinSetup.first = '';
                            return;
                        }
                        if (first !== confirm) {
                            this.pinSetup.error = 'PIN tidak sama, ulangi PIN.';
                            this.pinSetup.confirm = '';
                            this.pinSetup.stage = 'confirm';
                            return;
                        }
                        this.busy = true;
                        try {
                            await this.api('/auth/pin', { method: 'POST', body: { pin: first } });
                            localStorage.setItem(fsmLocalPinKey(this.currentEmail()), first);
                            this.view = 'home';
                            this.showToast('PIN berhasil dibuat 🔐', 'success');
                        } catch (err) {
                            this.pinSetup.error = 'Gagal menyimpan PIN, coba lagi.';
                        } finally {
                            this.busy = false;
                        }
                    },
                    async pinKey(digit) {
                        if (this.pinEntry.length >= 6) return;
                        this.pinEntry += digit;
                        this.pinError = '';
                        if (this.pinEntry.length === 6) await this.verifyPin();
                    },
                    pinBack() {
                        this.pinEntry = this.pinEntry.slice(0, -1);
                        this.pinError = '';
                    },
                    async verifyPin() {
                        const pin = this.pinEntry;
                        const status = await this.serverPinCheck(pin);
                        const ok = status === 'ok' || this.localPin() === pin;
                        if (!ok) {
                            this.pinEntry = '';
                            this.pinError = status === 'wrong'
                                ? 'PIN salah, coba lagi.'
                                : 'Tidak dapat verifikasi — periksa koneksi.';
                            return;
                        }
                        if (status === 'ok') localStorage.setItem(fsmLocalPinKey(this.currentEmail()), pin);
                        if (this.pendingRelogin) {
                            const relogged = await this.pinLogin(pin);
                            if (!relogged) {
                                this.pinEntry = '';
                                this.pinError = 'Gagal memperbarui sesi — coba lagi.';
                                return;
                            }
                        }
                        this.pendingRelogin = false;
                        this.unlock();
                    },
                    async pinLogin(pin) {
                        try {
                            const res = await fetch(API_V1 + '/auth/pin/login', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ email: this.currentEmail(), pin }),
                            });
                            const data = await res.json();
                            if (!res.ok) return false;
                            this.token = data.token;
                            this.user = data.user;
                            localStorage.setItem('fsm_tech_token', this.token);
                            localStorage.setItem('fsm_tech_user', JSON.stringify(this.user));
                            return true;
                        } catch (_) {
                            return false;
                        }
                    },
                    unlock() {
                        this.pinEntry = '';
                        this.pinError = '';
                        this.locked = false;
                        localStorage.removeItem('fsm_locked');
                        this.view = 'home';
                        this.maybeShowInstall();
                        if (!this.pollTimer) {
                            this.loadOrders();
                            this.pollTimer = setInterval(() => this.loadOrders(true), 45000);
                        }
                    },
                    async hashPin(pin, salt) {
                        const text = salt + ':' + pin;
                        if (window.crypto && crypto.subtle) {
                            const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(text));
                            return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, '0')).join('');
                        }
                        let h = 5381;
                        for (let i = 0; i < text.length; i++) h = ((h << 5) + h + text.charCodeAt(i)) >>> 0;
                        return 'djb2:' + h.toString(16);
                    },
                    randomSalt() {
                        if (window.crypto && crypto.getRandomValues) {
                            const a = new Uint8Array(8);
                            crypto.getRandomValues(a);
                            return Array.from(a).map(b => b.toString(16).padStart(2, '0')).join('');
                        }
                        return Math.random().toString(36).slice(2) + Date.now().toString(36);
                    },
                    async detectBiometric() {
                        try {
                            if (!window.Capacitor || !window.Capacitor.isNativePlatform || !window.Capacitor.isNativePlatform()) {
                                this.biometricAvailable = false;
                                return;
                            }
                            // checkBiometry() wajib dipanggil sebelum authenticate()
                            const result = await BiometricAuth.checkBiometry();
                            this.biometricAvailable = result.isAvailable || false;
                        } catch (_) {
                            this.biometricAvailable = false;
                        }
                    },
                    async tryBiometric() {
                        try {
                            if (!window.Capacitor || !window.Capacitor.isNativePlatform || !window.Capacitor.isNativePlatform()) return;
                            // authenticate() resolve saat sukses, reject (BiometryError) saat gagal/batal
                            await BiometricAuth.authenticate({
                                reason: 'Buka FSM Teknisi',
                                cancelTitle: 'Batal',
                                iosFallbackTitle: 'Gunakan Passcode',
                                androidTitle: 'Autentikasi',
                                androidSubtitle: 'Sentuh sensor sidik jari',
                            });
                            this.unlock();
                        } catch (err) {
                            this.pinError = 'Autentikasi gagal, gunakan PIN.';
                        }
                    },
                    async runAction(act) {
                        if (act.key === 'accept') {
                            this.busy = true;
                            try {
                                const a = this.myAssignment(this.current);
                                await this.api('/assignments/' + a.id + '/accept', { method: 'POST' });
                                this.showToast('Pekerjaan diterima. Gas! 🔥', 'success');
                                await this.reloadDetail();
                            } catch (err) {
                                this.showToast(err.message, 'error');
                            } finally {
                                this.busy = false;
                            }
                            return;
                        }
                        if (act.key === 'reject') {
                            this.modal = {
                                show: true,
                                mode: 'reject',
                                reason: '',
                                title: 'Tolak Pekerjaan?',
                                desc: 'Kasih tahu koordinator alasannya, biar bisa dicarikan solusi.',
                                chips: ['Jadwal bentrok', 'Lokasi terlalu jauh', 'Sedang ada pekerjaan lain', 'Sakit / izin'],
                                error: ''
                            };
                            return;
                        }
                        if (act.key === 'fail') {
                            this.modal = {
                                show: true,
                                mode: 'fail',
                                reason: '',
                                title: 'Laporkan Kendala',
                                desc: 'Jelaskan kendalanya, tim koordinator akan segera tindak lanjut.',
                                chips: ['Customer tidak di tempat', 'Akses lokasi terhambat', 'Kendala teknis', 'Kendaraan bermasalah'],
                                error: ''
                            };
                            return;
                        }
                        if (act.key === 'arrive') {
                            this.askConfirm('Konfirmasi Kedatangan', 'Yakin kamu sudah tiba di lokasi pelanggan? 📍', () => this.executeAction(act));
                            return;
                        }
                        if (act.key === 'finish') {
                            this.openFinishSheet();
                            return;
                        }
                        await this.executeAction(act);
                    },
                    async executeAction(act) {
                        this.busy = true;
                        try {
                            await this.api('/work-orders/' + this.current.id + '/' + act.key, { method: 'POST' });
                            this.showToast('Berhasil! 🎉', 'success');
                            await this.reloadDetail();
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        } finally {
                            this.busy = false;
                        }
                    },
                    openFinishSheet() {
                        this.finishSheet = {
                            show: true,
                            photos: [],
                            note: '',
                            error: '',
                            uploading: false,
                        };
                    },
                    closeFinishSheet() {
                        if (this.finishSheet.uploading) return;
                        this.finishSheet.show = false;
                    },
                    async addFinishPhoto(source) {
                        if (this.finishSheet.photos.length >= this.maxPhotos) {
                            this.finishSheet.error = 'Maksimal ' + this.maxPhotos + ' foto.';
                            return;
                        }
                        this.finishSheet.error = '';
                        try {
                            if (this.isNativeApp) {
                                const photo = await Camera.getPhoto({
                                    quality: 60,
                                    width: 1280,
                                    correctOrientation: true,
                                    resultType: CameraResultType.DataUrl,
                                    source: source === 'camera' ? CameraSource.Camera : CameraSource.Photos,
                                });
                                const blob = this.dataUrlToBlob(photo.dataUrl);
                                this.pushFinishPhoto(blob, 'foto.' + (photo.format || 'jpeg'));
                            } else {
                                // Fallback browser: input file.
                                const input = document.createElement('input');
                                input.type = 'file';
                                input.accept = 'image/*';
                                if (source === 'camera') input.capture = 'environment';
                                input.onchange = async () => {
                                    const file = input.files && input.files[0];
                                    if (file) {
                                        const compressed = await this.compressImage(file);
                                        this.pushFinishPhoto(compressed, file.name || 'foto.jpg');
                                    }
                                };
                                input.click();
                            }
                        } catch (err) {
                            // User batal ambil foto — abaikan.
                        }
                    },
                    pushFinishPhoto(blob, name) {
                        if (!blob) return;
                        if (this.finishSheet.photos.length >= this.maxPhotos) {
                            this.finishSheet.error = 'Maksimal ' + this.maxPhotos + ' foto.';
                            return;
                        }
                        const preview = URL.createObjectURL(blob);
                        this.finishSheet.photos.push({ blob, name, preview });
                    },
                    removeFinishPhoto(idx) {
                        const p = this.finishSheet.photos[idx];
                        if (p && p.preview) URL.revokeObjectURL(p.preview);
                        this.finishSheet.photos.splice(idx, 1);
                    },
                    dataUrlToBlob(dataUrl) {
                        const parts = dataUrl.split(',');
                        const mime = (parts[0].match(/:(.*?);/) || [])[1] || 'image/jpeg';
                        const bstr = atob(parts[1]);
                        let n = bstr.length;
                        const u8 = new Uint8Array(n);
                        while (n--) u8[n] = bstr.charCodeAt(n);
                        return new Blob([u8], { type: mime });
                    },
                    compressImage(file, maxDim = 1280, quality = 0.6) {
                        return new Promise((resolve) => {
                            const img = new Image();
                            const url = URL.createObjectURL(file);
                            img.onload = () => {
                                let { width, height } = img;
                                if (width > height && width > maxDim) {
                                    height = Math.round(height * maxDim / width);
                                    width = maxDim;
                                } else if (height > maxDim) {
                                    width = Math.round(width * maxDim / height);
                                    height = maxDim;
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = width;
                                canvas.height = height;
                                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                                URL.revokeObjectURL(url);
                                canvas.toBlob((blob) => resolve(blob || file), 'image/jpeg', quality);
                            };
                            img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
                            img.src = url;
                        });
                    },
                    async submitFinish() {
                        if (this.finishSheet.uploading) return;
                        if (this.finishSheet.photos.length < 1) {
                            this.finishSheet.error = 'Minimal 1 foto pemasangan wajib dilampirkan.';
                            return;
                        }
                        this.finishSheet.uploading = true;
                        this.finishSheet.error = '';
                        this.busy = true;
                        try {
                            const form = new FormData();
                            this.finishSheet.photos.forEach((p) => form.append('photos[]', p.blob, p.name));
                            if (this.finishSheet.note.trim()) form.append('note', this.finishSheet.note.trim());
                            await this.apiUpload('/work-orders/' + this.current.id + '/finish', form);
                            this.finishSheet.photos.forEach((p) => p.preview && URL.revokeObjectURL(p.preview));
                            this.finishSheet.show = false;
                            this.showToast('Pemasangan selesai! 🎉', 'success');
                            await this.reloadDetail();
                        } catch (err) {
                            this.finishSheet.error = err.message || 'Gagal menyelesaikan pekerjaan.';
                        } finally {
                            this.finishSheet.uploading = false;
                            this.busy = false;
                        }
                    },
                    async submitReason() {
                        const reason = this.modal.reason.trim();
                        if (!reason || this.busy) return;
                        this.busy = true;
                        this.modal.error = '';
                        try {
                            if (this.modal.mode === 'reject') {
                                const a = this.myAssignment(this.current);
                                await this.api('/assignments/' + a.id + '/reject', { method: 'POST', body: { reason } });
                                this.showToast('Pekerjaan ditolak. Terima kasih atas kejujurannya.', 'success');
                            } else {
                                await this.api('/work-orders/' + this.current.id + '/fail', { method: 'POST', body: { reason } });
                                this.showToast('Kendala sudah dilaporkan ke koordinator.', 'success');
                            }
                            this.modal.show = false;
                            await this.reloadDetail();
                        } catch (err) {
                            this.modal.error = err.message;
                        } finally {
                            this.busy = false;
                        }
                    },
                    async reloadDetail() {
                        if (!this.current) return;
                        try {
                            const data = await this.api('/work-orders/' + this.current.id);
                            this.current = data.data || data;
                            const session = this.current.tracking_sessions
                                ? this.current.tracking_sessions.find(s => s.status === 'active')
                                : null;
                            if (this.current.status === 'on_the_way' && session) this.tripSessionId = session.id;
                            if (this.current.status === 'on_the_way') {
                                this.startGps();
                            } else {
                                this.tripSessionId = null;
                                this.stopGps();
                            }
                            this.$nextTick(() => this.initMap());
                            await this.loadOrders(true);
                        } catch (err) {
                            this.showToast(err.message, 'error');
                        }
                    },
                    async syncTracking() {
                        if (!this.token) return;
                        const trip = this.orders.find(wo =>
                            wo.status === 'on_the_way' && this.myAssignment(wo) && this.myAssignment(wo).status === 'accepted'
                        );
                        if (!trip) {
                            this.tripSessionId = null;
                            this.stopGps();
                            return;
                        }
                        if (!this.tripSessionId) {
                            try {
                                const data = await this.api('/work-orders/' + trip.id);
                                const wo = data.data || data;
                                const session = (wo.tracking_sessions || []).find(s => s.status === 'active');
                                if (session) this.tripSessionId = session.id;
                            } catch (err) {
                                return; // coba lagi di polling berikutnya
                            }
                        }
                        if (this.tripSessionId) this.startGps();
                    },
                    activeSession() {
                        if (!this.current || !this.current.tracking_sessions) return null;
                        return this.current.tracking_sessions.find(s => s.status === 'active') || null;
                    },
                    startGps() {
                        if (this.watchId !== null || this.bgWatcherId !== null) return;
                        this.keepScreenAwake();

                        // Di dalam APK: pakai background geolocation (tetap jalan saat app di-minimize).
                        if (window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform()) {
                            this.gpsState = 'starting';
                            this.requestBatteryOptimizationExemption();
                            if (!localStorage.getItem('fsm_battery_guide_shown')) {
                                localStorage.setItem('fsm_battery_guide_shown', '1');
                                this.askConfirm(
                                    'Tracking & Baterai',
                                    'Agar lokasi tetap terkirim saat layar mati, izinkan aplikasi tanpa batasan baterai. Buka pengaturannya?',
                                    () => this.openBatterySettings(),
                                );
                            }
                            BackgroundGeolocation.addWatcher(
                                {
                                    backgroundMessage: 'Melacak perjalanan teknisi',
                                    backgroundTitle: 'FSM Teknisi aktif',
                                    requestPermissions: true,
                                    stale: false,
                                    distanceFilter: 10,
                                },
                                (location, error) => {
                                    if (error) {
                                        this.gpsState = 'error';
                                        return;
                                    }
                                    if (!location) return;
                                    // Samakan bentuk dengan navigator.geolocation (coords.*).
                                    this.lastPos = {
                                        coords: {
                                            latitude: location.latitude,
                                            longitude: location.longitude,
                                            accuracy: location.accuracy,
                                            speed: location.speed,
                                            heading: location.bearing,
                                        },
                                    };
                                    this.gpsState = 'active';
                                    this.sendLocation();
                                },
                            ).then((id) => { this.bgWatcherId = id; });

                            this.gpsTimer = setInterval(() => {
                                if (this.lastPos && this.onTrip) this.sendLocation();
                            }, 15000);
                            return;
                        }

                        // Di browser: fallback ke Geolocation API biasa (hanya jalan saat app dibuka).
                        if (!('geolocation' in navigator)) {
                            this.gpsState = 'error';
                            return;
                        }
                        this.gpsState = 'starting';
                        this.watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                this.lastPos = pos;
                                this.gpsState = 'active';
                                this.sendLocation();
                            },
                            () => {
                                this.gpsState = 'error';
                            },
                            { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 },
                        );
                        this.gpsTimer = setInterval(() => {
                            if (this.lastPos && this.onTrip) this.sendLocation();
                        }, 15000);
                    },
                    async requestBatteryOptimizationExemption() {
                        try {
                            if (!window.Capacitor || !window.Capacitor.isNativePlatform
                                || !window.Capacitor.isNativePlatform()) return;
                            const { enabled } = await BatteryOptimization.isBatteryOptimizationEnabled();
                            if (!enabled) return; // sudah dibebaskan — tidak perlu apa-apa
                            // Dialog sistem cukup diminta SEKALI per install, biar tidak
                            // muncul ulang tiap app dibuka saat perjalanan aktif.
                            if (localStorage.getItem('fsm_battery_requested') === '1') return;
                            localStorage.setItem('fsm_battery_requested', '1');
                            await BatteryOptimization.requestIgnoreBatteryOptimization();
                        } catch (_) { /* dialog dibatalkan user — aman */ }
                    },
                    async openBatterySettings() {
                        try {
                            if (this.isNativeApp) {
                                await BatteryOptimization.openBatteryOptimizationSettings();
                            }
                        } catch (_) { /* tidak bisa dibuka — abaikan */ }
                    },
                    async keepScreenAwake() {
                        try {
                            if (this.isNativeApp) {
                                await KeepAwake.keepAwake();
                            } else if (navigator.wakeLock && navigator.wakeLock.request) {
                                this.wakeLockSentinel = await navigator.wakeLock.request('screen');
                            }
                        } catch (_) { /* layar tetap boleh tidur — aman */ }
                    },
                    async allowScreenSleep() {
                        try {
                            if (this.isNativeApp) {
                                await KeepAwake.allowSleep();
                            } else if (this.wakeLockSentinel) {
                                await this.wakeLockSentinel.release();
                                this.wakeLockSentinel = null;
                            }
                        } catch (_) {}
                    },
                    async sendLocation() {
                        const sessionId = this.tripSessionId || (this.activeSession() ? this.activeSession().id : null);
                        if (!sessionId || !this.lastPos) return;
                        const pos = this.lastPos.coords;
                        try {
                            await this.api('/tracking-sessions/' + sessionId + '/locations', {
                                method: 'POST',
                                body: {
                                    latitude: +pos.latitude.toFixed(7),
                                    longitude: +pos.longitude.toFixed(7),
                                    accuracy_meters: pos.accuracy != null ? Math.round(pos.accuracy) : null,
                                    speed_mps: pos.speed != null ? Math.round(pos.speed * 100) / 100 : null,
                                    heading_degrees: pos.heading != null ? Math.round(pos.heading) : null,
                                    recorded_at: new Date().toISOString(),
                                },
                            });
                            this.gpsSentLabel = new Date().toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            this.lastGpsSentAt = Date.now();
                        } catch (err) { /* dikirim ulang pada interval berikutnya */ }
                    },
                    catchUpLocation() {
                        if (!this.onTrip || !this.lastPos || !this.tripSessionId) return;
                        const staleMs = Date.now() - this.lastGpsSentAt;
                        if (staleMs > 45000) {
                            this.sendLocation();
                            if (staleMs > 120000) {
                                this.showToast('Tracking sempat terputus — posisi terakhir dikirim ulang.', 'info');
                            }
                        }
                    },
                    stopGps() {
                        if (this.bgWatcherId !== null) {
                            BackgroundGeolocation.removeWatcher({ id: this.bgWatcherId }).catch(() => {});
                            this.bgWatcherId = null;
                        }
                        if (this.watchId !== null) {
                            navigator.geolocation.clearWatch(this.watchId);
                            this.watchId = null;
                        }
                        if (this.gpsTimer) {
                            clearInterval(this.gpsTimer);
                            this.gpsTimer = null;
                        }
                        this.gpsState = 'off';
                        this.lastPos = null;
                        this.allowScreenSleep();
                    },
                    initMap() {
                        if (typeof L === 'undefined') return;
                        const loc = this.current && this.current.service_location;
                        const el = document.getElementById('detail-map');
                        if (!loc || !loc.latitude || !el) return;
                        this.destroyMap();
                        const lat = parseFloat(loc.latitude),
                            lng = parseFloat(loc.longitude);
                        this.mapInstance = L.map('detail-map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.mapInstance);
                        this.mapMarker = L.marker([lat, lng]).addTo(this.mapInstance);
                        if (this.lastPos && this.lastPos.coords) {
                            const c = this.lastPos.coords;
                            this.mapPosMarker = L.circleMarker([c.latitude, c.longitude], {
                                radius: 8,
                                color: '#d00202',
                                fillColor: '#d00202',
                                fillOpacity: .35
                            }).addTo(this.mapInstance);
                        }
                    },
                    destroyMap() {
                        if (this.mapInstance) {
                            this.mapInstance.remove();
                            this.mapInstance = null;
                        }
                        this.mapMarker = null;
                        this.mapPosMarker = null;
                    },
                    openChangePass() {
                        this.passModal = {
                            show: false,
                            current: '',
                            next: '',
                            confirm: '',
                            error: ''
                        };
                        this.view = 'change-pass';
                    },
                    async submitPasswordChange() {
                        const p = this.passModal;
                        this.busy = true;
                        p.error = '';
                        try {
                            await this.api('/auth/change-password', {
                                method: 'POST',
                                body: {
                                    current_password: p.current,
                                    new_password: p.next,
                                    new_password_confirmation: p.confirm
                                },
                            });
                            this.view = 'home';
                            this.showToast('Password berhasil diganti! 🔐', 'success');
                        } catch (err) {
                            p.error = err.message;
                        } finally {
                            this.busy = false;
                        }
                    }
                }
};
</script>

<style>
        /* =========================================================
           DESIGN SYSTEM — IML FSM Mobile
        ========================================================= */
        :root {
            /* Navy palette */
            --navy-900: #061429;
            --navy-800: #0b2044;
            --navy-700: #112b5c;
            --navy-600: #1a3a7a;
            --navy-500: #2451a0;
            --navy-400: #3a6bc8;

            /* Red palette */
            --red-700: #8b0c1e;
            --red-600: #a81226;
            --red-500: #c8102e;
            --red-400: #e01836;
            --red-100: #ffe4e9;

            /* Gradients */
            --brand-grad: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-700) 60%, var(--navy-600) 100%);
            --red-grad: linear-gradient(135deg, var(--red-400) 0%, var(--red-700) 100%);

            /* Backgrounds */
            --bg: #f0f4fb;
            --bg-2: #e8eef9;
            --surface: #ffffff;
            --surface-2: #f6f9ff;
            --glass: rgba(255, 255, 255, .12);
            --glass-bdr: rgba(255, 255, 255, .20);

            /* Text */
            --ink: #0d1b35;
            --ink-2: #2c3e65;
            --muted: #64748b;
            --on-dark: #ffffff;
            --on-dark-2: rgba(255, 255, 255, .75);

            /* Borders */
            --line: #e2e8f4;
            --line-2: #cbd5e8;

            /* Status */
            --green: #059669;
            --green-bg: #d1fae5;
            --amber: #d97706;
            --amber-bg: #fef3c7;
            --sky: #0284c7;
            --sky-bg: #e0f2fe;
            --violet: #7c3aed;
            --vlt-bg: #ede9fe;
            --rose: #e11d48;
            --rose-bg: #ffe4e6;
            --slate: #475569;
            --slate-bg: #e2e8f0;

            /* Shadows */
            --shadow-sm: 0 1px 4px rgba(11, 32, 68, .06);
            --shadow: 0 4px 16px rgba(11, 32, 68, .10);
            --shadow-lg: 0 10px 32px rgba(11, 32, 68, .14);

            /* Shape */
            --r-sm: 10px;
            --r: 16px;
            --r-lg: 22px;
            --r-xl: 28px;

            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        [v-cloak] {
            display: none;
        }

        #app {
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: var(--bg);
        }

        .app-main-content {
            padding-bottom: calc(80px + env(safe-area-inset-bottom));
        }

        /* =========================================================
           LOGIN SCREEN
        ========================================================= */
        .login-screen {
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: calc(env(safe-area-inset-top, 20px) + 20px) 20px calc(env(safe-area-inset-bottom, 20px) + 20px);
        }

        .login-top {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 380px;
            margin-bottom: 20px;
        }

        .login-logo-wrap {
            background: #ffffff;
            border-radius: var(--r-lg);
            padding: 10px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .3);
            margin-bottom: 16px;
        }

        .login-logo-wrap img {
            height: 44px;
            width: auto;
            display: block;
        }

        .login-screen h1 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 4px;
            text-align: center;
        }

        .login-tagline {
            text-align: center;
            color: var(--on-dark-2);
            font-size: 13.5px;
            margin: 0;
        }

        .login-card {
            background: var(--surface);
            color: var(--ink);
            border-radius: var(--r-xl);
            padding: 24px 20px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 380px;
        }

        .login-card label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            margin: 14px 0 6px;
        }

        .login-card label:first-of-type {
            margin-top: 0;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            opacity: .5;
        }

        .login-card input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            font-size: 15px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
        }

        .login-card input.input-has-eye {
            padding-right: 46px;
        }

        .login-eye {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            font-size: 17px;
            cursor: pointer;
            border-radius: 8px;
        }

        .login-eye:active {
            background: rgba(0, 0, 0, .06);
        }

        .btn-login {
            width: 100%;
            margin-top: 20px;
            border: 0;
            border-radius: var(--r-sm);
            padding: 15px;
            background: var(--red-grad);
            color: #fff;
            font-size: 15.5px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(200, 16, 46, .35);
        }

        .login-error {
            background: var(--red-100);
            color: var(--red-700);
            border: 1px solid #fecdd3;
            border-radius: var(--r-sm);
            padding: 11px 14px;
            font-size: 13px;
            margin-top: 14px;
            display: flex;
            gap: 8px;
        }

        .login-actions-row {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .login-actions-row .btn-login {
            flex: 1;
            margin-top: 0;
        }

        .btn-pin-quick {
            width: 56px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            background: var(--surface-2);
            color: var(--red-500);
            font-size: 20px;
            cursor: pointer;
        }

        .btn-pin-quick:active {
            background: rgba(200, 16, 46, .1);
        }

        /* =========================================================
           APP HEADER & NAVIGATION
        ========================================================= */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0px) 16px 0;
            box-shadow: 0 4px 20px rgba(6, 20, 41, .15);
        }

        .app-header-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
        }

        .logo-chip {
            background: #ffffff;
            border-radius: 10px;
            padding: 5px 9px;
            flex-shrink: 0;
        }

        .logo-chip img {
            height: 22px;
            display: block;
        }

        .header-title {
            flex: 1;
            min-width: 0;
        }

        .header-title strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.2;
        }

        .header-title span {
            font-size: 11px;
            color: var(--on-dark-2);
        }

        .icon-btn {
            background: var(--glass);
            border: 1px solid var(--glass-bdr);
            color: var(--on-dark);
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 17px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .greet-band {
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: 4px 16px 28px;
        }

        .greet-band h2 {
            margin: 0 0 2px;
            font-size: 20px;
            font-weight: 800;
        }

        .greet-band p {
            margin: 0;
            color: var(--on-dark-2);
            font-size: 13px;
        }

        /* =========================================================
           TAB SWITCHER & HORIZONTAL CHIPS
        ========================================================= */
        .tab-switcher-wrapper {
            padding: 0 16px;
            margin-top: -20px;
            position: relative;
            z-index: 10;
        }

        .tab-switcher {
            display: flex;
            background: var(--surface);
            border: 1px solid var(--line);
            padding: 4px;
            border-radius: var(--r);
            box-shadow: var(--shadow);
        }

        .tab-btn {
            flex: 1;
            border: 0;
            background: transparent;
            padding: 11px 8px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--muted);
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s var(--ease);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .tab-btn.active {
            background: var(--brand-grad);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(11, 32, 68, .2);
        }

        .tab-count {
            background: rgba(255, 255, 255, 0.25);
            padding: 2px 7px;
            border-radius: 99px;
            font-size: 11px;
        }

        .tab-btn:not(.active) .tab-count {
            background: var(--bg-2);
            color: var(--muted);
        }

        .scroll-x-bar {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 14px 16px 4px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .scroll-x-bar::-webkit-scrollbar {
            display: none;
        }

        .filter-chip {
            flex-shrink: 0;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--muted);
            padding: 7px 14px;
            border-radius: 99px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
        }

        .filter-chip.active {
            background: var(--navy-800);
            color: #ffffff;
            border-color: var(--navy-800);
        }

        /* =========================================================
           CARDS & CONTENT LIST
        ========================================================= */
        .section {
            padding: 8px 16px;
        }

        .wo-card {
            background: var(--surface);
            border-radius: var(--r);
            box-shadow: var(--shadow-sm);
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--line);
            display: block;
            width: 100%;
            text-align: left;
            font-family: inherit;
            color: inherit;
            cursor: pointer;
        }

        .wo-card:active {
            transform: scale(.982);
            border-color: var(--navy-300);
        }

        .wo-card .row1 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .wo-card .number {
            font-weight: 800;
            font-size: 15px;
            color: var(--navy-700);
        }

        .wo-card .cust {
            font-weight: 700;
            font-size: 14.5px;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .wo-card .sub {
            font-size: 12.5px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .b-amber {
            background: var(--amber-bg);
            color: #92400e;
        }

        .b-green {
            background: var(--green-bg);
            color: #065f46;
        }

        .b-sky {
            background: var(--sky-bg);
            color: #075985;
        }

        .b-violet {
            background: var(--vlt-bg);
            color: #5b21b6;
        }

        .b-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .b-indigo {
            background: #e0e7ff;
            color: #3730a3;
        }

        .b-rose {
            background: var(--rose-bg);
            color: #9f1239;
        }

        .b-red {
            background: var(--red-100);
            color: var(--red-700);
        }

        .b-gray {
            background: var(--slate-bg);
            color: var(--slate);
        }

        .empty {
            background: var(--surface);
            border: 1.5px dashed var(--line-2);
            border-radius: var(--r);
            padding: 28px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 13.5px;
        }

        .empty .big {
            font-size: 36px;
            display: block;
            margin-bottom: 8px;
        }

        /* =========================================================
           DETAIL VIEW
        ========================================================= */
        .detail-head {
            position: sticky;
            top: 0;
            z-index: 40;
            background: var(--brand-grad);
            color: var(--on-dark);
            padding: env(safe-area-inset-top, 0) 14px 0;
        }

        .detail-head-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
        }

        .detail-head .d-num {
            font-size: 16px;
            font-weight: 800;
        }

        .detail-head .d-sub {
            font-size: 11.5px;
            color: var(--on-dark-2);
        }

        .back-btn {
            background: var(--glass);
            border: 1px solid var(--glass-bdr);
            color: var(--on-dark);
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-body {
            padding: 14px 16px 100px;
        }

        .status-banner {
            border-radius: var(--r);
            padding: 16px;
            color: #fff;
            margin-bottom: 14px;
        }

        .status-banner .s-label {
            font-size: 17px;
            font-weight: 800;
        }

        .status-banner .s-hint {
            opacity: .9;
            font-size: 12.5px;
            margin-top: 4px;
        }

        .card {
            background: var(--surface);
            border-radius: var(--r);
            border: 1px solid var(--line);
            padding: 16px;
            margin-bottom: 12px;
        }

        .card h4 {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--muted);
        }

        .kv {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--bg-2);
            font-size: 14px;
        }

        .kv:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .kv .k {
            color: var(--muted);
        }

        .kv .v {
            text-align: right;
            font-weight: 600;
            color: var(--ink-2);
        }

        .wo-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--bg-2);
            font-size: 14px;
        }

        .wo-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .wo-item-name {
            font-weight: 700;
            color: var(--ink-2);
        }

        .wo-item-qty {
            flex-shrink: 0;
            font-weight: 800;
            color: var(--primary);
        }

        #detail-map {
            position: relative;
            z-index: 0;
            height: 180px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line);
            margin-bottom: 10px;
        }

        .btn-maps-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 0;
            border-radius: var(--r-sm);
            padding: 12px;
            background: #4285f4;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .sticky-actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 60;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            display: grid;
            gap: 8px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 0;
            border-radius: var(--r-sm);
            padding: 15px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            color: #fff;
        }

        .action-btn.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .action-btn.violet {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        }

        .action-btn.blue {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
        }

        .action-btn.amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .action-btn.ghost {
            background: var(--surface-2);
            color: var(--ink-2);
            border: 1.5px solid var(--line);
        }

        .gps-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 99px;
            background: rgba(16, 185, 129, .12);
            border: 1px solid rgba(16, 185, 129, .35);
            color: var(--green);
            font-size: 12.5px;
            font-weight: 700;
        }

        .gps-pill .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 1.4s infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .chip {
            padding: 8px 12px;
            border-radius: 99px;
            background: var(--surface-2);
            border: 1.5px solid var(--line);
            color: var(--ink-2);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
        }

        .chip.selected {
            background: var(--red-100);
            border-color: var(--red-500);
            color: var(--red-500);
        }

        .reason-box textarea {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            padding: 12px;
            font-size: 14.5px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            resize: none;
            min-height: 96px;
            outline: none;
        }

        .reason-box textarea:focus {
            border-color: var(--red-500);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .12);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-around;
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
        }

        .nav-item {
            background: none;
            border: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--muted);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .nav-item.active {
            color: var(--red-500);
            font-weight: 800;
        }

        .nav-item .nav-icon {
            font-size: 19px;
        }

        .toast {
            position: fixed;
            top: calc(env(safe-area-inset-top, 12px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            background: var(--navy-800);
            color: #fff;
            padding: 12px 20px;
            border-radius: var(--r-sm);
            font-size: 13.5px;
            box-shadow: var(--shadow-lg);
        }

        .update-banner {
            position: fixed;
            top: calc(env(safe-area-inset-top, 12px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 95;
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #c8102e, #8b0c1e);
            color: #fff;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(200, 16, 46, .35);
            max-width: calc(100vw - 32px);
            cursor: pointer;
        }

        .update-banner .ub-close {
            background: rgba(255, 255, 255, .18);
            border: 0;
            color: #fff;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .install-sheet {
            position: fixed;
            top: calc(env(safe-area-inset-top, 12px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 300;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            width: calc(100vw - 32px);
            max-width: 420px;
            background: #fff;
            color: var(--ink);
            border-radius: 18px;
            padding: 16px;
            box-shadow: 0 18px 48px rgba(6, 20, 41, .30);
            border: 1px solid var(--line);
            animation: sheetDown .28s cubic-bezier(.2, .8, .2, 1);
        }

        @keyframes sheetDown {
            from {
                transform: translate(-50%, -24px);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        .install-sheet .is-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #112b5c, #2451a0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .install-sheet .is-body {
            flex: 1;
        }

        .install-sheet .is-title {
            font-size: 15px;
            font-weight: 900;
            color: var(--ink);
        }

        .install-sheet .is-desc {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
            line-height: 1.4;
        }

        .install-sheet .is-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .install-sheet .is-btn {
            border: 0;
            background: var(--red-grad);
            color: #fff;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .install-sheet .is-later {
            border: 1.5px solid var(--line);
            background: var(--surface-2);
            color: var(--ink-2);
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .offline-banner {
            position: fixed;
            top: calc(env(safe-area-inset-top, 12px) + 12px);
            left: 50%;
            transform: translateX(-50%);
            z-index: 96;
            background: #b45309;
            color: #fff;
            padding: 9px 16px;
            border-radius: 99px;
            font-size: 12.5px;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(180, 83, 9, .35);
            white-space: nowrap;
        }

        .ptr-indicator {
            height: 0;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            transition: height .15s;
        }

        .ptr-indicator.active {
            height: 44px;
        }

        .ptr-indicator.refreshing span {
            animation: spin .75s linear infinite;
        }

        .search-bar {
            position: relative;
            margin: 4px 16px 12px;
        }

        .search-bar input {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: 12px;
            padding: 11px 38px 11px 38px;
            font-size: 14px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
            margin: 0;
        }

        .search-bar input:focus {
            border-color: var(--red-500);
            background: #fff;
        }

        .search-ico {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            opacity: .5;
            pointer-events: none;
        }

        .search-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: var(--bg-2);
            color: var(--muted);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
        }

        /* =========================================================
           MODAL & BOTTOM SHEET (ENHANCED FORM DESIGN)
        ========================================================= */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(6, 20, 41, .65);
            z-index: 1000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            backdrop-filter: blur(6px);
        }

        .modal {
            background: var(--surface);
            width: 100%;
            max-width: 480px;
            border-radius: var(--r-xl) var(--r-xl) 0 0;
            padding: 16px 20px calc(20px + env(safe-area-inset-bottom));
            box-shadow: 0 -12px 48px rgba(0, 0, 0, .25);
            animation: sheetUp 0.25s var(--ease);
        }

        @keyframes sheetUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .modal-grip {
            width: 40px;
            height: 4.5px;
            background: var(--line-2);
            border-radius: 99px;
            margin: 0 auto 16px;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .modal-title-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-icon-badge {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: var(--red-100);
            color: var(--red-500);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .modal h3 {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: var(--ink);
        }

        .modal .desc {
            color: var(--muted);
            font-size: 12.5px;
            margin: 2px 0 0;
        }

        /* Form Group Password */
        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ink-2);
            margin-bottom: 6px;
        }

        .pass-input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .pass-input-box input {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            padding: 12px 42px 12px 38px;
            font-size: 14.5px;
            font-family: inherit;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .pass-input-box input:focus {
            border-color: var(--red-500);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(200, 16, 46, .12);
        }

        .field-ico {
            position: absolute;
            left: 12px;
            font-size: 16px;
            opacity: .5;
            pointer-events: none;
        }

        .eye-toggle {
            position: absolute;
            right: 8px;
            background: none;
            border: 0;
            padding: 6px 8px;
            cursor: pointer;
            font-size: 16px;
            opacity: .6;
            border-radius: 6px;
        }

        .eye-toggle:active {
            opacity: 1;
            background: var(--bg-2);
        }

        /* Checklist Password */
        .pass-checklist {
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            border-radius: var(--r-sm);
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .chk-item {
            font-size: 12px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .chk-item.valid {
            color: var(--green);
            font-weight: 700;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .modal-actions button {
            flex: 1;
            border: 0;
            border-radius: var(--r-sm);
            padding: 14px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-actions .cancel {
            background: var(--bg-2);
            color: var(--ink-2);
        }

        .modal-actions .ok-red {
            background: var(--red-grad);
            color: #fff;
            box-shadow: 0 4px 14px rgba(200, 16, 46, .28);
        }

        .modal-actions .ok-green {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(5, 150, 105, .28);
        }

        .modal-actions button:disabled {
            opacity: .5;
            pointer-events: none;
        }

        /* ---- Foto Selesai Pemasangan ---- */
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 4px 0 8px;
        }

        .photo-thumb {
            position: relative;
            aspect-ratio: 1 / 1;
            border-radius: var(--r-sm);
            overflow: hidden;
            background: var(--bg-2);
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .photo-del {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            border: 0;
            border-radius: 50%;
            background: rgba(15, 23, 42, .72);
            color: #fff;
            font-size: 12px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-add {
            aspect-ratio: 1 / 1;
            border: 1.5px dashed var(--line);
            border-radius: var(--r-sm);
            background: var(--bg-2);
            color: var(--ink-2);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .photo-add span:first-child {
            font-size: 20px;
        }

        .loading {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--line);
            border-top-color: var(--red-500);
            border-radius: 50%;
            animation: spin .75s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ---- PIN Lock Screen ---- */
        .lock-screen {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: linear-gradient(160deg, #061429 0%, #0b2044 60%, #112b5c 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #fff;
        }

        .lock-logo-wrap {
            background: #fff;
            border-radius: 16px;
            padding: 12px 20px;
            margin-bottom: 22px;
        }

        .lock-logo-wrap img {
            height: 42px;
            display: block;
        }

        .lock-title {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 900;
        }

        .lock-sub {
            margin: 0 0 26px;
            font-size: 13px;
            color: rgba(255, 255, 255, .6);
        }

        .pin-dots {
            display: flex;
            gap: 14px;
            margin-bottom: 18px;
        }

        .pin-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .45);
        }

        .pin-dot.filled {
            background: #c8102e;
            border-color: #c8102e;
        }

        .pin-error {
            color: #ffb3bc;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
            min-height: 18px;
        }

        .pin-pad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            width: 100%;
            max-width: 340px;
            margin-bottom: 22px;
        }

        .pin-key {
            width: 100%;
            height: 64px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            cursor: pointer;
        }

        .pin-key:active {
            background: rgba(255, 255, 255, .18);
        }

        .pin-key-back {
            font-size: 18px;
        }

        .pin-key-bio {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .lock-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            width: 100%;
            max-width: 340px;
        }

        .lock-actions .sec-back {
            margin-top: 0;
        }

        .sec-back-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .lock-link {
            background: none;
            border: 0;
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
        }

        .pass-input {
            width: 100%;
            border: 1.5px solid var(--line);
            border-radius: var(--r-sm);
            padding: 12px 14px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 8px;
            background: var(--surface-2);
            color: var(--ink);
            outline: none;
            font-family: inherit;
        }

        .pass-input:focus {
            border-color: var(--red-500);
        }

        /* ===== Ganti Password form ===== */
        .pass-form {
            width: 100%;
            max-width: 340px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .pass-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pass-label {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, .75);
            padding-left: 2px;
        }

        .pass-input-text {
            text-align: left;
            letter-spacing: normal;
            background: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .pass-input-text::placeholder {
            color: rgba(255, 255, 255, .4);
        }

        .pass-input-text:focus {
            border-color: rgba(255, 255, 255, .55);
            background: rgba(255, 255, 255, .14);
        }

        .pass-input-wrap {
            position: relative;
        }

        .pass-input-wrap .pass-input {
            padding-right: 46px;
        }

        .pass-eye {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            font-size: 17px;
            cursor: pointer;
            border-radius: 8px;
        }

        .pass-eye:active {
            background: rgba(255, 255, 255, .12);
        }

        .bio-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 14px;
            padding: 14px 16px;
        }

        .bio-label {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        .bio-switch {
            width: 52px;
            height: 30px;
            border-radius: 99px;
            border: 0;
            background: rgba(255, 255, 255, .2);
            position: relative;
            cursor: pointer;
            transition: background .2s;
        }

        .bio-switch.on {
            background: #10b981;
        }

        .bio-knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #fff;
            transition: transform .2s;
        }

        .bio-switch.on .bio-knob {
            transform: translateX(22px);
        }

        /* ===== Security (Keamanan) list ===== */
        .sec-list {
            width: 100%;
            max-width: 340px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sec-item {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 15px 16px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
            transition: background .15s;
        }

        .sec-item:active {
            background: rgba(255, 255, 255, .16);
        }

        .sec-item-static {
            cursor: default;
        }

        .sec-ico {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .1);
            font-size: 17px;
            color: #fff;
        }

        .sec-label {
            flex: 1 1 auto;
        }

        .sec-chev {
            color: rgba(255, 255, 255, .5);
            font-size: 22px;
            line-height: 1;
            font-weight: 400;
        }

        .sec-back {
            margin-top: 6px;
            width: 100%;
            padding: 13px 16px;
            border-radius: 14px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .22);
            color: rgba(255, 255, 255, .85);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .sec-back:active {
            background: rgba(255, 255, 255, .1);
        }

        .pin-pad-modal {
            grid-template-columns: repeat(3, 64px);
            gap: 10px;
            justify-content: center;
            margin-bottom: 14px;
        }

        .pin-key-modal {
            width: 64px;
            height: 52px;
            font-size: 20px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            color: var(--ink);
        }

        .pin-key-modal:active {
            background: var(--navy-100);
        }
</style>
