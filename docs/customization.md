# Customization

You can customize the appearance and behavior of the Fastfony Identity-Bundle in several ways.

## 1. Bundle Configuration

You can customize the bundle's configuration by creating or modifying the `config/packages/fastfony_identity.yaml` file in your Symfony project.
Here is an example configuration file with possible options:

```yaml
fastfony_identity:
    user:
        class: App\Entity\User  # Your own User entity class (if you don't want to use the default one)
        require_email_verification: true  # Require email verification for new users (default: false)
    role:
        class: App\Entity\Role  # Your own Role entity class (if you don't want to use the default one)
    group:
        class: App\Entity\Group  # Your own Group entity class (if you don't want to use the default one)
    registration:
        enabled: true  # Enable or disable user registration (default: false)
    login:
        default_method: login_link  # Default route and method to login (default: form_login)
    login_link:
        enabled: false  # Enable or disable login via email link (default: true)
        email_subject: 'Your login link' # Subject of the email sent with the login link (text or translation key)
        limit_max_ask_by_minute: 3  # Maximum number of login link requests in one minute
    request_password:
        email_subject: 'Reset your password' # Subject of the email sent with the password reset link (text or translation key)
        email_content: 'Click on the button below to reset your password.' # Content of the email sent with the password reset link (text or translation key)
        email_action_text: 'Reset my password' # Text of the action button in the email sent with the password reset link (text or translation key)
        lifetime: 900  # Lifetime of the password reset token in seconds (default: 900 seconds = 15 minutes)
        redirect_route: app_homepage  # Route to redirect to after a successful password reset (default: app_homepage)
```

Other configuration options are always available:

Thanks to the [Symfony Security component](https://symfony.com/doc/current/security.html) you can customize security parameters in the `config/packages/security.yaml` file. Here is a non-exhaustive list of options you can set there:
* [login link max uses and lifetime](https://symfony.com/doc/current/security/login_link.html#configure-a-maximum-use-of-a-link)
* [lifetime and activation remember me](https://symfony.com/doc/current/security/remember_me.html)
* [limiting login attemps](https://symfony.com/doc/current/security.html#limiting-login-attempts)

Thanks to the [Symfony Mailer component](https://symfony.com/doc/current/mailer.html) you can also customize the email sending configuration in the `config/packages/mailer.yaml` file:
* [sending method](https://symfony.com/doc/current/mailer.html#transport-setup)
* [email sender](https://symfony.com/doc/current/mailer.html#configuring-emails-globally)

## 2. Override Templates

### Generality

The bundle uses Twig templates for rendering views. You can override these templates by placing your custom versions in the `templates/bundles/FastfonyIdentityBundle/` directory of your Symfony project.

For example, to customize the login page, create a file at `templates/bundles/FastfonyIdentityBundle/security/login.html.twig`.

In each template, you can override specific blocks to change parts of the content without needing to rewrite the entire template.

### Login Page

To customize the login page, create a file at `templates/bundles/FastfonyIdentityBundle/login.html.twig`.

You can override the entire template or extend the default one and modify only specific blocks. Here is an example of extending the default template and just changing the other options login block for remove social login buttons sample:

```twig
{% extends '@!FastfonyIdentity/login.html.twig' %}

{% block login_other_options %}
    {# No other options #}
{% endblock %}
```

### Registration Page

If user registration is enabled in the bundle configuration, you can customize the registration page by creating a file at `templates/bundles/FastfonyIdentityBundle/register.html.twig`.

You can override the entire template or extend the default one and modify only specific blocks. Here is an example of extending the default template and just changing the background image url:

```twig
{% extends '@!FastfonyIdentity/register.html.twig' %}

{% block registration_background_image_url %}
    {{ asset('images/custom-background.jpg') }}
{% endblock %}
```

## 3. Login, Registration, and Password Reset e-mails

The bundle provides default email templates for login, registration, and password reset. You can customize these templates by overriding them in the `templates/bundles/FastfonyIdentityBundle/emails/` directory.

For example, to customize the password reset email, create a file at `templates/bundles/FastfonyIdentityBundle/emails/password_reset.html.twig` or to customize the login link email, create a file at `templates/bundles/FastfonyIdentityBundle/email/login_link_email.html.twig`.