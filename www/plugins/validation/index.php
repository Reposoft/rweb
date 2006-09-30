<?php
/** form validation logic for repos

What we need:
User should be told directly which fields are required.
User should get direct validation of common fields such as e-mail or date (but this is not needed in repos yet).
Upon validation, the user wants to keep the values in the form.
User wants to see which fields are invalid.
Error messages can be localized, and the localization should be shared between pages

Development:
We still want valid HTML.
The server-side page that receives the sumbit knows which fields are required, and how to validate them.
Declaring required fields should take one line of code with the field names.
Programmatic declaration of rules should not depend on the choice of validation framework.
The HTML designer does not know or care which fields are required, 
and definitely does not care about the syntax checks for input, and can not provide the error message.
Some forms require semantic validation, which is a function in the server-side page.

Common validation rules:
required
date
datetime
time
minlength(x)
maxlength(x) (only server side, can be specified on client for input boxes)
filename (or foldername)
message (practically no restrictions on contents)

Repos.se assumptions:
Same field is shared in many forms (like filename or commit message).
Common input errors - missing field + invalid date - accounts for 80% of the user errors.
In the other 20%, it's no big deal if we do a roundtrip to the server.
90% of the browsers will run javascript+AJAX based validation.
Upon invalid input, it is quite ok to use browser's back functionality to preserve the form data
(so the non-javascript solution to form validation i to display an error page with a back button).
We can benefit from conventions, for example: 'target' is an absolute path to a file or folder.
All form fields have a <label for="fieldname"> tag, which contains the localized field name.

References:
http://particletree.com/features/a-guide-to-unobtrusive-javascript-validation/


*/


require(dirname(dirname(dirname(__FILE__))).'/conf/Presentation.class.php');
$p = new Presentation();
$p->display();

?>