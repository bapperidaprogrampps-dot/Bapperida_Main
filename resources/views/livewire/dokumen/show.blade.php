<div class="container">

     <div class="banner-crop-full">
        <img src="{{ asset('assets/img/element5.png') }}" alt="Banner Pegawai">
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('dokumenpublik') }}">Daftar Dokumen</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $dokumen->nama_dokumen }}</li>
        </ol>
    </nav>
    <div class="my-4">
        <div class="row g-4 align-items-start">
            @if ($dokumen->thumbnail_path)
                <div class="col-md-3 text-center">
                    <img src="{{ Storage::url($dokumen->thumbnail_path) }}" alt="Cover {{ $dokumen->nama_dokumen }}"
                        class="img-fluid rounded shadow border" style="max-height: 280px; width: auto; object-fit: contain;">
                </div>
            @endif

            <div class="{{ $dokumen->thumbnail_path ? 'col-md-9' : 'col-md-12' }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block m-0">Nama Dokumen</small>
                            <p class="fw-bold fs-5 mb-0">{{ $dokumen->nama_dokumen }}</p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block m-0">Bidang</small>
                            <p class="fw-bold mb-0">{{ $dokumen->bidang->nama_bidang ?? 'Belum ditentukan' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <small class="text-muted d-block m-0">Format & Ukuran</small>
                            <p class="fw-bold mb-0">{{ strtoupper($dokumen->file_type) }} • {{ $dokumen->formatted_size }}</p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block m-0">Tanggal Ditambahkan</small>
                            <p class="fw-bold mb-0">{{ $dokumen->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <small class="text-muted d-block m-0">Deskripsi</small>
                        <p class="text-secondary mb-0">{{ $dokumen->deskripsi_dokumen }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="my-5 card">
            <div class="ratio ratio-4x3" style="min-height: 600px;">
                <iframe src="{{ Storage::url($dokumen->file_path) }}#toolbar=1&navpanes=1&scrollbar=1"
                    type="application/pdf" class="border-0" style="width: 100%; height: 100%;">
                    <p class="p-4">
                        Browser Anda tidak mendukung preview PDF.
                        <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" class="btn btn-primary">
                            Buka di Tab Baru
                        </a>
                    </p>
                </iframe>
            </div>

            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Tidak bisa melihat preview?
                    <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" class="fw-bold">
                        Buka di tab baru
                    </a>
                </small>
            </div>
        </div>
    </div>
</div>
