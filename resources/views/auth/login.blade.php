@extends('layouts.app')

@section('content')
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">Авторизация</h1>
                <p class="login-subtitle">Введите учетные данные для входа в систему</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        class="form-input @error('email') is-error @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    @error('email')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Пароль</label>
                    <input
                        id="password"
                        type="password"
                        class="form-input @error('password') is-error @enderror"
                        name="password"
                        required
                    >
                    @error('password')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Запомнить меня</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Забыли пароль?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-btn">Войти</button>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .login-container {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px;
            background: #f5f5f5;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #d0d0d0;
            padding: 32px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e0e0;
        }

        .login-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }

        .login-subtitle {
            font-size: 13px;
            color: #666666;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #333333;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 1px solid #cccccc;
            background: #ffffff;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #0066cc;
        }

        .form-input.is-error {
            border-color: #cc0000;
        }

        .form-error {
            display: block;
            font-size: 12px;
            color: #cc0000;
            margin-top: 4px;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #333333;
        }

        .checkbox-label input {
            width: 14px;
            height: 14px;
            margin: 0;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            color: #0066cc;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            background: #0066cc;
            color: #ffffff;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
        }

        .login-btn:hover {
            background: #0052a3;
        }
    </style>
@endpush
