<?php

namespace Peoplefone;

use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;
use PhpXmlRpc\Response;
use PhpXmlRpc\Value;
use XMLWriter;

abstract class ProvisioningRPCXML implements ProvisioningRPCInterface
{
	protected $client;
        protected $encoder;
	
	public function __construct($base_uri)
	{
		$this->client = new \GuzzleHttp\Client([
				'base_uri' => $base_uri,
		]);

            $this->encoder = new Encoder();
	}
	
	/**
	 * @param string $mac
	 * @return string
	 */
	public function formatMacAddress(string $mac) : string
	{
		$macid = null;
		
		if(substr_count($mac, '-')==1) // GIGASET
		{
			list($mac, $macid) = explode("-", $mac);
		}
		
		$mac = preg_replace("/[^a-f0-9\-]/", "", strtolower($mac));

        return !is_null($macid) && strlen($macid)>0 ? $mac."-".$macid : $mac;
	}

    /**
     * @param string $method
     * @param array $params
     * @return string
     *
     * replace deprecated xmlrpc_encode_request
     *
     */
    public function createXml(string $method, array $params): string
    {
        $oXMLWriter = new XMLWriter();
        $oXMLWriter->openMemory();
        $oXMLWriter->startDocument('1.0', 'UTF-8');

        $oXMLWriter->startElement('methodCall');
        // set the method name
        $oXMLWriter->startElement('methodName');
        $oXMLWriter->text($method);
        $oXMLWriter->endElement(); // methodName

        $oXMLWriter->startElement('params');
        foreach ($params as $param) {
            $oXMLWriter->startElement('param');
            $oXMLWriter->startElement('value');
            $oXMLWriter->startElement('string');
            $oXMLWriter->text($param);
            $oXMLWriter->endElement(); // string
            $oXMLWriter->endElement(); // value
            $oXMLWriter->endElement(); // param
        }
        $oXMLWriter->endElement(); // string
        $oXMLWriter->endElement(); // value
        $oXMLWriter->endElement(); // param
        $oXMLWriter->endElement(); // params
        $oXMLWriter->endElement(); // methodCall

        $oXMLWriter->endDocument();
        return $oXMLWriter->outputMemory(TRUE);
    }

    /**
     * Decode XML
     * @param string $data
     * @param array $options
     */
    protected function decodeXml(string $data, array $options = [])
    {
        $response = $this->encoder->decodeXml($data, $options);

        if ($response instanceof Response && $response->faultCode()) {
            throw new \RuntimeException("XML-RPC Fault {$response->faultCode()}: {$response->faultString()}");
        }

        return $this->encoder->decode($response->value());
    }
}