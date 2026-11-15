To perform actions when a contact is created, such as sending an email notification, you can utilize Eloquent model events in Laravel. Eloquent provides several events, including `creating`, `created`, `updating`, `updated`, and so on. For your case, you'll want to use the `created` event.

Here’s a step-by-step guide on how to implement this:

### Step 1: Create an Observer

First, you can create an observer class that will handle the logic when a contact is created. You can generate an observer using the Artisan command:

```bash
php artisan make:observer ContactObserver --model=Contact
```

This command will create a new observer class named `ContactObserver` in the `app/Observers` directory and automatically link it to the `Contact` model.

### Step 2: Implement the Observer Logic

Open the `ContactObserver` class and implement the `created` method to handle the action you want to perform when a contact is created. For example, you can send an email notification.

Here’s how the `ContactObserver` might look:

```php
namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactNotification; // Ensure you create this Mailable class

class ContactObserver
{
    public function created(Contact $contact)
    {
        // Send email notification when a contact is created
        Mail::to('admin@example.com')->send(new ContactNotification($contact));
    }
}
```

### Step 3: Create a Mailable

You will need to create a Mailable class to define the email content. You can create it using the following command:

```bash
php artisan make:mail ContactNotification
```

In the `ContactNotification` class, you can define the email content. Here’s an example:

```php
namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        return $this->subject('New Contact Submission')
                    ->view('emails.contact-notification'); // Create a view for the email content
    }
}
```

### Step 4: Create the Email View

You need to create a Blade view for the email content. Create a new file at `resources/views/emails/contact-notification.blade.php` and add your email template. Here’s a simple example:

```blade
<!-- resources/views/emails/contact-notification.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>New Contact Submission</title>
</head>
<body>
    <h1>New Contact Submission</h1>
    <p><strong>Name:</strong> {{ $contact->name }}</p>
    <p><strong>Email:</strong> {{ $contact->email }}</p>
    <p><strong>Message:</strong></p>
    <p>{{ $contact->message }}</p>
    <p><strong>Contact Date:</strong> {{ $contact->contact_date }}</p>
    <p><strong>Contact Time:</strong> {{ $contact->contact_time }}</p>
</body>
</html>
```

### Step 5: Register the Observer

Finally, you need to register the observer in your `AppServiceProvider` or a dedicated service provider. Open `App\Providers\AppServiceProvider.php` and add the registration in the `boot` method:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contact;
use App\Observers\ContactObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Contact::observe(ContactObserver::class);
    }

    public function register()
    {
        //
    }
}
```

### Summary
Now, whenever a new `Contact` is created, the `created` event will be fired, and the `created` method in your `ContactObserver` will execute, sending an email notification to the specified address.

### Additional Considerations
- **Queueing Emails**: If you anticipate a high volume of contacts, consider queueing the email sending for improved performance. You can use Laravel’s queue functionality by adding `use Illuminate\Contracts\Queue\ShouldQueue;` to your `ContactNotification` class and implementing the `ShouldQueue` interface.
- **Validation**: Ensure you have validation in place for the contact form to avoid sending incomplete or invalid data.