<x-mail::message>
# Hello

    You are receiving this email because we received a password reset request for your account.


    Token: {{$code}}

        If you did not request a password reset. Please follow these steps:
        1. Reset your password.
        2. Review your security info.
        3. Learn how to make your account more secure.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
