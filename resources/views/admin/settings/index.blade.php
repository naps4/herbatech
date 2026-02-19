@extends('layouts.app')

@section('title', 'Site Settings')
@section('page-title', '')

@section('breadcrumb')

@endsection

@section('content')
<div class="row mt-4"> 
    <div class="col-12">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">
                            <i class="fas fa-cog mr-1"></i> General
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="appearance-tab" data-toggle="pill" href="#appearance" role="tab" aria-controls="appearance" aria-selected="false">
                            <i class="fas fa-paint-brush mr-1"></i> Appearance
                        </a>
                    </li>
                </ul>
            </div>
            
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent" style="min-height: 400px;">
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="app_name">Nama Aplikasi <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-desktop"></i></span>
                                            </div>
                                            <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                                   id="app_name" name="app_name" 
                                                   value="{{ old('app_name', $settings['app_name'] ?? 'CPB System') }}" required>
                                            @error('app_name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="app_description">Deskripsi Aplikasi</label>
                                        <textarea class="form-control" name="app_description" id="app_description" rows="3" placeholder="Masukkan deskripsi singkat aplikasi...">{{ old('app_description', $settings['app_description'] ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="callout callout-info">
                                        <h5><i class="fas fa-info"></i> Info</h5>
                                        <p>Nama aplikasi akan muncul di judul halaman browser dan bagian sidebar dashboard.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <div class="form-group">
                                        <label for="app_logo">Logo Utama</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error('app_logo') is-invalid @enderror" 
                                                       id="app_logo" name="app_logo" accept="image/*">
                                                <label class="custom-file-label" for="app_logo">Pilih file logo</label>
                                            </div>
                                        </div>
                                        <small class="text-muted">Format yang disarankan: PNG transparan.</small>
                                        @error('app_logo')
                                            <span class="text-danger d-block small mt-1">{{ $message }}</span>
                                        @enderror
                                        
                                        <div class="mt-4 text-center p-3 border rounded bg-light" style="min-height: 120px; display: flex; flex-direction: column; justify-content: center;">
                                            <p class="text-sm text-muted mb-2">Preview Logo Saat Ini:</p>
                                            @if(isset($settings['app_logo']) && $settings['app_logo'])
                                                <img src="{{ asset($settings['app_logo']) }}" alt="App Logo" class="img-fluid mx-auto" style="max-height: 80px">
                                            @else
                                                <span class="text-muted italic small">Belum ada logo diunggah</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="app_favicon">Favicon Browser</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input @error('app_favicon') is-invalid @enderror" 
                                                       id="app_favicon" name="app_favicon" accept="image/*">
                                                <label class="custom-file-label" for="app_favicon">Pilih file favicon</label>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block">Ukuran ideal: 32x32 pixel (ICO/PNG).</small>
                                        @error('app_favicon')
                                            <span class="text-danger d-block small mt-1">{{ $message }}</span>
                                        @enderror

                                        <div class="mt-4 text-center p-3 border rounded bg-light" style="min-height: 120px; display: flex; flex-direction: column; justify-content: center;">
                                            <p class="text-sm text-muted mb-2">Preview Favicon Saat Ini:</p>
                                            @if(isset($settings['app_favicon']) && $settings['app_favicon'])
                                                <img src="{{ asset($settings['app_favicon']) }}" alt="App Favicon" class="img-thumbnail mx-auto" style="max-height: 32px">
                                            @else
                                                <span class="text-muted italic small">Belum ada favicon diunggah</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top">
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="reset" class="btn btn-default mr-2">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> Simpan Semua Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Update label input file secara dinamis
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>
@endpush