<?php


namespace DelirehberiWebFinger;

/**
 * Class JsonRD
 * @package DelirehberiWebFinger
 * The JSON Resource Descriptor (JRD), originally introduced in RFC 6415
 * [https://tools.ietf.org/html/rfc7033#ref-16] and based on the Extensible Resource Descriptor (XRD) format
 * [https://tools.ietf.org/html/rfc7033#ref-17], is a JSON object that comprises the following name/value pairs:
 *
 * o subject
 * o aliases
 * o properties
 * o links
 *
 * The member "subject" is a name/value pair whose value is a string,
 * "aliases" is an array of strings, "properties" is an object
 * comprising name/value pairs whose values are strings, and "links" is
 * an array of objects that contain link relation information.
 *
 * When processing a JRD, the client MUST ignore any unknown member and
 * not treat the presence of an unknown member as an error.
 */
class JsonRD
{
    /**
     * The value of the "subject" member is a URI that identifies the entity
     * that the JRD describes.
     *
     * The "subject" value returned by a WebFinger resource MAY differ from
     * the value of the "resource" parameter used in the client's request.
     * This might happen, for example, when the subject's identity changes
     * (e.g., a user moves his or her account to another service) or when
     * the resource prefers to express URIs in canonical form.
     *
     * The "subject" member SHOULD be present in the JRD.
     * @var  string
     */
    protected $subject = "";

    /**
     * The "aliases" array is an array of zero or more URI strings that
     * identify the same entity as the "subject" URI.
     * The "aliases" array is OPTIONAL in the JRD.
     * @var array[string]
     */
    protected $aliases = [];

    /**
     * The "properties" object comprises zero or more name/value pairs whose
     * names are URIs (referred to as "property identifiers") and whose
     * values are strings or null.  Properties are used to convey additional
     * information about the subject of the JRD.  As an example, consider
     * this use of "properties":
     *
     * "properties" : { "http://webfinger.example/ns/name" : "Bob Smith" }
     *
     * The "properties" member is OPTIONAL in the JRD.
     *
     * @var array[string=>string]
     */
    protected $properties = [];

    /**
     * The "links" array has any number of member objects, each of which
     * represents a link [4].
     * @var array[JsonRDLink]
     */
    protected $links = [];

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return JsonRD
     */
    public function setSubject(string $subject): JsonRD
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param array $aliases
     * @return JsonRD
     */
    protected function setAliases(array $aliases): JsonRD
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * @param string $uri
     * @return JsonRD
     */
    public function addAlias(string $uri): JsonRD
    {
        array_push($this->aliases, $uri);
        return $this;
    }

    /**
     * @param string $uri
     * @return JsonRD
     */
    public function removeAlias(string $uri): JsonRD
    {
        $key = array_search($uri, $this->aliases);
        if (false !== $key) {
            unset($this->aliases[$key]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return JsonRD
     */
    protected function setProperties(array $properties): JsonRD
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param string $uri
     * @param string|null $value
     * @return JsonRD
     */
    public function addProperty(string $uri, string $value = null): JsonRD
    {
        $this->properties[$uri] = $value;
        return $this;
    }

    /**
     * @param string $uri
     * @return JsonRD
     */
    public function removeProperty(string $uri): JsonRD
    {
        if (!array_key_exists($uri, $this->properties)) {
            return $this;
        }
        unset($this->properties[$uri]);
        return $this;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     * @return JsonRD
     */
    protected function setLinks(array $links): JsonRD
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @param JsonRDLink $link
     * @return JsonRD
     */
    public function addLink(JsonRDLink $link): JsonRD
    {
        array_push($this->links, $link);
        return $this;
    }

    /**
     * @param JsonRDLink $link
     * @return JsonRD
     */
    public function removeLink(JsonRDLink $link): JsonRD
    {
        $serialized_link = serialize($link);
        foreach ($this->links as $key => $_link) {
            $_serialized_link = serialize($_link);
            if ($_serialized_link === $serialized_link) {
                unset($this->links[$key]);
                break;
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        $data['subject'] = $this->getSubject();
        !empty($this->getAliases()) && $data['aliases'] = $this->getAliases();
        !empty($this->getLinks()) && $data['links'] = array_map(function (JsonRDLink $jsonRdLink) {
            return $jsonRdLink->toArray();
        }, $this->getLinks());
        !empty($this->getProperties()) && $data['properties'] = $this->getProperties();
        return $data;
    }
}