<?php

  /* GOALS & NOTES:
    * ENHANCEMENT - Need to make the validation area dynamic now, considering that the forms are now variable (lines 176, 189, and 214).
    * ENHANCEMENT - Automated data collection.
        * MySQL integration.
        * Google Sheets (Spreadsheet) integration.
        * Data encryption.
    * ENHANCEMENT - Add the capability of sending HTML content messages to visitors (may already work).
    * ENHANCEMENT - Timestamps.

    * BEST PRACTICE - Google Analytics - Users are redirected to the same page, whether or not the outcome is default, success, or failure.  There is currently no way of delineating this behavior when it comes to tracking via Google Analytics.  How do we incorporate this?
    * BEST PRACTICE - Currently using IDs as hooks to style contact forms via CSS. Is this best practice?

    * PERFORMANCE - Would prefer to define many configuration variables locally in the first IF statement to improve page-rendering performance, but not sure how to do that without making the code look complicated and overwhelming.
    * PERFORMANCE - Contemplating using just ucwords() function to replace the titleCase() function.

    * PRESENTATION - Use OOP model to organize preferences, or perhaps XML or EOF for data/HTML sections?
        Example:
          <?php
          $str = <<< XML
          <?xml version="1.0"?>
          <shapes>
            <shape type="circle" radius="2" />
            <shape type="rectangle" length="5" width="2" />
            <shape type="square" length="7" />
          </shapes>

          XML;
          ?>
  */


  // SETTINGS ====================================================================================================
    
    // CONTACT FORM CONFIGURATION:
      $formTitle = 'Contact us today for more information!';
      $successTitle = 'Success!';
      $failureTitle = 'Oops... Something went wrong!';
      
      $inputFields = array(
        // Which fields do you want the contact form to include?
        // FORMAT: array(referenceKey (no spaces), Display Name, fieldType, isRequired?),
        array('name', 'Name', 'text', true),
        array('phone', 'Phone Number', 'text', true),
        array('email', 'Email Address', 'text', false),
        array('source', 'Source', 'text', false),
        array('message', 'How can we help you?', 'textarea', true),
      );
      
      $useResetButton = false;

      // Cues for dynamic recognition of data
      // message = textarea, required, only one.
      // email = 

    //---------------------------------------------------------------

    // BUSINESS INFORMATION CONFIGURATION:
      $businessName = 'Example Business Name';
      $businessWebsite = 'http://example.com';
      $businessFacebook = 'http://www.facebook.com/ExampleURL';
      $businessEmail = 'info@example.com'; // The verification email that the visitor receives, comes from this address.
      
      $canReply = true; // This setting allows the visitor to reply to this email address.
      $enableHTML = true; // This setting enables HTML in the outgoing emails.

    //---------------------------------------------------------------
    
    // EMAIL CONTACT CONFIGURATION:
      $contactEmails = 'info@example.com, boss@example.com'; // The visitor's message is sent to these email addresses.
      $emailSubjectInformation = "$businessName's Contact Form Information"; // Subject line for the email sent to the business.
      $emailSubjectVerification = "$businessName's Contact Form Verification"; // Subject line for the email sent to the visitor.
      
      $emailBodyVerification = "Thank you for contacting $businessName! This is an automatically generated email to verify that we have received your information. A copy of your information is included below. We will contact you as soon as possible to answer your questions or address your concerns. In the meantime, please visit our Facebook ($businessFacebook) and website ($businessWebsite) to see what's new at $businessName! Have a great day!\n\n"; // Body of the email sent to the visitor.

    //---------------------------------------------------------------

    // Dynamic content - this is the content that is written above the form.  This content changes depending on actions from the visitor.  If the visitor is just visiting the page (hasn't submitted the form yet), they will see the default content; if the visitor successfully submitted the form, they will see the success content; if the visitor submitted the form with errors, they will see the failure content.
      
      // Default dynamic content
        $defaultContent = "
          <h2>
            $formTitle
          </h2>";
      

      // Success dynamic content
        $successContent = "
          <h2>
            $successTitle
          </h2>
          <p>
            Your information has been sent! We will contact you as soon as possible. If you provided an email, a verification email has been sent containing the information you submitted.
          </p>
          <p>
            While you wait for us to get back to you regarding your questions, please take a second to check out our <a href=\"$businessFacebook\" target=\"blank\">Facebook</a> to see what's new at $businessName!
          </p>
          <h2>
            Another Question? Don't be shy!
          </h2>";

      
      // Failure dynamic content
        function failureContent($errors='We\'re sorry for the inconvenience. Please try again.') {
          global $failureTitle;
          $failureContent = "
          <h2>
            $failureTitle
          </h2>
          <p>
            We are very sorry, but there seems to be error(s) in the information you've provided below. Please fix these errors and resubmit the form. Thanks!
          </p>
          <h3>
            Error(s):
          </h3>
          <p>
            $errors
          </p>";
          return $failureContent;
        }

    //---------------------------------------------------------------

    // DATABASE CONFIGURATION:
      $host = 'localhost';
      $user = 'exampleuser';
      $pass = 'examplepass';
      $db = 'exampledb';

  //==============================================================================================================


  // Initialization
    $content = '<div id="contact-dynamic">';
    $errorMessage = '';
    $generatedForms = '';
    $requiredFields = array();

  // Contact form generation
    for ($i = 0; $i < sizeof($inputFields); $i++) {
      if ($inputFields[$i][3]) { // If a field is required:
        $inputFields[$i][1] .= '*'; // Append an "*" to the end of a required field name,
        array_push($requiredFields, $inputFields[$i][0]); // And add the required field to the requiredFields array.
      }
      if ($inputFields[$i][2] === 'textarea') { // Code the fields properly in HTML.
        $generatedForms .= "
          <label for=\"{$inputFields[$i][0]}\">{$inputFields[$i][1]}</label>
          <textarea name=\"{$inputFields[$i][0]}\" rows=\"5\" cols=\"20\" /></textarea>
          ";
      } else {
        $generatedForms .= "
          <label for=\"{$inputFields[$i][0]}\">{$inputFields[$i][1]}</label>
          <input name=\"{$inputFields[$i][0]}\" type=\"{$inputFields[$i][2]}\" />
          ";
      }
    }
    $generatedForms .= "
      <input name=\"submit\" value=\"Submit\" id=\"submit\" type=\"submit\" />";
    if ($useResetButton) {
      $generatedForms .= "
      <input name=\"reset\" value=\"Clear\" id=\"reset\" type=\"reset\" />";
    }
    $contactForm = "

      <div id=\"contact-form\">
        <form method=\"post\" action=\"{$_SERVER['PHP_SELF']}\">
          $generatedForms
        </form>
      </div>";


  do { // This do-while(0) loop allows us to break out of its loop with the break command, without terminating the rest of the page's rendering of HTML.

    // Information processing - check to see if the information from the form needs to be processed.
      if(isset($_POST['submit'])) { // Process the contact form.

        // Validation - expected data must exist
          if (sizeof($requiredFields) > 0) { // If there are required fields:
            foreach ($requiredFields as $i) { // Cycle through them,
              if($_POST[$i] == '' || !isset($_POST[$i])) { // If any are blank or unset:
                $errorMessage .= "*The $i field (required) wasn't filled out.<br />"; // Append corresponding error message.
              }
            }
            if (strlen($errorMessage) > 0) { // If there is an error message to be displayed:
              $content .= failureContent($errorMessage); // Add the failure content to the page,
              break; // And break out of do-while(0) loop;
            }
          }

        // Formatting and storing the data from the fields in variables - THIS NEEDS TO BE DYNAMIC.
          $inputName = ucwords((prepareString($_POST['name'])));
          $inputPhone = prepareString($_POST['phone']);
          $inputEmail = strtolower(prepareString($_POST['email']));
          $inputEmail = filter_var($inputEmail, FILTER_SANITIZE_EMAIL);
          $inputSource = titleCase(prepareString($_POST['source']));
          $inputMessage = prepareString($_POST['message']);

        // Validation - Data must adhere to expected characters (REGEX) - NEED TO ADD MORE COMMON SETS OF REGEX VALIDATION.
          $expectedName = '/^[A-Za-z\.\'-]{2,}([\s][A-Za-z\.\'-]{2,})+$/';
          $expectedPhone = '/^(\+?1[\-\. ]?)?\(?[2-9][0-8][0-9][\)\-\. ]?[0-9]{3}[\-\. ]?[0-9]{4}$/';
          $expectedEmail = '/^[\_]*([a-z0-9]+(\.|\_*)?)+@([a-z][a-z0-9\-]+(\.|\-*\.))+[a-z]{2,6}$/';

          // Testing the formats of the inputs - THIS NEEDS TO BE DYNAMIC.
          // FORMAT: regexValidation($input, $expected, $referenceName)
          regexValidation($inputName, $expectedName, 'name');
          regexValidation($inputPhone, $expectedPhone, 'phone number');
          $emailValidation = regexValidation($inputEmail, $expectedEmail, 'email address');
          if($emailValidation && !filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) { // Built-in fall-back check.
            $errorMessage .= '*The email address you entered does not appear to be valid.<br />';
          }
          if (strlen($inputMessage) < 3) {
            $errorMessage .= '*You didn\'t tell us how we can help you.<br />';
          }
          if (strlen($errorMessage) > 0) {
            $content .= failureContent($errorMessage);
            break; // Break out of do-while(0) loop.
          }

        /*// Log data into a database
          if database variables are set
            do { // Will breaking out of this do-while(0) loop break out of both of them?
              $connection = mysql_connect($server, $user, $pass) or break;
              mysql_select_db($db, $connection) or break;
              $query = "INSERT";
              mysql_close($connection);
            } while (0);
        */

        // Prepare email body information text - NEEDS TO BE DYNAMIC
          $emailBodyInformation = "-Form details-\n";
          $emailBodyInformation .= "Name: $inputName\n";
          $emailBodyInformation .= "Phone: $inputPhone\n";
          $emailBodyInformation .= "Email: $inputEmail\n";
          $emailBodyInformation .= "Source: $inputSource\n";
          $emailBodyInformation .= "Message: $inputMessage\n";

        // Prepare email body verification text
          if (strlen($inputEmail) > 0) {
            $emailBodyVerification .= $emailBodyInformation;
          }

        // Construct the headers
          $headers = '';
          if ($enableHTML === true) {
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\n";
          }
          $headersInformation = $headers;
          $headersInformation .= htmlentities("From: $businessName <$businessEmail>\r\n");
          if (strlen($inputEmail) > 0) {
            $headersInformation .= htmlentities("Reply-To: $inputName <$inputEmail>\r\n");
          }
          $headersVerification = $headers;
          $headersVerification .= htmlentities("From: $businessName <$businessEmail>\r\n");
          if ($canReply === true) {
            $headersVerification .= htmlentities("Reply-To: $businessName <$businessEmail>");
          } else {
            $headersVerification .= "Reply-To:";
          }

        // Send information email to business
          $informationSuccess = mail($contactEmails, $emailSubjectInformation, $emailBodyInformation, $headersInformation);

        // Send verification email to visitor
          if (strlen($inputEmail) > 0) {
            mail($inputEmail, $emailSubjectVerification, $emailBodyVerification, $headersVerification);
          }

        // Appends the dynamic content to the $content variable
          if ($informationSuccess) {
            $content .= $successContent;
          } else {
            $content .= failureContent('*E-mail failed to send, please check your email address and try again.');
          }
      } else { // Appends the default content to the $content variable
        $content .= $defaultContent;
      }
  } while (0);

  $content .= "
    </div>
    ";
  $content .= $contactForm;
  echo $content;


  // Helper functions
    function prepareString($string) { // Prepares the $string for processing by the script
      $newString = htmlentities($string);
      $newString = stripslashes($newString);
      $newString = trim($newString);
      $bad = array('content-type','bcc:','to:','cc:','href');
      $newString = str_replace($bad,'',$newString);
      return $newString;
    }

    function titleCase($string) { // Converts $string into title case using multiple rules
      // Remove no_parse content.
        $string_array = preg_split("/(<no_parse>|<\/no_parse>)+/i",$string);
        $newString = '';
        for ($k=0; $k<count($string_array); $k=$k+2) {
          $string = $string_array[$k];
          // If the entire string is upper case, don't perform any title case on it.
            if ($string != strtoupper($string)) {
              // TITLE CASE RULES:
                // 1.) Uppercase the first char in every word.
                  $new = preg_replace("/(^|\s|\'|'|\"|-){1}([a-z]){1}/ie","''.stripslashes('\\1').''.stripslashes(strtoupper('\\2')).''", $string);
                // 2.) Lower case words exempt from title case.
                  // Lowercase all articles, coordinate conjunctions ("and", "or", "nor"), and prepositions regardless of length, when they are other than the first or last word.
                  // Lowercase the "to" in an infinitive." - this rule is of course approximated since it is context sensitive.
                  $matches = array();
                  // Perform recursive matching on the following words.
                  preg_match_all("/(\sof|\sa|\san|\sthe|\sbut|\sor|\snot|\syet|\sat|\son|\sin|\sover|\sabove|\sunder|\sbelow|\sbehind|\snext\sto|\sbeside|\sby|\samoung|\sbetween|\sby|\still|\ssince|\sdurring|\sfor|\sthroughout|\sto|\sand){2}/i",$new ,$matches);
                  for ($i=0; $i<count($matches); $i++) {
                    for ($j=0; $j<count($matches[$i]); $j++) {
                      $new = preg_replace("/(".$matches[$i][$j]."\s)/ise","''.strtolower('\\1').''",$new);
                    }
                  }
                // 3.) Do not allow upper case apostrophes.
                  $new = preg_replace("/(\w'S)/ie","''.strtolower('\\1').''",$new);
                  $new = preg_replace("/(\w'\w)/ie","''.strtolower('\\1').''",$new);
                  $new = preg_replace("/(\W)(of|a|an|the|but|or|not|yet|at|on|in|over|above|under|below|behind|next to| beside|by|amoung|between|by|till|since|durring|for|throughout|to|and)(\W)/ise","'\\1'.strtolower('\\2').'\\3'",$new);
                // 4.) Capitalize first letter in the string always.
                  $new = preg_replace("/(^[a-z]){1}/ie","''.strtoupper('\\1').''", $new);
                // 5.) Replace special cases.
                  // You will add to this as you find case specific problems.
                  $new = preg_replace("/\sin-/i"," In-",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(ph){1}(\s|,|\.|\"|\'|:|!|\?|\*|$){1}/ie","'\\1pH\\3'",$new);
                  $new = preg_replace("/^ph(\s|$)/i","pH ",$new);
                  $new = preg_replace("/(\s)ph($)/i"," pH",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(&){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1and\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(groundwater){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/e","'\\1Ground Water\\3'",$new);
                  $new = preg_replace("/(\W|^){1}(cross){1}(\s){1}(connection){1}(\W|$){1}/ie","'\\1\\2-\\4\\5'",$new); // Always hyphenate cross-connections.
                  $new = preg_replace("/(\s|\"|\'){1}(vs\.){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1Vs.\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(on-off){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1On-Off\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(on-site){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1On-Site\\3'",$new);
                  // Special cases like Class A Fires.
                  $new = preg_replace("/(\s|\"|\'){1}(class\s){1}(\w){1}(\s|,|\.|\"|\'|:|!|\?|\*|$){1}/ie","'\\1\\2'.strtoupper('\\3').'\\4'",$new);
                  $new = stripslashes($new);
                  $string_array[$k] = $new;
            }
        }
        for ($k=0; $k<count($string_array); $k++) {
          $newString .= $string_array[$k];
        }
        return($newString);
    }

    function regexValidation($input, $expected, $referenceName) { // Tests to see if entered fields adhere to expected formats.  NOTE:  Required fields already exist, so we don't have to check for required state.
      global $errorMessage;
      if(strlen($input) > 0 && !preg_match($expected, $input)) {
        $errorMessage .= "*The $referenceName you entered does not appear to be valid.<br />"; // $referenceName should be dynamically read.
        return false;
      }
      return true;
    }
?>