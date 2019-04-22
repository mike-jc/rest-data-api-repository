# Example of usage

Inside Symfony controller:

```php
$this->get('app.entities.manager')->getRepository(MeetingRequest::class)->find($requestId);
```

This code under curtains:
 * according to annotations in the entity class will get target endpoint URL of the REST API
 * will make request to remote JSON REST API 
 * will check and parse JSON response and hydrate it to the object that is instance of the initial entity class
 
Also for POST/PUT requests (for `save` method of the repository):
 * according to annotations in the entity class will build request body
 
 
Repository supports relations between entities and is optimized (uses cache).