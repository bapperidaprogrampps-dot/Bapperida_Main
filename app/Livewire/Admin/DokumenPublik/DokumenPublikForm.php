<?php

namespace App\Livewire\Admin\DokumenPublik;

use App\Models\Bidang;
use App\Models\DokumenPublik;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class DokumenPublikForm extends Component
{
    use WithFileUploads;

    public $dokumenId;
    public $fkidBidang;
    public $namaDokumen;
    public $deskripsiDokumen;
    public $file;
    public $existingFile;
    public $cover;
    public $existingCover;
    public $removeCover = false;

    public $isEdit = false;

    protected function rules()
    {
        $fileRule = $this->isEdit ? 'nullable' : 'required';

        return [
            'fkidBidang' => 'required|exists:tbl_bidang,id',
            'namaDokumen' => 'required|string|max:255',
            'deskripsiDokumen' => 'required|string',
            'file' => $fileRule . '|file|mimes:pdf|max:22240', // max 20MB
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
        ];
    }

    protected $messages = [
        'fkidBidang.required' => 'Bidang harus dipilih',
        'namaDokumen.required' => 'Nama dokumen harus diisi',
        'deskripsiDokumen.required' => 'Deskripsi harus diisi',
        'file.required' => 'File dokumen harus diupload',
        'file.mimes' => 'File harus berformat PDF',
        'file.max' => 'Ukuran file dokumen maksimal 20MB',
        'cover.image' => 'Cover harus berupa file gambar',
        'cover.mimes' => 'Format cover harus: JPG, JPEG, PNG, atau WEBP',
        'cover.max' => 'Ukuran cover maksimal 5MB',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->isEdit = true;
            $this->dokumenId = $id;
            $dokumen = DokumenPublik::findOrFail($id);

            $this->fkidBidang = $dokumen->fkid_bidang ?? null;
            $this->namaDokumen = $dokumen->nama_dokumen;
            $this->deskripsiDokumen = $dokumen->deskripsi_dokumen;
            $this->existingCover = $dokumen->thumbnail_path;
            $this->existingFile = [
                'name' => $dokumen->file_name,
                'type' => $dokumen->file_type,
                'size' => $dokumen->formatted_size,
                'path' => $dokumen->file_path
            ];
        }
    }

    public function removeExistingCover()
    {
        $this->cover = null;
        $this->existingCover = null;
        $this->removeCover = true;
    }

    public function resetCoverUpload()
    {
        $this->cover = null;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEdit) {
                $this->update();
            } else {
                $this->create();
            }

            session()->flash('success', 'Dokumen berhasil ' . ($this->isEdit ? 'diperbarui' : 'ditambahkan') . '!');
            return redirect()->route('admin.dokumenpublik.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function create()
    {
        $fileData = $this->uploadFile();
        $coverPath = null;

        if ($this->cover) {
            $coverData = $this->uploadCover();
            $coverPath = $coverData['path'];
        }

        DokumenPublik::create([
            'fkid_bidang' => $this->fkidBidang,
            'nama_dokumen' => $this->namaDokumen,
            'deskripsi_dokumen' => $this->deskripsiDokumen,
            'file_path' => $fileData['path'],
            'file_name' => $fileData['name'],
            'file_type' => $fileData['type'],
            'file_size' => $fileData['size'],
            'thumbnail_path' => $coverPath,
        ]);
    }

    private function update()
    {
        $dokumen = DokumenPublik::findOrFail($this->dokumenId);

        $data = [
            'fkid_bidang' => $this->fkidBidang,
            'nama_dokumen' => $this->namaDokumen,
            'deskripsi_dokumen' => $this->deskripsiDokumen,
        ];

        // Jika ada file dokumen baru diupload
        if ($this->file) {
            if (Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }

            $fileData = $this->uploadFile();
            $data['file_path'] = $fileData['path'];
            $data['file_name'] = $fileData['name'];
            $data['file_type'] = $fileData['type'];
            $data['file_size'] = $fileData['size'];
        }

        // Handle update cover / thumbnail
        if ($this->cover) {
            if ($dokumen->thumbnail_path && Storage::disk('public')->exists($dokumen->thumbnail_path)) {
                Storage::disk('public')->delete($dokumen->thumbnail_path);
            }

            $coverData = $this->uploadCover();
            $data['thumbnail_path'] = $coverData['path'];
        } elseif ($this->removeCover) {
            if ($dokumen->thumbnail_path && Storage::disk('public')->exists($dokumen->thumbnail_path)) {
                Storage::disk('public')->delete($dokumen->thumbnail_path);
            }
            $data['thumbnail_path'] = null;
        }

        $dokumen->update($data);
    }

    private function uploadFile()
    {
        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->getClientOriginalExtension();
        $fileSize = $this->file->getSize();

        // Generate unique filename
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

        // Store file
        $path = $this->file->storeAs('dokumen-publik', $fileName, 'public');

        return [
            'path' => $path,
            'name' => $originalName,
            'type' => $extension,
            'size' => $fileSize,
        ];
    }

    private function uploadCover()
    {
        $extension = $this->cover->getClientOriginalExtension();
        $coverName = 'cover_' . Str::slug($this->namaDokumen) . '_' . time() . '.' . $extension;

        // Store cover file in public storage
        $path = $this->cover->storeAs('dokumen-publik/covers', $coverName, 'public');

        return [
            'path' => $path,
            'name' => $coverName,
        ];
    }

    #[Layout('components.layouts.admin', ['title' => 'Admin | Upload Dokumen', 'pageTitle' => 'Upload Dokumen'])]
    public function render()
    {
        $dataBidang = Bidang::all();
        return view('livewire.admin.dokumen-publik.dokumen-publik-form', [
            'dataBidang' => $dataBidang
        ]);
    }
}
