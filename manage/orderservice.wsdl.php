<?php
ini_set("soap.wsdl_cache_enabled", "0");
$path = str_replace('//', '/', dirname(__FILE__) . '/');
$prefix = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'],'/'), '', $path);
$soap_server = 'http://' . $_SERVER['HTTP_HOST'] . $prefix . 'orderservice.php';
header("Content-Type: text/xml; charset=utf-8");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/"
             xmlns:soap12bind="http://schemas.xmlsoap.org/wsdl/soap12/"
             xmlns:soapbind="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:tns="<?= $soap_server; ?>"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:xsd1="<?= $soap_server; ?>"
             name="tl_exch"
             targetNamespace="<?= $soap_server; ?>">
<types>
    <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"
               xmlns:xs1="<?= $soap_server; ?>"
               targetNamespace="<?= $soap_server; ?>"
               attributeFormDefault="unqualified"
               elementFormDefault="qualified">
        <xs:complexType name="ClientsOrder">
            <xs:sequence>
                <xs:element name="id"
                            type="xs:int"/>
                <xs:element name="uid"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="number"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="date"
                            type="xs:dateTime"
                            nillable="true"/>
                <xs:element name="costumer_fio"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="currency"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="summ"
                            type="xs:long"
                            nillable="true"/>
                <xs:element name="count"
                            type="xs:int"
                            nillable="true"/>
                <xs:element name="debt"
                            type="xs:long"
                            nillable="true"/>
                <xs:element name="paid"
                            type="xs:long"
                            nillable="true"/>
                <xs:element name="products"
                            type="tns:product"
                            nillable="true"
                            minOccurs="0"
                            maxOccurs="unbounded"/>
            </xs:sequence>
        </xs:complexType>
        <xs:complexType name="ClientsOrderSI">
            <xs:sequence>
                <xs:element name="number"
                            type="xs:string"/>
                <xs:element name="Date"
                            type="xs:dateTime"/>
                <xs:element name="Status"
                            type="xs:string"/>
                <xs:element name="Summ"
                            type="xs:float"/>
            </xs:sequence>
        </xs:complexType>
        <xs:complexType name="File">
            <xs:all>
                <xs:element name="name"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="extension"
                            type="xs:string"
                            nillable="true"/>
                <xs:element name="binaryData"
                            type="xs:base64Binary"
                            nillable="true"/>
            </xs:all>
        </xs:complexType>
        <xs:complexType name="product">
            <xs:sequence>
                <xs:element name="n"
                            type="xs:int"/>
                <xs:element name="name"
                            type="xs:string"/>
                <xs:element name="image"
                            type="tns:File"
                            nillable="true"/>
                <xs:element name="count"
                            type="xs:int"/>
                <xs:element name="price"
                            type="xs:float"/>
                <xs:element name="summ"
                            type="xs:float"/>
            </xs:sequence>
        </xs:complexType>
        <xs:element name="GetOrder">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="number"
                                type="xs:string"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="GetOrderResponse">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="return"
                                type="xs:string"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="ClientPayment">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="number"
                                type="xs:string"/>
                    <xs:element name="uid"
                                type="xs:string"/>
                    <xs:element name="in_"
                                type="xs:double"/>
                    <xs:element name="out"
                                type="xs:double"/>
                    <xs:element name="acceptsumm"
                                type="xs:double"/>
                    <xs:element name="outsumm1"
                                type="xs:double"/>
                    <xs:element name="outsumm2"
                                type="xs:double"/>
                    <xs:element name="reject"
                                type="xs:double"
                                nillable="true"/>
                    <xs:element name="rejected"
                                type="xs:double"
                                nillable="true"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="ClientPaymentResponse">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="return"
                                type="xs:string"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="PaymentOf">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="number"
                                type="xs:string"/>
                    <xs:element name="uid"
                                type="xs:string"/>
                    <xs:element name="out"
                                type="xs:double"/>
                    <xs:element name="acceptsumm"
                                type="xs:double"/>
                    <xs:element name="outsumm1"
                                type="xs:double"/>
                    <xs:element name="outsumm2"
                                type="xs:double"/>
                    <xs:element name="reject"
                                type="xs:double"
                                nillable="true"/>
                    <xs:element name="rejected"
                                type="xs:double"
                                nillable="true"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="PaymentOfResponse">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="return"
                                type="xs:string"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="FixChanges">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="in_"
                                type="xs:double"/>
                    <xs:element name="out"
                                type="xs:double"/>
                    <xs:element name="acceptsumm"
                                type="xs:double"/>
                    <xs:element name="outsumm1"
                                type="xs:double"/>
                    <xs:element name="outsumm2"
                                type="xs:double"/>
                    <xs:element name="reject"
                                type="xs:double"
                                nillable="true"/>
                    <xs:element name="rejected"
                                type="xs:double"
                                nillable="true"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
        <xs:element name="FixChangesResponse">
            <xs:complexType>
                <xs:sequence>
                    <xs:element name="return"
                                type="xs:string"/>
                </xs:sequence>
            </xs:complexType>
        </xs:element>
    </xs:schema>
