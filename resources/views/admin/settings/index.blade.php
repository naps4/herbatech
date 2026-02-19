@extends('layouts.app')

@section('title', 'Site Settings')
@section('page-title', '')

@section('breadcrumb')

@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">General Settings</h3>
            </div>
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <!-- App Name -->
                    <div class="form-group">
                        <label for="app_name">Nama Aplikasi</label>
                        <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                               id="app_name" name="app_name" 
                               value="{{ old('app_name', $settings['app_name'] ?? 'CPB System') }}" required>
                        @error('app_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- App Logo -->
                    <div class="form-group">
                        <label for="app_logo">Logo Aplikasi</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('app_logo') is-invalid @enderror" 
                                       id="app_logo" name="app_logo" accept="image/*">
                                <label class="custom-file-label" for="app_logo">Pilih file</label>
                            </div>
                        </div>
                        @error('app_logo')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        @if(isset($settings['app_logo']) && $settings['app_logo'])
                            <div class="mt-2">
                                <p class="text-sm text-muted">Preview saat ini:</p>
                                <img src="{{ asset($settings['app_logo']) }}" alt="App Logo" class="img-thumbnail" style="max-height: 80px">
                            </div>
                        @endif
                    </div>

                    <!-- App Favicon -->
                    <div class="form-group">
                        <label for="app_favicon">Favicon</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('app_favicon') is-invalid @enderror" 
                                       id="app_favicon" name="app_favicon" accept="image/*">
                                <label class="custom-file-label" for="app_favicon">Pilih file</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Disarankan ukuran 16x16 atau 32x32 pixel.</small>
                        @error('app_favicon')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        @if(isset($settings['app_favicon']) && $settings['app_favicon'])
                            <div class="mt-2">
                                <p class="text-sm text-muted">Preview saat ini:</p>
                                <img src="{{ asset($settings['app_favicon']) }}" alt="App Favicon" class="img-thumbnail" style="max-height: 32px">
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Custom file input label change
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>
@endpush
