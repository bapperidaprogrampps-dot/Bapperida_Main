<div>
    @php
        $breadcrumbs = [
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Data Dokumen Publik', 'url' => route('admin.dokumenpublik.index')],
            ['name' => $isEdit ? 'Edit Dokumen Publik' : 'Tambah Dokumen Publik', 'url' => '#'],
        ];
    @endphp
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="my-4">
        <div>
            <!-- Alert Messages -->
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Form Card -->
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <!-- Bidang -->
                    <div class="mb-3">
                        <label for="bidang" class="form-label">
                            Bidang <span class="text-danger">*</span>
                        </label>
                        <select id="bidang" class="form-select @error('fkidBidang') is-invalid @enderror"
                            wire:model="fkidBidang">
                            <option value="">Pilih Bidang</option>
                            @foreach ($dataBidang as $bidang)
                                <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                            @endforeach
                        </select>
                        @error('fkidBidang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Dokumen -->
                    <div class="mb-3">
                        <label for="namaDokumen" class="form-label">
                            Nama Dokumen <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="namaDokumen"
                            class="form-control @error('namaDokumen') is-invalid @enderror" wire:model="namaDokumen"
                            placeholder="Masukkan nama dokumen">
                        @error('namaDokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea id="deskripsi" class="form-control @error('deskripsiDokumen') is-invalid @enderror"
                            wire:model="deskripsiDokumen" rows="4" placeholder="Masukkan deskripsi dokumen"></textarea>
                        @error('deskripsiDokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cover / Thumbnail Dokumen -->
                    <div class="mb-4">
                        <label for="cover" class="form-label">
                            Cover / Thumbnail Dokumen <span class="text-muted fw-normal">(Opsional - untuk pratinjau dokumen)</span>
                        </label>

                        <!-- Existing Cover Preview (Edit Mode) -->
                        @if ($existingCover && !$cover)
                            <div class="card border shadow-sm p-3 mb-3 bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ Storage::url($existingCover) }}" alt="Cover saat ini"
                                        class="rounded border shadow-sm" style="width: 100px; height: 130px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="badge bg-primary mb-1">Cover Saat Ini</div>
                                        <p class="mb-2 small text-muted">Cover ini saat ini digunakan sebagai gambar pratinjau dokumen publik.</p>
                                        <button type="button" wire:click="removeExistingCover" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i> Hapus Cover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <input type="file" id="cover" class="form-control @error('cover') is-invalid @enderror"
                            wire:model="cover" accept="image/jpeg,image/png,image/jpg,image/webp">

                        <div class="form-text">
                            Format gambar: JPG, JPEG, PNG, atau WEBP. Maksimal 5MB. Cover akan ditampilkan sebagai gambar sampul/preview dokumen.
                        </div>

                        @error('cover')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <!-- Upload Progress for Cover -->
                        <div wire:loading wire:target="cover" class="mt-2">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                                    style="width: 100%">
                                    Mengupload Cover...
                                </div>
                            </div>
                        </div>

                        <!-- Preview cover yang baru diupload -->
                        @if ($cover)
                            <div class="card border-success mt-3 p-3 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $cover->temporaryUrl() }}" alt="Preview Cover Baru"
                                            class="rounded border shadow-sm" style="width: 100px; height: 130px; object-fit: cover;">
                                        <div>
                                            <div class="badge bg-success mb-1">Preview Cover Baru</div>
                                            <p class="mb-1 fw-bold">{{ $cover->getClientOriginalName() }}</p>
                                            <small class="text-muted">{{ number_format($cover->getSize() / 1024, 2) }} KB</small>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="resetCoverUpload" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-circle me-1"></i> Batal Pilih
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- File Dokumen Upload -->
                    <div class="mb-4">
                        <label for="file" class="form-label">
                            File Dokumen
                            @if (!$isEdit)
                                <span class="text-danger">*</span>
                            @endif
                        </label>

                        @if ($existingFile && !$file)
                            <div class="alert alert-info d-flex align-items-center mb-2">
                                <i class="bi bi-file-earmark-pdf text-danger me-2" style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <strong>{{ $existingFile['name'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ strtoupper($existingFile['type']) }} • {{ $existingFile['size'] ?? '-' }}
                                    </small>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-2">
                                Upload file baru untuk mengganti file dokumen yang ada
                            </small>
                        @endif

                        <input type="file" id="file" class="form-control @error('file') is-invalid @enderror"
                            wire:model="file" accept=".pdf">

                        <div class="form-text">
                            Format dokumen: PDF, Maksimal 20MB.
                        </div>

                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <!-- Upload Progress for Document File -->
                        <div wire:loading wire:target="file" class="mt-2">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 100%">
                                    Mengupload File Dokumen...
                                </div>
                            </div>
                        </div>

                        <!-- Preview file yang baru diupload -->
                        @if ($file)
                            <div class="alert alert-success d-flex align-items-center mt-2">
                                <i class="bi bi-check-circle me-2"></i>
                                <div>
                                    <strong>File siap diupload:</strong> {{ $file->getClientOriginalName() }}
                                    <br>
                                    <small>{{ number_format($file->getSize() / 1024, 2) }} KB</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a wire:navigate href="{{ route('admin.dokumenpublik.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="card border-info mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle text-info"></i> Informasi
                    </h6>
                    <ul class="mb-0 small">
                        <li>Pastikan file yang diupload adalah dokumen resmi</li>
                        <li>Ukuran file maksimal 20MB</li>
                        <li>Format yang didukung: PDF, Word (doc/docx), Excel (xls/xlsx), PowerPoint (ppt/pptx)</li>
                        <li>Dokumen akan tersedia untuk publik setelah disimpan</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
