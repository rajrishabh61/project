
this is my sesssion.php code
<?php

namespace App\Core\Session;

class SessionManager
{
    public static function startSecureSession()
    {
        // Set session cookie attributes for security
        session_set_cookie_params([
            'lifetime' => 3600, // Session timeout in seconds (adjust as needed)
            'path' => '/',
            'domain' => 'localhost',
            'secure' => true,    // Send cookies only over HTTPS
            'httponly' => true   // Prevent JavaScript access to cookies
        ]);

        // Start a secure session after setting cookie parameters
        session_start();

        // Regenerate the session ID to prevent session fixation
        self::regenerateSessionId();
    }

    public static function regenerateSessionId()
    {
        // Regenerate the session ID to prevent session fixation
        session_regenerate_id(true);
    }
}


ithis is my init.php code
<?php
require_once 'app\core\session\session.php';
require_once 'app\core\config\config.php';
require_once 'app\core\controller\controller.php';
require_once 'app\core\database\database.php';
require_once 'app\core\route\app.php';
require_once 'app\core\csrf\CSRFHelper.php';
use App\Core\Session\SessionManager;
SessionManager::startSecureSession();

?>
this is index.php
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'app/init.php';
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Route\App;
$app = new App();
?>
this is csrf token page
<?php
namespace App\Core\Csrf;
class CSRFHelper {
    public static function generateCsrfToken() {
        // Check if a CSRF token already exists in the session
        if (!isset($_SESSION['csrf_token'])) {
            // If not, generate a new one
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }
}



    
?>
    this is ajax
  //Sigin Code
  $(document).on("click", "#sinCon", function (e) {
    e.preventDefault(); // Prevent default form submit
    // Get the input value
    var csrf_token = $(".csrf_token").val();
    var email = $("#email").val();
    var password = $("#password").val();
    var error = false;

    // Regex patterns for email and phone number
    var emailRegex = /^([\w-.]+@([\w-]+.)+[\w-]{2,4})?$/;
    var phoneRegex = /^([0-9]{3}[- .]?){2}[0-9]{4}$/;

    if (password === "") {
      $("#password").next(".error").text("Password is required").show();
      error = true;
    } else if (password.length < 8) {
      $("#password")
        .next(".error")
        .text("Password should be at least 8 characters")
        .show();
      error = true;
    } else {
      $("#password").next(".error").hide();
    }

    if (email === "") {
      $("#email")
        .next(".error")
        .text("Enter your email or mobile phone number")
        .show();
      error = true;
    } else if (emailRegex.test(email) || phoneRegex.test(email)) {
    // Send the data via AJAX
    $.ajax({
      type: "POST",
      url: BASEURL + "signin/login", // Replace with the URL of your PHP script that inserts data
      data: { csrf_token: csrf_token, email: email, password: password },
      beforeSend: function () {
        $("#sinCon").html(
          '<div class="spinner-border text-light s_eC87zS" role="status"></div>'
        );
        $("#sinCon").addClass("a_uB1q1E");
        $("#sinCon").prop("disabled", true);
      },
      success: function (data) {
        console.log(data);
        $("#sinCon").removeClass("a_uB1q1E");
        $("#sinCon").prop("disabled", false);
        $("#sinCon").html("Continue");

        var data = JSON.parse(data);
        if (data.success === "verified") {
          // Handle success response
          window.location.href = BASEURL;
          // console.log(data); // Log the response to the console
        } else if (data.error) {
          $(".e_zEZTIS").html('<p id="error_message">' + data.error + "</p>");
        }
      },
      error: function (xhr, status, error) {
        // Handle error response
        //console.log(xhr.responseText); // Log the error message to the console
        $(".e_zEZTIS").html('<p id="error_message">' + error + "</p>"); // Display the error message to the user
      },
    });
    } else {
      // Invalid input
      // Show an error message or do something else
      $("#email").next(".error").text("Invalid Credential").show();
      error = true;
    }
  });
this is controller
    //<<>>[[ Login Data Passing To model ]]<<>>/
    public function login()
    {
        // Verify the CSRF token
        if (isset($_POST['csrf_token']) && CSRFHelper::verifyCsrfToken($_POST['csrf_token'])) {
            $signin = new User();
            $username = trim($_POST["email"]);
            $password = trim($_POST['password']);
            $result = $signin->login($username, $password);

            if ($result) {
                echo json_encode(["success" => $_SESSION['success']]);
            } else {
                echo json_encode(["error" => $_SESSION['error']]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Invalid CSRF token"]);
        }
    }
html inside form
<input type="hidden" name="csrf_token" class="csrf_token" value="5b1ac0ec034adb14bd5b10ad6cc17d8d614363893818d902409d3a4cd34965da">
