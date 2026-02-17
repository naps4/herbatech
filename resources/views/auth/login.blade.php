@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="login-page" style="min-height: 100vh;">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/') }}"><b>CPB</b>Management</a>
        </div>
        
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>
                
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Password" required autocomplete="current-password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">Remember Me</label>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                
                <p class="mb-1">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">I forgot my password</a>
                    @endif
                </p>
                
                @if(config('app.debug'))
                <div class="mt-3">
                    <p class="mb-1"><strong>Demo Accounts:</strong></p>
                    <div class="small">
                        <div>superadmin@cpb.com / password</div>
                        <div>rnd@cpb.com / password</div>
                        <div>qa@cpb.com / password</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection