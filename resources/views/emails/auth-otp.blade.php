<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TechCourse Verification Code</title>
    </head>
    <body style="margin:0;padding:24px;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #dbe6f1;border-radius:20px;padding:32px;">
            <div style="font-size:28px;font-weight:800;color:#173f87;margin-bottom:10px;">TechCourse</div>
            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.3;">
                {{ $mode === 'register' ? 'Verify your email address' : 'Verify your login' }}
            </h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#52637a;">
                Hello {{ $name }}, use the code below to {{ $mode === 'register' ? 'activate your account' : 'complete your login' }}.
            </p>
            <div style="margin:20px 0;padding:20px;border-radius:18px;background:#eff6ff;border:1px solid #cfe0fb;text-align:center;">
                <div style="font-size:34px;font-weight:800;letter-spacing:8px;color:#155eef;">{{ $code }}</div>
            </div>
            <p style="margin:0 0 8px;font-size:14px;line-height:1.7;color:#52637a;">
                This code expires in 10 minutes.
            </p>
            <p style="margin:0;font-size:14px;line-height:1.7;color:#52637a;">
                If you did not request this code, please ignore this email.
            </p>
        </div>
    </body>
</html>
