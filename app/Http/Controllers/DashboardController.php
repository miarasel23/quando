<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
     public function index(Request $request)
    {

        $payload = $this->buildPayload();

       $url="https://sandbox-api.ryftpay.com/v1/webhooks";



        // Initialize cURL
        $ch = curl_init($url);


        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($payload))
        ]);

        // Execute cURL
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }

        // Close cURL
        curl_close($ch);

        // Handle response (if needed)
        return $response;



    $response = $this->redirectToRyftPay($payload);
    }






    protected function buildPayload()
    {
        return [
            [
                "id" => "ps_01FCTS1XMKH9FF43CAFA4YYXT3P",
                "amount" => 500,
                "currency" => "GBP",
                "paymentType" => "Standard",
                "entryMode" => "Online",
                "customerEmail" => "example@mail.com",
                "customerDetails" => [
                    "id" => "cus_01G0EYVFR02KBBVE2YWQ8AKMGJ",
                    "firstName" => "Fred",
                    "lastName" => "Jones",
                    "homePhoneNumber" => "+447900000000",
                    "mobilePhoneNumber" => "+447900000000",
                    "metadata" => [
                        "customerId" => "123"
                    ]
                ],
                "credentialOnFileUsage" => [
                    "initiator" => "Customer",
                    "sequence" => "Initial"
                ],
                "previousPayment" => [
                    "id" => "string"
                ],
                "rebillingDetail" => [
                    "amountVariance" => "Fixed",
                    "numberOfDaysBetweenPayments" => 30,
                    "totalNumberOfPayments" => 12,
                    "currentPaymentNumber" => 1,
                    "expiry" => 1776988800
                ],
                "enabledPaymentMethods" => [
                    "Card"
                ],
                "paymentMethod" => [
                    "type" => "Card",
                    "tokenizedDetails" => [
                        "id" => "pmt_01G0EYVFR02KBBVE2YWQ8AKMGJ",
                        "stored" => true
                    ],
                    "card" => [
                        "scheme" => "Mastercard",
                        "last4" => "4242"
                    ],
                    "wallet" => [
                        "type" => "GooglePay"
                    ],
                   "billingAddress" => [
                        "firstName" => "MD Rasel",
                        "lastName" => "Mia",
                        "lineOne" => "123 Example Street",
                        "lineTwo" => "Flat 4B",
                        "city" => "London",
                        "country" => "UK",
                        "postalCode" => "E7 8EX",
                        "region" => "Greater London"
                    ],

                    "checks" => [
                        "avsResponseCode" => "Y",
                        "cvvResponseCode" => "M"
                    ]
                ],
                "platformFee" => 50,
                "splitPaymentDetail" => [
                    "items" => [
                        [
                            "id" => "sp_01FCTS1XMKH9FF43CAFA4CYYXT3P",
                            "accountId" => "ac_b83f2653-06d7-44a9-a548-5825e8186004",
                            "amount" => 50,
                            "fee" => [
                                "amount" => 50
                            ],
                            "description" => "2 x The Selfish Gene",
                            "metadata" => [
                                "productId" => "123",
                                "productDescription" => "The Selfish Gene"
                            ]
                        ]
                    ]
                ],
                "status" => "PendingPayment",
                "metadata" => [
                    "orderNumber" => "123"
                ],
                "clientSecret" => "sk_sandbox_TyOGnha9jCBNo3UxRlAru1cQnbT4wBiELjBrOQh+A0DEs7oCpV15ARitR8LFEy0V",
                "lastError" => "insufficient_funds",
                "refundedAmount" => 120,
                "statementDescriptor" => [
                    "descriptor" => "Ryft Ltd",
                    "city" => "London"
                ],
                "requiredAction" => [
                    "type" => "Redirect",
                    "url" => "https://ryftpay.com/3ds-auth"
                ],
                "returnUrl" => "https://ryftpay.com/checkout?orderId=123&ps=sk_sandbox_TyOGnha9jCBNo3UxRlAru1cQnbT4wBiELjBrOQh+A0DEs7oCpV15ARitR8LFEy0V",
                "authorizationType" => "FinalAuth",
                "captureFlow" => "Automatic",
                "verifyAccount" => true,
                "shippingDetails" => [
                    "address" => [
                        "firstName" => "Fox",
                        "lastName" => "Mulder",
                        "lineOne" => "Stonehenge",
                        "postalCode" => "SP4 7DE",
                        "city" => "Salisbury",
                        "country" => "GB"
                    ]
                ],
                "orderDetails" => [
                    "items" => [
                        [
                            "reference" => "product123",
                            "name" => "The Big Gundown (Blu-ray)",
                            "quantity" => 2,
                            "unitPrice" => 250,
                            "taxAmount" => 50,
                            "totalAmount" => 540,
                            "discountAmount" => 10,
                            "productUrl" => "string",
                            "imageUrl" => "string"
                        ]
                    ]
                ],
                "createdTimestamp" => 1470989538,
                "lastUpdatedTimestamp" => 1470989538
            ]
        ];
    }


}
