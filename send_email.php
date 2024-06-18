<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the submitted data
  $to = $_POST["to"];
  $subject = $_POST["subject"];
  $message = $_POST["message"];
  $attachment = $_FILES["attachment"];
  $signature = $_POST["signature"]; // Get signature from the form

  // Generate a unique boundary string
  $boundary = "Boundary" . md5(time());

  // Define the email headers
  $headers = [
    "From: Caroline Muthoni Kaburu <cmkaburu@strathmore.edu>",
    'Sender: cmkaburu@strathmore.edu',
    "Reply-To: Orina Jared Collins <orina.jared@strathmore.edu>",
    "Content-Type: multipart/mixed; boundary={$boundary}"
  ];

  // Implode the headers array into a string
  $headers_str = implode("\n", $headers);

  // Create the email body
  $body = "--{$boundary}\n";
  $body.= "Content-Type: text/plain; charset=UTF-8\n";
  $body.= "Content-Transfer-Encoding: 8bit\n\n";
  $body.= $message . "\n\n";

  // Add the signature from the form with quoted-printable encoding
  $body.= "--{$boundary}\n";
  $body.= "Content-Type: text/html; charset=UTF-8\n";
  $body .= "Content-Transfer-Encoding: 8bit\n\n";
  $body.= $signature . "\n\n";


  // Add the attachment to the email body (if uploaded)
  if ($attachment["error"] == UPLOAD_ERR_OK) {
    $file_name = $attachment["name"];
    $file_tmp = $attachment["tmp_name"];
    $file_size = $attachment["size"];
    $file_type = $attachment["type"];
    $body.= "--{$boundary}\n";
    $body.= "Content-Type: {$file_type}; name={$file_name}\n";
    $body.= "Content-Transfer-Encoding: base64\n";
    $body.= "Content-Disposition: attachment; filename={$file_name}\n\n";
    $body.= chunk_split(base64_encode(file_get_contents($file_tmp))) . "\n";
    $body.= "--{$boundary}--";
  }

  

  // Send the email
  if (mail($to, $subject, $body, $headers_str)) {
    echo "Email sent successfully!";
  } else {
    echo "Error sending email!";
  }
}
?>
