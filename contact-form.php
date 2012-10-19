<div id="contactdynamic">
	<?php

		/* GOALS & NOTES:
			*Find a way to incorporate storing (encrypted?) of collected user contact information in MySQL databases.
			*Required fields are stored within a global array, but it's only used in one function so far.
			*Store all fields in a global array as well, so we can automate the email building based on the forms that are present.
			*Moved the "Email contact configuration" to circa line 30 (global) for ease of use. Had these variables only conditionally defined if the form needed processing for better performace, but it decreased the user experience. Suggestions?
			*Would prefer to define many configuration variables locally in the first IF statement to improve page-rendering performance, but not sure how to do that without making the code look complicated and overwhelming.
		*/

		// SETTINGS ==========================================
			// Contact form configuration:
				$fields = array( // Which fields do you want the contact form to include?
					'Name' => 'name',
					'Phone Number' => 'phone',
					'Email Address' => 'email', // NOTE: RIGHT NOW THIS SECTION DOES NOTHING.
					'Source' => 'source',
					'Message' => 'message',
				);
				$requiredFields = array('Name', 'Phone Number'); // Which of the previous fields do you wish to be required?
				// NOTE: RIGHT NOW THIS SECTION DOES NOTHING - Array is overwritten when the isRequired function is called.

			// Business information configuration:
				$businessName = 'Example Business Name';
				$businessWebsite = 'http://example.com';
				$businessFacebook = 'http://www.facebook.com/ExampleURL';
				$businessEmail = 'info@example.com'; // The verification message to the customer comes from this address.
				$canReply = true; // This setting allows the customer to reply to this email address.

			// Email contact configuration:
				$contactEmails = 'info@example.com, boss@example.com'; // The customer's message is sent to these email addresses.
				$emailSubjectInformation = $businessName.'\'s Contact Form Information';
				$emailSubjectVerification = $businessName.'\'s Contact Form Verification';
				$emailBodyVerification = "Thank you for contacting $businessName! This is an automatically generated email to verify that we have received your information. A copy of your information is included below. We will contact you as soon as possible to answer your questions or address your concerns. In the meantime, please visit our Facebook ($businessFacebook) and website ($businessWebsite) to see what's new at $businessName! Have a great day!\n\n";

			// Default webpage content
				function defaultContent() { ?>
					<h2>
						Contact <? echo $businessName ?>
					</h2>
					<p>
						You can use the information below to contact <strong><em><? echo $businessName ?></em></strong> to find out more information about what we do, or to receive a free estimate on a project you may want to get started on. Our project specialists are ready to help you.
					</p>
					<?
				}

			// Success webpage content
				function successContent() {
					global $businessFacebook, $businessName; ?>
					<h2>
						Success!
					</h2>
					<p>
						Your information has been sent! We will contact you as soon as possible. If you provided an email, a verification email has been sent containing the information you submitted.
					</p>
					<p>
						While you wait for us to contact you to answer your questions, address your conerns, or give you a free estimate, please take a second to check out our <a href="<? echo $businessFacebook; ?>" target="blank">Facebook</a> to see what's new at <? echo $businessName; ?>!
					</p>
					<?
				}

			// Failure webpage content
				function failureContent($errors='We\'re sorry for the inconvenience. Please try again.') { ?>
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
						<? echo $errors; ?>
					</p>
					<?
				}
		//====================================================

		// Global variables
			$errorMessage = '';

		do { // This do-while(0) loop allows us to break out of its loop with the break command, without terminating the rest of the page's rendering of HTML.

			// Check to see if the information from the form needs to be processed.
				if(isset($_POST['submit'])) { // Process the contact form.

					// Validation - expected data must exist
						if(!isRequired('name', 'phone')) { // Place required form information here; unlimited parameters are accepted.
							failureContent($errorMessage);
							break; // Break out of do-while(0) loop;
						}

					// Formatting and storing the data from the fields in variables
						$inputName = titleCase(prepareString($_POST['name']));
						$inputPhone = prepareString($_POST['phone']);
						$inputEmail = strtolower(prepareString($_POST['email']));
						$inputEmail = filter_var($inputEmail, FILTER_SANITIZE_EMAIL);
						$inputSource = titleCase(prepareString($_POST['source']));
						$inputMessage = prepareString($_POST['message']);

					// Validation - Data must adhere to expected characters (REGEX)
						$expectedName = '/^[A-Za-z\.\'-]{2,}([\s][A-Za-z\.\'-]{2,})+$/';
						$expectedPhone = '/^(\+?1[\-\. ]?)?\(?[2-9][0-8][0-9][\)\-\. ]?[0-9]{3}[\-\. ]?[0-9]{4}$/';
						$expectedEmail = '/^[\_]*([a-z0-9]+(\.|\_*)?)+@([a-z][a-z0-9\-]+(\.|\-*\.))+[a-z]{2,6}$/';

						// Testing the formats of the inputs
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
							failureContent($errorMessage);
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

					// Writes dynamic content to the webpage
						if ($informationSuccess) {
							successContent();
						} else {
							failureContent('*E-mail failed to send, please check your email address and try again.');
						}
				} else { /* Writes the default content to the webpage */
					defaultContent();
				}
		} while (0);

		// Helper functions
			function isRequired() { // Checks to see if required POST data from form is provided. Takes unlimited parameters.
				global $errorMessage, $requiredFields;
				$notSet = false;
				$requiredFields = func_get_args();
				foreach ($requiredFields as $i) {
					if($_POST[$i] == '' || !isset($_POST[$i])) {
						$errorMessage .= "*The $i field (required) wasn't filled out.<br />";
						$notSet = true;
					}
				}
				return ($notSet === true) ? false : true;
			}

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
					$errorMessage .= "*The $referenceName you entered does not appear to be valid.<br />";
					return false;
				}
				return true;
			}
	?>
</div>

<div id="contactform">
	<h3>
		Free Estimate/Information Request Form:
	</h3>
	<form method="post" action="<? echo $_SERVER['PHP_SELF']; /* The form refers to the file in which it resides, that is, this file. */ ?>">
		<label for="name">Name*:</label>
		<input id="name" type="text" name="name" />

		<label for="phone">Phone Number*:</label>
		<input id="phone" type="text" name="phone" />

		<label for="email">E-mail:</label>
		<input id="email" type="text" name="email" />

		<label for="message">How can WE help you?</label>
		<textarea name="message" rows="5" cols="20" id="message"></textarea>

		<label for="source">How did you hear about us?</label>
		<input id="source" type="text" name="source" />

		<input type="submit" name="submit" value="Submit" id="submit" />
	</form>
</div>