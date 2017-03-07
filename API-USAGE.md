# < API USAGE >

### Aka. "How to use the public reservation api:
**Information:**
<br>
The API displays the values as JSON-Object, so
if you are about to implement this API in your project,
so you best use this API by using XHR / AJAX requests to 
fetch the data and convert it to a readable Object by converting
the response text:

```JavaScript
var response = xhr.response.text;
var responseObject = JSON.parse(response);
```

**Parameters:**
<br>
(All Parameters are appended to the URL as $_GET Parameters --> 
../reservation-api.php?parameter1=value1&amp;parameter2=value2 \[...\])


Info: The response object will <u>always</u> return a field named "error".
If this field's value is "false", the object has a field named 
"data" which contains an array with the reservations. Otherwise - if the
"error" - field has the value "true", the Object will contain a field "message".
This field's value is a custom error text supplied by the backend script(s).


<table>
<thead>
<tr>
<th>Parameter</th>
<th>Values</th>
<th>Function</th>
</tr>
</thead>
<tbody>
<tr>
<td>"get"</td>
<td>"reservations"</td>
<td>Just a simple confirmation that the response is actually requested.</td>
</tr>
<tr>
<td>"prename" &amp; "surname"</td>
<td>Any prename and surname of the teacher you want to get the
reservations from.</td>
<td>The username for this teacher will automatically 
generated, therefore both prename and surname have to be set for the 
request to work. If one of the parameters is not set or is invalid, 
the script will fetch reservations from all teachers.</td>
</tr>
<tr>
<td>teacher</td>
<td>A teacher's Account-Name</td>
<td>This is the account-name of the teacher you want to get 
the reservations from. If the username is not found in the 
database, the script will fetch reservations from all teachers.</td>
</tr>
<tr>
<td>short</td>
<td>Any shortname (Lehrerkürzel)</td>
<td>This is the short form of the name / "Lehrerkürzel" of the 
teacher you want to get the reservations from. If this value
is not found in the database or doesn't match any account, the
script will fetch reservations from all teachers.</td>
</tr>
<tr>
<td>max</td>
<td>Integer > 0</td>
<td>This number will specify the maximum amount of returned
reservations. If this number is invalid (smaller than 0 or 
bigger than the maximum amount of the returnable reservation
datasets, the script will return all reservations.</td>
</tr>
</tbody>
</table>

---

If you want to get all reservations, just dont use the params
"teacher", "short" or "prename" &amp; "surname".
<br>
The parameter "max" can be appended to any request if wanted; it doesn't
have a default value.