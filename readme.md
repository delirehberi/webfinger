# PHP WebFinger implementation

WebFinger [RFC7033](https://tools.ietf.org/html/rfc7033) implementation for PHP. 
You can use your projects after with basic installation steps.

### Installation

- add to your dependencies 
    `composer require delirehberi/webfinger`
- create your WebFingerController and route it to `/.well-known/webfinger`
- configure your resources.
  - add user resource for `acct:` requests. eg: `acct:rik@florina.gov`
  - add url resource for `https://` request. eg: `https://florina.gov/news/missing-spatio-analyst`
  - add user resource for `mailto:` request. eg: `mailto:rik@florina.gov`


#### Example

- `$router` is router object
- `$dbh` pdo instance

At first, you need implement your entities from  `\DelirehberiWebFinger\ResourceDescriptorInterface`

---
After that you need to write your transform method.
```php

public function transform(): \DelirehberiWebFinger\JsonRD
            {
                $data = new \DelirehberiWebFinger\JsonRD();
                $data
                    ->setSubject("acct:" . $this->getEmail());
                $data->addAlias('https://www.example.com/~' . $this->getUsername() . "/");

                $link = new \DelirehberiWebFinger\JsonRDLink();
                $link->setRel('http://webfinger.example/rel/profile-page')
                    ->setHref('https://www.example.com/~' . $this->getUsername() . "/");
                $data->addLink($link);

                $link = new \DelirehberiWebFinger\JsonRDLink();
                $link->setRel('http://webfinger.example/rel/businesscard')
                    ->setHref('https://www.example.com/~' . $this->getUsername() . "/" . $this->getVcardUrl());
                $data->addLink($link);

                $data->addProperty('http://example.com/ns/role', 'employee');
                return $data;
            }
```
---

Last step is return your object in your controller.
```php
 $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
 $router->add('/.well-known/webfinger',function(Request $req)use($dbh){
 
    $userAdapter = new \DelirehberiWebFinger\Adapter\ArrayAdapter();
    $userAdapter->add($user);
    $userAdapter->setScheme(\DelirehberiWebFinger\Constants::Account);
    $userAdapter->setFilter(function(User $user,string $query){
        if($user->getEmail()==$query){
            return true;
        }
        return false;
    });
    
    $contentAdapter = new \DelirehberiWebFinger\Adapter\SqlQueryAdapter();
    $contentAdapter->setPdoInstance($dbg);
    $contentAdapter->setQuery("select * from contents where slug=:data");
    $contentAdapter->addModifier(function($url)use($router){
        $url = parse_url($url);
        $parse = $router->match($url['path');
        return $parse['slug']??false;
    });
    $contentAdapter->setScheme(\DelirehberiWebFinger\Constants::Content);
    
    $webfinger = new \DelirehberiWebFinger\WebFinger();
    $webfinger->addResource($userAdapter);
    $webfinger->addResource($contentAdapter);
    
    $data = $webfinger->response($req->getQuery());
    if($data===null){
        //header 404
        return "";
    }
    // don't forget add content-type to your response header
    // eg:  Content-Type: application/jrd+json
    return $data->transform()->toJSON();
 });
```
