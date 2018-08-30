<?php

namespace Italia\Spid\Spid\Saml\In;

class LogoutResponse extends Base
{
    public function validate()
    {
        if (!isset($_POST) || !isset($_POST['SAMLResponse'])) {
            return false;
        }

        $xmlString = base64_decode($_POST['SAMLResponse']);
        $xml = new \DOMDocument();
        $xml->loadXML($xmlString);

        $root = $xml->getElementsByTagName('LogoutResponse')->item(0);
        
        if (is_null($root)) {
            return false;
        }
        if ($root->getAttribute('ID') == "") {
            throw new \Exception("missing ID attribute");
        }
        if ($root->getAttribute('Version') == "") {
            throw new \Exception("missing Version attribute");
        } elseif ($root->getAttribute('Version') != '2.0') {
            throw new \Exception("Invalid Version attribute");
        }
        if ($root->getAttribute('IssueInstant') == "") {
            throw new \Exception("Missing IssueInstant attribute");
        }
        if ($root->getAttribute('InResponseTo') == "" || !isset($_SESSION['RequestID'])) {
            throw new \Exception("Missing InResponseTo attribute, or request ID was not saved correctly for comparison");
        } elseif ($root->getAttribute('InResponseTo') != $_SESSION['RequestID']) {
            throw new \Exception("Invalid InResponseTo attribute, expected " . $_SESSION['RequestID']);
        }
        if ($root->getAttribute('Destination') == "") {
            throw new \Exception("Missing Destination attribute");
        }

        if ($xml->getElementsByTagName('Status')->length <= 0) {
            throw new \Exception("Missing Status element");
        } elseif ($xml->getElementsByTagName('StatusCode')->item(0)->getAttribute('Value') != 'urn:oasis:names:tc:SAML:2.0:status:Success') {
            // Status code != success
            return false;
        }

        session_unset();
        return true;
    }
}