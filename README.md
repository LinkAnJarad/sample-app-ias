## Simple Pentesting App

### Run steps with Docker:
- `git clone https://github.com/LinkAnJarad/sample-app-ias`
- `cd path_to_project`
- `docker compose up --build`

If there are errors when signing up, run `php artisan migrate` on `pentest_backend` from Docker.
Note that the email verification uses Mailtrap, which doesn't actually send the email verification link, but just sends the verification email message to LinkAnJarad's Mailtrap account.
