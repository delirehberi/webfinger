<?php
declare(strict_types=1);

/**
 * Created by PhpStorm
 */
class ArrayAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function exampleUser()
    {
        return (new class implements \DelirehberiWebFinger\ResourceDescriptorInterface
        {
            public function transform(): \DelirehberiWebFinger\JsonRD
            {
                $data = new \DelirehberiWebFinger\JsonRD();
                $data
                    ->setSubject("acct:" . $this->getEmail());
                $data->addAlias('https://www.example.com/~' . $this->getUsername() . "/");

                $link = new \DelirehberiWebFinger\JsonRDLink();
                $link->setRel('http://openid.net/specs/connect/1.0/issuer')
                    ->setHref('https://openid.example.com');
                $data->addLink($link);

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

            public function getVcardUrl()
            {
                return "bob.vcf";
            }

            public function getUsername()
            {
                return "bob";
            }

            public function getEmail()
            {
                return 'bob@example.com';
            }
        });
    }

    public function exampleUserAdapter()
    {
        $user = $this->exampleUser();
        $userAdapter = new \DelirehberiWebFinger\Adapter\ArrayAdapter();
        $userAdapter->add($user);
        $userAdapter->setScheme(\DelirehberiWebFinger\Constants::Account);
        $userAdapter->setFilter(function ($user, string $query) {
            if ($user->getEmail() == $query) {
                return true;
            }
            return false;
        });
        return $userAdapter;
    }

    public function testArrayAdapterForAcct()
    {
        $userAdapter = $this->exampleUserAdapter();
        $webfinger = new \DelirehberiWebFinger\WebFinger();
        $webfinger->addResource($userAdapter);

        try {
            $data = $webfinger->response("?resource=acct:bob@example.com");
        } catch (\Exception $a) {
            echo $a->getMessage();
        }
        $result = $data->toJSON();
        $this->assertEquals(self::BOB_RESPONSE, $result);
    }

    public function testArrayAdapterForHttps()
    {
        $content = (new class implements \DelirehberiWebFinger\ResourceDescriptorInterface
        {
            public function transform(): \DelirehberiWebFinger\JsonRD
            {
                $data = new \DelirehberiWebFinger\JsonRD();
                $data
                    ->setSubject($this->getFullUrl());
                $data->addAlias('https://www.example.com/blog/' . $this->getId());
                $data->addProperty('http://blgx.example.net/ns/version', "1.3");
                $link = new \DelirehberiWebFinger\JsonRDLink();
                $link->setRel('copyright')
                    ->setHref('http://www.example.com/copyright');
                $data->addLink($link);

                $link = new \DelirehberiWebFinger\JsonRDLink();
                $link->setRel('author')
                    ->setHref($this->getAuthorUrl())
                    ->addTitle('en_US', $this->getAuthorTitle())
                    ->addTitle('tr_TR', $this->getAuthorTitle('tr_TR'))
                    ->addProperty('http://example.com/role', 'editor');
                $data->addLink($link);

                return $data;
            }

            public function getId()
            {
                return 10;
            }

            public function getAuthorTitle($locale = 'en_US')
            {
                $titles = [
                    'en_US' => "Steve`s world",
                    'tr_TR' => 'Steve`in dünyası',
                ];
                return $titles[$locale];
            }

            public function getSlug()
            {
                return "hello-world";
            }

            public function getFullUrl()
            {
                return "http://blog.example.com/" . $this->getSlug();
            }

            public function getAuthorUrl()
            {
                return "http://blog.example.com/author/steve";
            }
        });

        $contentAdapter = new \DelirehberiWebFinger\Adapter\ArrayAdapter();
        $contentAdapter
            ->add($content)
            ->setScheme(\DelirehberiWebFinger\Constants::Content);
        $contentAdapter->addModifier(function ($query) {
            $url = parse_url($query);
            if (!isset($url['path'])) {
                return null;
            }
            $path = trim($url['path'], '/');
            return $path;
        });
        $contentAdapter->setFilter(function ($content, $query) {
            if ($content->getSlug() == $query) {
                return true;
            }
            return false;
        });
        $webfinger = new \DelirehberiWebFinger\WebFinger();
        $webfinger->addResource($contentAdapter);
        try {
            $data = $webfinger->response("?resource=http://blog.example.com/hello-world");
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        $this->assertEquals(self::CONTENT_RESPONSE, $data->toJSON());
    }

    public function testArrayAdapterAcctForRel()
    {
        $userAdapter = $this->exampleUserAdapter();
        $webfinger = new \DelirehberiWebFinger\WebFinger();
        $webfinger->addResource($userAdapter);

        try {
            $data = $webfinger->response("?resource=acct:bob@example.com&rel=http://openid.net/specs/connect/1.0/issuer");
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
        $this->assertEquals(self::REL_BOB_RESPONSE, $data->toJSON());
    }

    const REL_BOB_RESPONSE = '{"subject":"acct:bob@example.com","links":[{"rel":"http:\/\/openid.net\/specs\/connect\/1.0\/issuer","href":"https:\/\/openid.example.com"}]}';
    const BOB_RESPONSE = '{"subject":"acct:bob@example.com","aliases":["https:\/\/www.example.com\/~bob\/"],"links":[{"rel":"http:\/\/openid.net\/specs\/connect\/1.0\/issuer","href":"https:\/\/openid.example.com"},{"rel":"http:\/\/webfinger.example\/rel\/profile-page","href":"https:\/\/www.example.com\/~bob\/"},{"rel":"http:\/\/webfinger.example\/rel\/businesscard","href":"https:\/\/www.example.com\/~bob\/bob.vcf"}],"properties":{"http:\/\/example.com\/ns\/role":"employee"}}';
    const CONTENT_RESPONSE = '{"subject":"http:\/\/blog.example.com\/hello-world","aliases":["https:\/\/www.example.com\/blog\/10"],"links":[{"rel":"copyright","href":"http:\/\/www.example.com\/copyright"},{"rel":"author","href":"http:\/\/blog.example.com\/author\/steve","titles":{"en_US":"Steve`s world","tr_TR":"Steve`in d\u00fcnyas\u0131"},"properties":{"http:\/\/example.com\/role":"editor"}}],"properties":{"http:\/\/blgx.example.net\/ns\/version":"1.3"}}';
}