</types>
<message name="GetOrderRequestMessage">
    <part name="parameters"
          element="tns:GetOrder"/>
</message>
<message name="GetOrderResponseMessage">
    <part name="parameters"
          element="tns:GetOrderResponse"/>
</message>
<message name="ClientPaymentRequestMessage">
    <part name="parameters"
          element="tns:ClientPayment"/>
</message>
<message name="ClientPaymentResponseMessage">
    <part name="parameters"
          element="tns:ClientPaymentResponse"/>
</message>
<message name="PaymentOfRequestMessage">
    <part name="parameters"
          element="tns:PaymentOf"/>
</message>
<message name="PaymentOfResponseMessage">
    <part name="parameters"
          element="tns:PaymentOfResponse"/>
</message>
<message name="FixChangesRequestMessage">
    <part name="parameters"
          element="tns:FixChanges"/>
</message>
<message name="FixChangesResponseMessage">
    <part name="parameters"
          element="tns:FixChangesResponse"/>
</message>
<portType name="tl_exchPortType">
    <operation name="GetOrder">
        <input message="tns:GetOrderRequestMessage"/>
        <output message="tns:GetOrderResponseMessage"/>
    </operation>
    <operation name="ClientPayment">
        <input message="tns:ClientPaymentRequestMessage"/>
        <output message="tns:ClientPaymentResponseMessage"/>
    </operation>
    <operation name="PaymentOf">
        <input message="tns:PaymentOfRequestMessage"/>
        <output message="tns:PaymentOfResponseMessage"/>
    </operation>
    <operation name="FixChanges">
        <input message="tns:FixChangesRequestMessage"/>
        <output message="tns:FixChangesResponseMessage"/>
    </operation>
</portType>
<binding name="tl_exchSoapBinding"
         type="tns:tl_exchPortType">
    <soapbind:binding style="document"
                      transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="GetOrder">
        <soapbind:operation style="document"
                            soapAction="<?= $soap_server; ?>#tl_exch:GetOrder"/>
        <input>
        <soapbind:body use="literal"/>
        </input>
        <output>
            <soapbind:body use="literal"/>
        </output>
    </operation>
    <operation name="ClientPayment">
        <soapbind:operation style="document"
                            soapAction="<?= $soap_server; ?>#tl_exch:ClientPayment"/>
        <input>
        <soapbind:body use="literal"/>
        </input>
        <output>
            <soapbind:body use="literal"/>
        </output>
    </operation>
    <operation name="PaymentOf">
        <soapbind:operation style="document"
                            soapAction="<?= $soap_server; ?>#tl_exch:PaymentOf"/>
        <input>
        <soapbind:body use="literal"/>
        </input>
        <output>
            <soapbind:body use="literal"/>
        </output>
    </operation>
    <operation name="FixChanges">
        <soapbind:operation style="document"
                            soapAction="<?= $soap_server; ?>#tl_exch:FixChanges"/>
        <input>
        <soapbind:body use="literal"/>
        </input>
        <output>
            <soapbind:body use="literal"/>
        </output>
    </operation>
</binding>

<binding name="tl_exchSoap12Binding"
         type="tns:tl_exchPortType">
    <soap12bind:binding style="document"
                        transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="GetOrder">
        <soap12bind:operation style="document"
                              soapAction="<?= $soap_server; ?>#tl_exch:GetOrder"/>
        <input>
        <soap12bind:body use="literal"/>
        </input>
        <output>
            <soap12bind:body use="literal"/>
        </output>
    </operation>
    <operation name="ClientPayment">
        <soap12bind:operation style="document"
                              soapAction="<?= $soap_server; ?>#tl_exch:ClientPayment"/>
        <input>
        <soap12bind:body use="literal"/>
        </input>
        <output>
            <soap12bind:body use="literal"/>
        </output>
    </operation>
    <operation name="PaymentOf">
        <soap12bind:operation style="document"
                              soapAction="<?= $soap_server; ?>#tl_exch:PaymentOf"/>
        <input>
        <soap12bind:body use="literal"/>
        </input>
        <output>
            <soap12bind:body use="literal"/>
        </output>
    </operation>
    <operation name="FixChanges">
        <soap12bind:operation style="document"
                              soapAction="<?= $soap_server; ?>#tl_exch:FixChanges"/>
        <input>
        <soap12bind:body use="literal"/>
        </input>
        <output>
            <soap12bind:body use="literal"/>
        </output>
    </operation>
</binding>
<service name="tl_exch">
    <port name="tl_exchSoap"
          binding="tns:tl_exchSoapBinding">
        <documentation>
            <wsi:Claim xmlns:wsi="http://ws-i.org/schemas/conformanceClaim/"
                       conformsTo="http://ws-i.org/profiles/basic/1.1"/>
        </documentation>
        <soapbind:address location="<?= $soap_server; ?>"/>
    </port>
    <port name="tl_exchSoap12"
          binding="tns:tl_exchSoap12Binding">
        <soap12bind:address location="<?= $soap_server; ?>"/>
    </port>
</service>
</definitions>