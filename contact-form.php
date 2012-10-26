<?php

  /* GOALS & NOTES:
    * ENHANCEMENT - Automated data collection
        * MySQL integration
        * Google Sheets (Spreadsheet) integration
        * Data encryption
    * ENHANCEMENT - Need to make the validation area dynamic now, considering that the forms are now variable.
    * ENHANCEMENT - Add the capability of sending HTML content messages to customers (may already work).
    * ENHANCEMENT - Timestamps.

    * BEST PRACTICE - Google Analytics - Users are redirected to the same page, whether or not the outcome is default, success, or failure.  There is currently no way of delineating this behavior when it comes to tracking via Google Analytics.  How do we incorporate this?
    * BEST PRACTICE - Currently using IDs as hooks to style contact forms via CSS. Is this best practice?

    * PERFORMANCE - Would prefer to define many configuration variables locally in the first IF statement to improve page-rendering performance, but not sure how to do that without making the code look complicated and overwhelming.
    * PERFORMANCE - Contemplating using just ucwords() function to replace the titleCase() function.
  */


  // SETTINGS ==========================================
    // Contact form configuration:
      $formTitle = 'Free Estimate/Information Request Form';
      $inputFields = array(
        // Which fields do you want the contact form to include?
        // FORMAT: array(referenceKey (no spaces), Display Name, fieldType, isRequired?),
        array('name', 'Name', 'text', true),
        array('phone', 'Phone Number', 'text', true),
        array('email', 'Email Address', 'text', false),
        array('source', 'Source', 'text', false),
        array('message', 'How can we help you?', 'textarea', true),
      );
      $resetButton = false;

    // Business information configuration:
      $businessName = 'Example Business Name';
      $businessWebsite = 'http://example.com';
      $businessFacebook = 'http://www.facebook.com/ExampleURL';
      $businessEmail = 'info@example.com'; // The verification email that the customer recieves, comes from this address.
      $canReply = true; // UNIMPLEMENTED - This setting allows the customer to reply to this email address.

    // Email contact configuration:
      $contactEmails = 'info@example.com, boss@example.com'; // The customer's message is sent to these email addresses.
      $emailSubjectInformation = "$businessName's Contact Form Information"; // Subject line for the email sent to the business.
      $emailSubjectVerification = "$businessName's Contact Form Verification"; // Subject line for the email sent to the customer.
      $emailBodyVerification = "Thank you for contacting $businessName! This is an automatically generated email to verify that we have received your information. A copy of your information is included below. We will contact you as soon as possible to answer your questions or address your concerns. In the meantime, please visit our Facebook ($businessFacebook) and website ($businessWebsite) to see what's new at $businessName! Have a great day!\n\n"; // Body of the email sent to the customer.

    // Dynamic content - this is the content that is written above the form.  This content changes depending on actions from the customer.  If the customer is just visiting the page (hasn't submitted the form yet), they will see the default content; if the customer successfully submitted the form, they will see the success content; if the customer submitted the form with errors, they will see the failure content.
      // Default dynamic content
        $defaultContent = "
          <h2>
            Contact $businessName
          </h2>
          <p>
            You can use the information below to contact <strong><em>$businessName</em></strong> to find out more information about what we do, or to receive a free estimate on a project you may want to get started on. Our project specialists are ready to help you.
          </p>";

      // Success dynamic content
        $successContent = "
          <h2>
            Success!
          </h2>
          <p>
            Your information has been sent! We will contact you as soon as possible. If you provided an email, a verification email has been sent containing the information you submitted.
          </p>
          <p>
            While you wait for us to contact you to answer your questions, address your concerns, or give you a free estimate, please take a second to check out our <a href=\"$businessFacebook\" target=\"blank\">Facebook</a> to see what's new at $businessName!
          </p>";

      // Failure dynamic content
        function failureContent($errors='We\'re sorry for the inconvenience. Please try again.') {
          $failureContent = "
          <h2>
            Oops... Something went wrong!
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

  //====================================================


  // Initialization
    $content = '<div id="contactdynamic">';
    $errorMessage = '';
    $generatedForms = '';
    $requiredFields = array();

  // Contact form generation
    for ($i = 0; $i < sizeof($inputFields); $i++) {
      if ($inputFields[$i][3] === true) {
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
          <input name=\"{$inputFields[$i][0]}\" type=\"text\" />
          ";
      }
    }
    $generatedForms .= "
      <input name=\"submit\" value=\"Submit\" id=\"submit\" type=\"submit\" />";
    if ($resetButton === true) {
      $generatedForms .= "
      <input name=\"reset\" value=\"Clear\" id=\"reset\" type=\"reset\" />";
    }
    $contactForm = "

      <div id=\"contactform\">
        <h3>
          $formTitle
        </h3>
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
          $inputName = titleCase(prepareString($_POST['name']));
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
          regexValidation($inputName, $expectedName, 'name');
          regexValidation($inputPhone, $expectedPhone, 'phone number');
          $emailValidation = regexValidation($inputEmail, $expectedEmail, 'email address');
          if($emailValidation === true && !filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) { // Built-in fallback check.
            $errorMessage .= '*The email address you entered does not appear to be valid.<br />';
          }
          if (strlen($inputMessage) < 3) {
            $errorMessage .= '*You didn\'t tell us how we can help you.<br />';
          }
          if (strlen($errorMessage) > 0) {
            $content .= failureContent($errorMessage);
            break; // Break out of do-while(0) loop.
          }

        // Prepare email body information text
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
          $headersInformation = "From: $businessName <$businessEmail>\r\n";
          $headersInformation .= "Reply-To: $inputName <$inputEmail>\r\n";
          $headersVerification = "From: $businessName <$businessEmail>\r\n";
          $headersVerification .= "Reply-To: $businessName <$businessEmail>";

        // Send information email to business
          $informationSuccess = mail($contactEmails, $emailSubjectInformation, $emailBodyInformation, $headersInformation);

        // Send verification email to customer
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
      $newString = stripslashes($string);
      $newString = trim($newString);
      $bad = array('content-type','bcc:','to:','cc:','href');
      $newString = str_replace($bad,'',$newString);
      return $newString;
    }

    function titleCase($string) { // Converts $string into title case using multiple rules
      //remove no_parse content
        $string_array = preg_split("/(<no_parse>|<\/no_parse>)+/i",$string);
        $newString = '';
        for ($k=0; $k<count($string_array); $k=$k+2) {
          $string = $string_array[$k];
          //if the entire string is upper case dont perform any title case on it
            if ($string != strtoupper($string)) {
              //TITLE CASE RULES:
                //1.) uppercase the first char in every word
                  $new = preg_replace("/(^|\s|\'|'|\"|-){1}([a-z]){1}/ie","''.stripslashes('\\1').''.stripslashes(strtoupper('\\2')).''", $string);
                //2.) lower case words exempt from title case
                  // Lowercase all articles, coordinate conjunctions ("and", "or", "nor"), and prepositions regardless of length, when they are other than the first or last word.
                  // Lowercase the "to" in an infinitive." - this rule is of course aproximated since it is contex sensitive
                  $matches = array();
                  // perform recusive matching on the following words
                  preg_match_all("/(\sof|\sa|\san|\sthe|\sbut|\sor|\snot|\syet|\sat|\son|\sin|\sover|\sabove|\sunder|\sbelow|\sbehind|\snext\sto|\sbeside|\sby|\samoung|\sbetween|\sby|\still|\ssince|\sdurring|\sfor|\sthroughout|\sto|\sand){2}/i",$new ,$matches);
                  for ($i=0; $i<count($matches); $i++) {
                    for ($j=0; $j<count($matches[$i]); $j++) {
                      $new = preg_replace("/(".$matches[$i][$j]."\s)/ise","''.strtolower('\\1').''",$new);
                    }
                  }
                //3.) do not allow upper case appostraphies
                  $new = preg_replace("/(\w'S)/ie","''.strtolower('\\1').''",$new);
                  $new = preg_replace("/(\w'\w)/ie","''.strtolower('\\1').''",$new);
                  $new = preg_replace("/(\W)(of|a|an|the|but|or|not|yet|at|on|in|over|above|under|below|behind|next to| beside|by|amoung|between|by|till|since|durring|for|throughout|to|and)(\W)/ise","'\\1'.strtolower('\\2').'\\3'",$new);
                //4.) capitalize first letter in the string always
                  $new = preg_replace("/(^[a-z]){1}/ie","''.strtoupper('\\1').''", $new);
                //5.) replace special cases
                  // you will add to this as you find case specific problems
                  $new = preg_replace("/\sin-/i"," In-",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(ph){1}(\s|,|\.|\"|\'|:|!|\?|\*|$){1}/ie","'\\1pH\\3'",$new);
                  $new = preg_replace("/^ph(\s|$)/i","pH ",$new);
                  $new = preg_replace("/(\s)ph($)/i"," pH",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(&){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1and\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(groundwater){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/e","'\\1Ground Water\\3'",$new);
                  $new = preg_replace("/(\W|^){1}(cross){1}(\s){1}(connection){1}(\W|$){1}/ie","'\\1\\2-\\4\\5'",$new); //always hyphonate cross-connections
                  $new = preg_replace("/(\s|\"|\'){1}(vs\.){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1Vs.\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(on-off){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1On-Off\\3'",$new);
                  $new = preg_replace("/(\s|\"|\'){1}(on-site){1}(\s|,|\.|\"|\'|:|!|\?|\*){1}/ie","'\\1On-Site\\3'",$new);
                  // special cases like Class A Fires
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