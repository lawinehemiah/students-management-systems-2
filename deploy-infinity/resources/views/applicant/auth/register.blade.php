<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | St. Maximiliancolbe College</title>

<link rel="shortcut icon" href="{{ asset('assets/images/logo.webp') }}">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<style>
    :root {
        /* Rangi za Chuo */
        --college-blue: #1e3a8a; 
        --college-red: #dc2626;
        --bg-light: #f3f4f6;
    }

    body {
        background: var(--bg-light);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 10px;
    }

    .registration-container {
        width: 100%;
        max-width: 420px;
        margin: auto;
    }

    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden; /* Muhimu ili rangi ya header isivuje pembeni */
    }

    /* Header Mpya yenye Blue na Red */
    .custom-header {
        background-color: var(--college-blue);
        padding: 20px 15px;
        text-align: center;
        border-bottom: 5px solid var(--college-red); /* Mstari wa Red chini ya Header */
        color: white;
    }

    .custom-header img {
        width: 65px;
        height: auto;
        margin-bottom: 10px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .custom-header h5 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.4;
    }

    .custom-header span {
        display: block;
        font-size: 0.7rem;
        opacity: 0.9;
        margin-top: 5px;
        font-weight: 400;
        letter-spacing: 1px;
    }

    .card-body {
        padding: 25px;
        background: white;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #4b5563;
        margin-bottom: 4px;
    }

    .form-control {
        padding: 10px 12px;
        font-size: 0.85rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--college-blue);
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
    }

    .btn-submit {
        background-color: var(--college-blue);
        color: white;
        border: none;
        padding: 12px;
        font-weight: 700;
        border-radius: 6px;
        width: 100%;
        margin-top: 10px;
        transition: 0.3s;
    }

    .btn-submit:hover {
        background-color: #162d6d;
        color: white;
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    .login-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 0.85rem;
    }

    .login-footer a {
        color: var(--college-red);
        text-decoration: none;
        font-weight: 700;
    }

    /* Kurekebisha kwa ajili ya simu */
    @media (max-width: 400px) {
        .custom-header h5 { font-size: 0.95rem; }
        .card-body { padding: 20px; }
        .row .col-6 { width: 100%; margin-bottom: 0; }
        .row .col-6:first-child { margin-bottom: 15px; }
    }
</style>
</head>

<body>

<div class="registration-container">
    <div class="card">
        <!-- Header yenye Logo na Jina ndani -->
        <div class="custom-header">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="College Logo">
            <h5>St. Maximiliancolbe College</h5>
            <span>APPLICANT REGISTRATION</span>
        </div>

        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger p-2 mb-3">
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('applicant.register.submit') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">First Name</label>
                        <input class="form-control" name="first_name" placeholder="" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Last Name</label>
                        <input class="form-control" name="last_name" placeholder="" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="example@mail.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" name="phone" placeholder="0712XXXXXX">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label small" for="terms">
                        I accept the college terms and conditions
                    </label>
                </div>

                <button type="submit" class="btn-submit shadow-sm">
                    CREATE ACCOUNT
                </button>

                <div class="login-footer text-muted">
                    Already have an account? <br>
                    <a href="{{ route('applicant.login') }}">Login to Portal</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>