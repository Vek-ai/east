require_once 'EmailTemplates.php';

$email = new EmailTemplates();

$email->sendWelcomeEmail("user@example.com", "Jane Doe");

$email->sendPasswordResetEmail("user@example.com", "https://yourdomain.com/reset?token=12345");

$email->sendBookingConfirmation("user@example.com", "Jane", "Date: Aug 5, Time: 2:00PM, Service: Consultation");

$email->sendNotification("admin@example.com", "System Alert", "A new user has registered.");
