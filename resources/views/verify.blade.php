<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تفعيل الحساب</title>
</head>
<body>
<h2>مرحباً {{ $user->name }}</h2>
<p>شكراً لتسجيلك. اضغط على الزر أدناه لتفعيل بريدك الإلكتروني:</p>
<p>
    <a href="{{ $verificationUrl }}" style="background:#28a745;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">
        تفعيل البريد الإلكتروني
    </a>
</p>
<p>أو انسخ الرابط التالي في المتصفح:</p>
<p>{{ $verificationUrl }}</p>
</body>
</html>
