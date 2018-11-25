<?php
declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: delirehberi
 * Date: 11/24/18
 * Time: 11:44 PM
 */
class ScenarioTest extends \PHPUnit\Framework\TestCase
{
    public function testValidWebFingerScenario()
    {
        $query = new \Symfony\Component\HttpFoundation\ParameterBag();
        $query->add([
            'resource' => 'acct:bob@example.com',
        ]);
        $request = new \DelirehberiWebFinger\Request();
        $request->setMethod('GET');
        $request->query = $query;

        $user = (new class implements \DelirehberiWebFinger\ResourceDescriptorInterface
        {
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
        $response = new \DelirehberiWebFinger\Response();
        $response->setData($user);

        $this->assertEquals(self::BOB_RESPONSE, $response->getContent());
        $this->assertEquals('application/jrd+json', $response->headers->get('Content-Type'));
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testInvalidWebFingerScenario()
    {
        $query = new \Symfony\Component\HttpFoundation\ParameterBag();
        $query->add([
            'resource' => 'acct:bob@example.com',
        ]);
        $request = new \DelirehberiWebFinger\Request();
        $request->setMethod('GET');
        $request->query = $query;

        $user = (new class implements \DelirehberiWebFinger\ResourceDescriptorInterface
        {
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
        $response = new \Symfony\Component\HttpFoundation\JsonResponse();
        $response->setData($user);

        $this->assertNotEquals(self::BOB_RESPONSE, $response->getContent());
        $this->assertNotEquals('application/jrd+json', $response->headers->get('Content-Type'));
        $this->assertNotEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    const BOB_RESPONSE = '{"subject":"acct:bob@example.com","aliases":["https:\/\/www.example.com\/~bob\/"],"links":[{"rel":"http:\/\/webfinger.example\/rel\/profile-page","href":"https:\/\/www.example.com\/~bob\/"},{"rel":"http:\/\/webfinger.example\/rel\/businesscard","href":"https:\/\/www.example.com\/~bob\/bob.vcf"}],"properties":{"http:\/\/example.com\/ns\/role":"employee"}}';
}