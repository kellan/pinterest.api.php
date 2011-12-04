basic bindings for Pinterest API.

_This is an unofficial API, and likely to change and break at any moment._

==

You'll need an access token to make API calls.  This is accomplished with an XAuth like call using HTTP Basic Auth over SSL.

    $p = new Pinterest_API();
    $p->fetch_access_token($client_id, $client_secret, $username, $password);
    $resp = $p->some_api_call($args);

Alternately if you already have an access token
 
    $p = new Pinterest_API($access_token);
    $p->some_api_call($args);

==

The only methods I know about are:
  
  * `upload_pin`
  * `repin`
  * `boards`
  * `activity`
  * `popular`  

Notably missing is a `pin` method that simulates the pinmarklet.  (that said I haven't looked very hard, so if you find one, let me know)

