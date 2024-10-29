<?php
 /**
 * Copyright (c) 2021 arvato Finance B.V.
 *
 * AfterPay reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of AfterPay.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @name        AfterPay Class
 * @author      AfterPay (plugins@afterpay.nl)
 * @description PHP Library to connect with AfterPay Post Payment services
 * @copyright   Copyright (c) 2021 arvato Finance B.V.
 */

namespace Afterpay;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

#[AllowDynamicProperties]
class RestClient extends Client
{
    /**
     * @var GuzzleHttp\Client $restClient
     */
    private $restClient;

    /**
     * @var string $requestUrl
     */
    private $requestUrl;

    /**
     * @var string $requestMethod
     */
    private $requestMethod;

    /**
     * @var \GuzzleHttp\Client $response
     */
    private $apiResponse;

    /**
     * @var array|\stdClass $orderResultTmp
     */
    private $orderResultTmp;

    /**
     * @var array $statusCodes
     */
    private $statusCodes = [
        'Accepted' => 'A',
        'Pending' => 'P',
        'Rejected' => 'W'];

    /**
     * @var array $resultId
     */
    private $resultId = [
        'Accepted' => '0',
        'Pending' => '4',
        'Rejected' => '3'];

    /**
     * @var array $additional
     */
    private $additional;

    /**
     * Function to create order line
     *
     * @param int $productId
     * @param string $description
     * @param int $quantity
     * @param int $unitPrice
     * @param int $vatCategory
     * @param int $vatAmount
     * @param int $googleProductCategoryId
     * @param string $googleProductCategory
     * @param string $productUrl
     * @param string $imageUrl
     */
    public function createOrderLine(
        $productId,
        $description,
        $quantity,
        $unitPrice,
        $vatCategory = null,
        $vatAmount = 0,
        $googleProductCategoryId = null,
        $googleProductCategory = null,
        $productUrl = null,
        $imageUrl = null,
        $groupId = null
    )
    {
        $vatAmount = $this->roundAmount($vatAmount);
        $grossUnitPrice = $this->roundAmount(($unitPrice / 100));
        $netUnitPrice = $this->roundAmount(($grossUnitPrice - $vatAmount));
        if ($this->orderAction === 'refund_partial') {
            $orderLine['refundType'] = 'Refund';
            $grossUnitPrice = $this->roundAmount($grossUnitPrice * -1);
            $netUnitPrice = $this->roundAmount($netUnitPrice * -1);
            $vatAmount = $this->roundAmount($vatAmount * -1);
        }
        $orderLine = [
            'productId' => substr($productId, 0, 49),
            'description' => $description,
            'quantity' => (string) $quantity,
            'grossUnitPrice' => $grossUnitPrice,
            'netUnitPrice' => $netUnitPrice
        ];
        if ($vatAmount !== null) {
            $orderLine['vatAmount'] = $vatAmount;
            $orderLine['vatPercent'] = $this->roundAmount(\Afterpay\calculateVatPercentage(abs($grossUnitPrice), abs($vatAmount)));
        }
        if (isset($googleProductCategoryId)) {
            $orderLine['googleProductCategoryId'] = $googleProductCategoryId;
        }
        if (isset($googleProductCategory)) {
            $orderLine['googleProductCategory'] = $googleProductCategory;
        }
        if (isset($productUrl) && $this->verifyUrl($imageUrl)) {
            $orderLine['productUrl'] = $productUrl;
        }
        if (isset($imageUrl) && $this->verifyUrl($imageUrl)) {
            $orderLine['imageUrl'] = $imageUrl;
        }
        if (isset($groupId)) {
            $orderLine['groupId'] = $groupId;
        }

        $this->totalOrderAmount = ($this->totalOrderAmount + ($quantity * $grossUnitPrice));
        $this->totalNetAmount = ($this->totalNetAmount + ($quantity * $netUnitPrice));
        $this->orderLines[] = $orderLine;
    }

    /**
     * If order management is used set action to true and update orderAction property;
     *
     * @param string $action
     */
    public function setOrderManagement($action)
    {
        $this->orderManagement = true;
        $this->orderAction = $action;
        $this->createDebugLine('Set order management to: ' . $this->orderAction);
    }

    /**
     * Create order information
     *
     * @param array $order
     * @param string $orderType
     */
    public function setOrder($order, $orderType)
    {
        $orderId = (array_key_exists('ordernumber', $order) ? $order['ordernumber'] : '');
        $this->setOrderType($orderType, $orderId);
        if ($this->orderType == 'OM') {
            switch ($this->orderAction) {
                case 'capture_full':
                    // Divide by 100 because default was sent in eurocents
                    $totalAmount = $order['totalamount']  / 100;
                    $totalNetAmount = $order['totalNetAmount'];
                    $this->order = [
                        'orderDetails' => [
                            'totalGrossAmount' => $this->roundAmount(\Afterpay\convertPrice($totalAmount)),
                            'totalNetAmount' => $this->roundAmount(\Afterpay\convertPrice($totalNetAmount))
                        ],
                        'invoiceNumber' => $order['invoicenumber'],
                    ];
                    if (!empty($this->orderLines)) {
                        $this->order['orderDetails']['items'] = $this->orderLines;
                        $this->order['orderDetails']['totalGrossAmount'] = $this->roundAmount($this->totalOrderAmount);
                        $this->order['orderDetails']['totalNetAmount'] = $this->roundAmount($this->totalNetAmount);
                    }
                    break;
                case 'capture_partial':
                    // Not divided by 100 because this is already done while creating orderlines
                    $totalGrossAmount = $this->totalOrderAmount;
                    $totalNetAmount = $this->totalNetAmount;
                    $this->order = [
                        'orderDetails' => [
                            'totalGrossAmount' => $this->roundAmount(\Afterpay\convertPrice($totalGrossAmount)),
                            'totalNetAmount' => $this->roundAmount(\Afterpay\convertPrice($totalNetAmount)),
                            'items' => $this->orderLines,
                        ],
                        'invoiceNumber' => $order['invoicenumber'],
                    ];
                    break;
                case 'refund_full':
                    $this->order = '';
                    break;
                case 'refund_partial':
                    $this->order = [
                        'captureNumber' => $order['invoicenumber'],
                        'orderItems' => $this->orderLines,
                        'refundType' => 'Refund'
                    ];
                    break;
                case 'void':
                    $this->order = '';
                    if($this->orderLines) {
                        $this->order = [
                            'cancellationDetails' => [
                                'totalGrossAmount' => $this->roundAmount(\Afterpay\convertPrice($this->totalOrderAmount)),
                                'totalNetAmount' => $this->roundAmount(\Afterpay\convertPrice($this->totalNetAmount)),
                                'items' => $this->orderLines ?: ''
                            ]
                        ];
                    }
                    break;
                case 'customer_lookup':
                    if (isset($order['countryCode'])) {
                        $this->order['countryCode'] = $order['countryCode'];
                    }
                    if (isset($order['customerNumber'])) {
                        $this->order['customerNumber'] = $order['customerNumber'];
                    }
                    if (isset($order['email'])) {
                        $this->order['email'] = $order['email'];
                    }
                    if (isset($order['identificationNumber'])) {
                        $this->order['identificationNumber'] = $order['identificationNumber'];
                    }
                    if (isset($order['mobilePhone'])) {
                        $this->order['mobilePhone'] = $order['mobilePhone'];
                    }
                    if (isset($order['postalCode'])) {
                        $this->order['postalCode'] = $order['postalCode'];
                    }
                    break;
                case 'installmentplans_lookup':
                    $this->order = [
                        'amount' => $order['amount']
                    ];
                    break;
                case 'validate_bankaccount':
                    $this->order = [
                        'bankAccount' => $order['bankAccount']
                    ];
                    break;
                case 'available_payment_methods':
                    if (isset($order['conversationLanguage'])) {
                        $this->order['conversationLanguage'] = $order['conversationLanguage'];
                    }
                    if (isset($order['country'])) {
                        $this->order['country'] = strtoupper($order['country']);
                    }
                    if (isset($order['order']['totalGrossAmount'])) {
                        $this->order['order']['totalGrossAmount'] = $this->roundAmount($order['order']['totalGrossAmount']);
                    }
                    if (isset($order['order']['totalNetAmount'])) {
                        $this->order['order']['totalNetAmount'] = $this->roundAmount($order['order']['totalNetAmount']);
                    }
                    if (isset($order['order']['currency'])) {
                        $this->order['order']['currency'] = $order['order']['currency'];
                    }
                    if (array_key_exists('additionalData', $order)) {
                        $this->order['additionalData'] = $this->getPluginProviderData($order);
        
                        if (!isset($order['additionalData']['partnerData']['pspName']) &&
                            !isset($order['additionalData']['partnerData']['pspType'])) {
                            unset($this->order['additionalData']['partnerData']);
                        }
                    }
                    break;
                default:
                    break;
            }
            return;
        }

        $this->resolveOrderCountry($order);

        // Check generic salutatation for billtoaddress
        if (isset($order['billtoaddress']['referenceperson']['gender'])) {
            switch($order['billtoaddress']['referenceperson']['gender']) {
                case 'M' :
                    $order['billtoaddress']['referenceperson']['gender'] = 'Mr';
                    break;
                case 'V' :
                    $order['billtoaddress']['referenceperson']['gender'] = 'Mrs';
                    break;
                case 'Herr' :
                    $order['billtoaddress']['referenceperson']['gender'] = 'Mr';
                    break;
                case 'Frau' :
                    $order['billtoaddress']['referenceperson']['gender'] = 'Mrs';
                    break;
            }
        }

        $this->order = [
            'customer' => [
                'customerCategory' => 'Person',
                'address' => [
                    'postalPlace' => $order['billtoaddress']['city'],
                    'streetNumber' => $order['billtoaddress']['housenumber'],
                    'countryCode' => $order['billtoaddress']['isocountrycode'],
                    'postalCode' => $order['billtoaddress']['postalcode'],
                    'street' => $order['billtoaddress']['streetname'],
                    'careOf' => (isset($order['billtoaddress']['careof']) ? $order['shiptoaddress']['careof'] : ''),
                ],
                'birthDate' => (isset($order['billtoaddress']['referenceperson']['dob'])
                    ? $order['billtoaddress']['referenceperson']['dob'] : ''),
                'identificationNumber' => (isset($order['billtoaddress']['referenceperson']['ssn'])
                    ? $order['billtoaddress']['referenceperson']['ssn'] : ''),
                'email' => $order['billtoaddress']['referenceperson']['email'],
                'salutation' => (
                    isset(
                        $order['billtoaddress']['referenceperson']['gender']
                    ) ? $order['billtoaddress']['referenceperson']['gender'] : ''),
                'firstName' => (
                    isset(
                        $order['billtoaddress']['referenceperson']['firstname']
                    ) ? $order['billtoaddress']['referenceperson']['firstname'] : ''),
                'conversationLanguage' => $order['billtoaddress']['referenceperson']['isolanguage'],
                'lastName' => $order['billtoaddress']['referenceperson']['lastname'],
                'riskData' => [
                    'ipAddress' => $order['ipaddress'],
                    'existingCustomer' => (isset($order['existingcustomer']) ? $order['existingcustomer'] : ''),
                    'profileTrackingId' => (isset($order['profileTrackingId']) ? $order['profileTrackingId'] : ''),
                    'customerIndividualScore' => (isset($order['customerIndividualScore']) ? $order['customerIndividualScore'] : '')
                ],
            ]
        ];

        // Check if the phonenumber is set and not empty, then merge it with the data
        if (array_key_exists('phonenumber', $order['billtoaddress']['referenceperson'])
        && $order['billtoaddress']['referenceperson']['phonenumber'] != '') {
            $this->order = array_merge_recursive($this->order,
                [
                    'customer' => [
                        'mobilePhone' => \Afterpay\cleanphone(
                            $order['billtoaddress']['referenceperson']['phonenumber'],
                            $order['billtoaddress']['isocountrycode']),
                            ],
                ]);
        }
        // Check if there is an additional information array, if so merge it with the order data
        if (array_key_exists('additionalData', $order)) {
            $this->order = array_merge_recursive($this->order,
                [
                    'additionalData' => $this->getPluginProviderData($order)
                ]);

            if (!isset($order['additionalData']['partnerData']['pspName']) &&
                !isset($order['additionalData']['partnerData']['pspType'])) {
                unset($this->order['additionalData']['partnerData']);
            }
        }

        // Check if there is an housenumber addition, if so merge it with the order data
        if (array_key_exists('housenumberaddition', $order['billtoaddress'])
            && !(empty($order['billtoaddress']['housenumberaddition']))) {
            $this->order = array_merge_recursive($this->order,
                [
                    'customer' => [
                        'address' => [
                            'streetNumberAdditional' => substr($order['billtoaddress']['housenumberaddition'],0,10)
                        ]
                    ]
                ]);
        }

        // Check generic salutatation for billtoaddress
        if (isset($order['shiptoaddress']['referenceperson']['gender'])) {
            switch($order['shiptoaddress']['referenceperson']['gender']) {
                case 'M' :
                    $order['shiptoaddress']['referenceperson']['gender'] = 'Mr';
                    break;
                case 'V' :
                    $order['shiptoaddress']['referenceperson']['gender'] = 'Mrs';
                    break;
                case 'Herr' :
                    $order['shiptoaddress']['referenceperson']['gender'] = 'Mr';
                    break;
                case 'Frau' :
                    $order['shiptoaddress']['referenceperson']['gender'] = 'Mrs';
                    break;
            }
        }

        // Check if the shiptoaddress differs from the billtoaddres, if so merge it to the order data
        if (!empty(\Afterpay\arrayRecursiveDiff($order['billtoaddress'], $order['shiptoaddress']))) {
            $this->order += [
                'deliveryCustomer' => [
                    'customerCategory' => 'Person',
                    'address' => [
                        'postalPlace' => $order['shiptoaddress']['city'],
                        'streetNumber' => $order['shiptoaddress']['housenumber'],
                        'countryCode' => $order['shiptoaddress']['isocountrycode'],
                        'postalCode' => $order['shiptoaddress']['postalcode'],
                        'street' => $order['shiptoaddress']['streetname'],
                        'careOf' => (isset($order['shiptoaddress']['careof']) ? $order['shiptoaddress']['careof'] : ''),
                        'addressType' => (isset($order['shiptoaddress']['addresstype']) ? $order['shiptoaddress']['addresstype'] : ''),
                    ],
                    'birthDate' => (isset($order['shiptoaddress']['referenceperson']['dob'])
                        ? $order['shiptoaddress']['referenceperson']['dob'] : ''),
                    'email' => $order['shiptoaddress']['referenceperson']['email'],
                    'salutation' => (
                        isset(
                            $order['shiptoaddress']['referenceperson']['gender']
                        ) ? $order['shiptoaddress']['referenceperson']['gender'] : ''),
                    'firstName' => (
                        isset(
                            $order['shiptoaddress']['referenceperson']['firstname']
                        ) ? $order['shiptoaddress']['referenceperson']['firstname'] : ''),
                    'conversationLanguage' => $order['shiptoaddress']['referenceperson']['isolanguage'],
                    'lastName' => $order['shiptoaddress']['referenceperson']['lastname'],
                ]
            ];
            if (array_key_exists('housenumberaddition', $order['shiptoaddress'])
                && !(empty($order['shiptoaddress']['housenumberaddition']))) {
                $this->order = array_merge_recursive($this->order,
                    [
                        'deliveryCustomer' => [
                            'address' => [
                                // The maximum amount of characters for a housenumber addition is 10.
                                'streetNumberAdditional' => substr($order['shiptoaddress']['housenumberaddition'],0,10)
                            ]
                        ]
                    ]);
            }
            if (array_key_exists('phonenumber', $order['shiptoaddress']['referenceperson'])
            && !(empty($order['shiptoaddress']['referenceperson']['phonenumber']))) {
                $this->order = array_merge_recursive($this->order,
                    [
                        'deliveryCustomer' => [
                            'mobilePhone' => \Afterpay\cleanphone(
                                $order['shiptoaddress']['referenceperson']['phonenumber'],
                                $order['shiptoaddress']['isocountrycode']),
                        ]
                    ]);
            }
        }

        // When ordertype is B2B, set the companyname and customer category.
        if($this->orderType == 'B2B') {
            if (array_key_exists('companyname', $order['company']) && $order['company']['companyname'] !== '') {
                $this->order['customer']['customerCategory'] = 'Company';
                $this->order['customer']['companyName'] = $order['company']['companyname'];
                if(isset($order['company']['identificationnumber']) && $order['company']['identificationnumber'] !== '') {
                    $this->order['customer']['identificationNumber'] = $order['company']['identificationnumber'];
                }
                if(isset($order['company']['legalform']) && $order['company']['legalform'] !== '') {
                    $this->order['customer']['legalForm'] = $order['company']['legalform'];
                }
                if (array_key_exists('deliveryCustomer', $this->order)) {
                    $this->order['deliveryCustomer']['customerCategory'] = 'Company';
                    $this->order['deliveryCustomer']['companyName'] = $order['company']['companyname'];
                    if(isset($order['company']['identificationnumber']) && $order['company']['identificationnumber'] !== '') {
                        $this->order['deliveryCustomer']['identificationNumber'] = $order['company']['identificationnumber'];
                    }
                    if(isset($order['company']['legalform']) && $order['company']['legalform'] !== '') {
                        $this->order['deliveryCustomer']['legalForm'] = $order['company']['legalform'];
                    }
                }
            }
        }

        $this->order += [
            'payment' => [
                'type' => 'Invoice'
            ],
            'order' => [
                'number' => $order['ordernumber'],
                'currency' => $order['currency'],
                'items' => $this->orderLines,
                'totalGrossAmount' => $this->roundAmount(\Afterpay\convertPrice($this->totalOrderAmount)),
                'totalNetAmount' => $this->roundAmount(\Afterpay\convertPrice($this->totalNetAmount))
            ]
        ];

        // Check if there is Installment number set, if so merge it with the order data
        // Also if country is DE or AT, then Direct Debit data is needed, otherwise not
        if (
            array_key_exists('installment', $order)
            && !(empty($order['installment']['profileNo']))
            && in_array($this->country, array('AT', 'DE'))
            ) {
            $this->order['payment'] =
                [
                    'type' => 'Installment',
                    'installment' => [
                        'profileNo' => $order['installment']['profileNo'],
                    ],
                    'directDebit' => [
                        'bankAccount' => $order['installment']['bankAccount']
                    ]
                ];
        }

        if (
            array_key_exists('installment', $order)
            && !(empty($order['installment']['profileNo']))
            && !in_array($this->country, array('AT', 'DE'))
            ) {
            $this->order['payment'] =
                [
                    'type' => 'Installment',
                    'installment' => [
                        'profileNo' => $order['installment']['profileNo'],
                    ]
                ];
        }

        // Check if this is account payment method, if so pass account profileNr in the request
        if (
            array_key_exists('account', $order)
            && !(empty($order['account']['profileNo']))
            ) {
            $this->order['payment'] =
                [
                    'type' => 'Account',
                    'account' => [
                        'profileNo' => $order['account']['profileNo'],
                    ]
                ];
        }

        // Check if this is campaign payment method, if so pass campaign number in the request
        if (
            array_key_exists('campaign', $order)
            && !(empty($order['campaign']['campaignNumber']))
            ) {
            $this->order['payment']['campaign'] =
                [
                        'campaignNumber' => $order['campaign']['campaignNumber'],
                ];
        }

        // Check if this is pay in X payment method, if so change the type element to pay in X in the request
        if (array_key_exists('payInX', $order) && !(empty($order['payInX']['dueAmount']))) {
            $this->order['payment'] =
                [
                    'type' => 'payInX',
                ];
        }

        // Check if there is DirectDebit element set, if so merge it with the order data
        if (array_key_exists('directDebit', $order)  && $order['directDebit']['bankAccount']) {
            $this->order['payment'] =
                [
                    'type' => 'Invoice',
                    'directDebit' => [
                        'bankAccount' => $order['directDebit']['bankAccount']
                    ]
                ];
        }

        // Check if there is google analytics user id set, if so merge it with the order data
        if (array_key_exists('googleAnalyticsUserId', $order) && !(empty($order['googleAnalyticsUserId']))) {
            $this->order = array_merge_recursive($this->order,
                [
                    'order' => [
                        'googleAnalyticsUserId' => $order['googleAnalyticsUserId'],
                    ],
                ]);
        }
        // Check if there is google analytics client id set, if so merge it with the order data
        if (array_key_exists('googleAnalyticsClientId', $order) && !(empty($order['googleAnalyticsClientId']))) {
            $this->order = array_merge_recursive($this->order,
                [
                    'order' => [
                        'googleAnalyticsClientId' => $order['googleAnalyticsClientId'],
                    ],
                ]);
        }

        // Remove fields that are not filled for customer and deliveryCustomer
        if (empty($this->order['customer']['address']['streetNumber'])) unset($this->order['customer']['address']['streetNumber']);
        if (empty($this->order['deliveryCustomer']['address']['streetNumber'])) unset($this->order['deliveryCustomer']['address']['streetNumber']);
        if (empty($this->order['customer']['address']['streetNumberAdditional'])) unset($this->order['customer']['address']['streetNumberAdditional']);
        if (empty($this->order['deliveryCustomer']['address']['streetNumberAdditional'])) unset($this->order['deliveryCustomer']['address']['streetNumberAdditional']);
        if (empty($this->order['customer']['address']['careOf'])) unset($this->order['customer']['address']['careOf']);
        if (empty($this->order['deliveryCustomer']['address']['careOf'])) unset($this->order['deliveryCustomer']['address']['careOf']);
        if (empty($this->order['deliveryCustomer']['address']['addressType'])) unset($this->order['deliveryCustomer']['address']['addressType']);
        if (empty($this->order['customer']['birthDate'])) unset($this->order['customer']['birthDate']);
        if (empty($this->order['deliveryCustomer']['birthDate'])) unset($this->order['deliveryCustomer']['birthDate']);
        if (empty($this->order['customer']['identificationNumber'])) unset($this->order['customer']['identificationNumber']);
        if (empty($this->order['deliveryCustomer']['identificationNumber'])) unset($this->order['deliveryCustomer']['identificationNumber']);
        if (empty($this->order['customer']['legalForm'])) unset($this->order['customer']['legalForm']);
        if (empty($this->order['deliveryCustomer']['legalForm'])) unset($this->order['deliveryCustomer']['legalForm']);
        if (empty($this->order['customer']['email'])) unset($this->order['customer']['email']);
        if (empty($this->order['deliveryCustomer']['email'])) unset($this->order['deliveryCustomer']['email']);
        if (empty($this->order['customer']['salutation'])) unset($this->order['customer']['salutation']);
        if (empty($this->order['deliveryCustomer']['salutation'])) unset($this->order['deliveryCustomer']['salutation']);
        if (empty($this->order['customer']['riskData']['ipAddress'])) unset(
            $this->order['customer']['riskData']['ipAddress']
        );
        if (empty($this->order['customer']['riskData']['ipAddress'])) unset(
            $this->order['deliveryCustomer']['riskData']['ipAddress']
        );
        if (empty($this->order['customer']['riskData']['existingCustomer'])) unset(
            $this->order['customer']['riskData']['existingCustomer']
        );
        if (empty($this->order['customer']['riskData']['existingCustomer'])) unset(
            $this->order['deliveryCustomer']['riskData']['existingCustomer']
        );
        if (empty($this->order['customer']['riskData']['profileTrackingId'])) {
            unset($this->order['customer']['riskData']['profileTrackingId']);
            unset($this->order['deliveryCustomer']['riskData']['profileTrackingId']);
        }
        if (empty($this->order['customer']['riskData']['customerIndividualScore'])) {
            unset($this->order['customer']['riskData']['customerIndividualScore']);
            unset($this->order['deliveryCustomer']['riskData']['customerIndividualScore']);
        }
        if (empty($this->order['customer']['mobilePhone'])) unset($this->order['customer']['mobilePhone']);
        if (empty($this->order['deliveryCustomer']['mobilePhone'])) unset($this->order['deliveryCustomer']['mobilePhone']);
    }

    /**
     * Set order types to correct webservice calls and function names
     *
     * @param string $orderType
     * @param string $orderNumber
     */
    private function setOrderType($orderType, $orderNumber = '')
    {
        $this->orderType = $orderType;
        switch ($this->orderAction) {
            case 'capture_full':
            case 'capture_partial':
                $this->requestUrl = sprintf('orders/%s/captures', $orderNumber);
                $this->requestMethod = 'POST';
                break;
            case 'refund_full':
            case 'refund_partial':
                $this->requestUrl = sprintf('orders/%s/refunds', $orderNumber);
                $this->requestMethod = 'POST';
                break;
            case 'void':
                $this->requestUrl = sprintf('orders/%s/voids', $orderNumber);
                $this->requestMethod = 'POST';
                break;
            case 'customer_lookup':
                $this->requestUrl = 'lookup/customer';
                $this->requestMethod = 'POST';
                break;
            case 'installmentplans_lookup':
                $this->requestUrl = 'lookup/installment-plans';
                $this->requestMethod = 'POST';
                break;
            case 'validate_bankaccount':
                $this->requestUrl = 'validate/bank-account';
                $this->requestMethod = 'POST';
                break;
            case 'available_payment_methods':
                $this->requestUrl = 'checkout/payment-methods';
                $this->requestMethod = 'POST';
                break;
            case 'get_order':
                $this->requestUrl = sprintf('orders/%s', $orderNumber);
                $this->requestMethod = 'GET';
                break;
            default:
                $this->requestUrl = '';
                $this->requestUrl = 'checkout/authorize';
                $this->requestMethod = 'POST';
                break;
        }
    }

    /**
     * Process request to REST webservice
     *
     * @param array $authorization
     * @param string $mode
     * @param string|null $language
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function doRequest($authorization, $mode, $language = null)
    {
        $this->setMode($mode);
        $this->setAuthorization($authorization);
        $this->setRestClient();
        try {
            // Set the request parameters.
            $requestParameters = [
                // Use the 'body' method instead of 'json' method because of the JSON_UNESCAPED_UNICODE setting
                'body' => json_encode($this->order, JSON_UNESCAPED_UNICODE),
                'headers' => ['Content-Type' => 'application/json']
            ];
            // Unset the body parameter when the request method is a GET call.
            if($this->requestMethod == 'GET') {
                unset($requestParameters['body']);
            }
            $this->apiResponse = $this->restClient->request(
                $this->requestMethod,
                $this->requestUrl,
                $requestParameters
            );
            $this->createDebugLine('Request', $this->order);
            $this->orderResultTmp = json_decode($this->apiResponse->getBody());
            $this->orderResult = [
                'return' => (array)$this->orderResultTmp
            ];
            $this->createDebugLine('Response', $this->orderResultTmp);
            if ($this->orderManagement) {
                $this->additional = [
                    'return' => [
                        'resultId' => 0
                    ]
                ];
                if ($this->orderAction === 'capture_full') {
                    $captureAdditional = [
                        'return' => [
                            'transactionId' => $this->orderResultTmp->captureNumber,
                            'totalInvoicedAmount' => $this->orderResultTmp->capturedAmount,
                            'totalReservedAmount' => $this->orderResultTmp->authorizedAmount,
                        ]
                    ];
                    $this->additional = array_merge_recursive($this->additional, $captureAdditional);
                }
            } else {
                $this->additional = [
                    'return' => [
                        'statusCode' => $this->statusCodes[$this->orderResultTmp->outcome],
                        'resultId' => $this->resultId[$this->orderResultTmp->outcome]
                    ]
                ];
            }
            $this->orderResult = array_merge_recursive($this->orderResult, $this->additional);
        } catch (ClientException $e) {
            $this->createDebugLine('Error', null, $e->getResponse()->getBody());
            $this->orderResultTmp = json_decode($e->getResponse()->getBody());
            $this->orderResult = [
                'return' => (array)$this->orderResultTmp
            ];
            $this->additional = $this->getAdditional($e, $this->orderResultTmp);
            $this->orderResult = array_merge_recursive((array)$this->orderResult, $this->additional);
        } catch (ServerException $e) {
            $this->createDebugLine('Error', null, $e->getResponse()->getBody());
            $this->orderResultTmp = json_decode($e->getResponse()->getBody());
            $this->orderResult = [
                'return' => (array)$this->orderResult
            ];
            $this->additional = $this->getAdditional($e, $this->orderResultTmp);
            $this->orderResult = array_merge_recursive($this->orderResult, $this->additional);
        } catch (\Exception $e) {
            $this->createDebugLine('Error', null, json_encode($e->getMessage()));
            $this->orderResult = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => $e->getMessage(),
                        'description' => \Afterpay\check_technical_error('default.message', $language),
                    ],
                    'messages' => [
                        [
                            'description' => \Afterpay\check_technical_error('default.message', $language),
                            'message' => $e->getMessage()
                        ]
                    ]
                ]
            ];
        }
    }

    /**
     * Sets mode, options are test or live
     *
     * @param string $mode
     *
     */
    protected function setMode($mode)
    {
        $this->mode = $mode;
        $this->createDebugLine('Set mode to: ' . $this->mode);
        $this->webServiceUrl = $this->getWebserviceUrl($this->country, $mode);
        $this->createDebugLine('Set WebServiceUrl to: ' . $this->webServiceUrl);
    }

    /**
     * Get correct API endpoint for the client
     *
     * @param string $country
     * @param string $mode
     *
     * @return null|string
     */
    protected function getWebserviceUrl($country, $mode)
    {
        $webServiceUrl = null;
        if ($mode === 'test') {
            $webServiceUrl = 'https://api.bnpl-pt.riverty.io/api/v3/';
        } elseif ($mode === 'sandbox') {
            $webServiceUrl = 'https://sandbox.bnpl.riverty.io/api/v3/';
        } elseif ($mode === 'live') {
            $webServiceUrl = 'https://api.bnpl.riverty.io/api/v3/';
        }
        return $webServiceUrl;
    }

    /**
     * Set authorization for Rest client connection
     *
     * @param array $authorization
     */
    protected function setAuthorization($authorization)
    {
        $this->authorization = [
            'apiKey' => $authorization['apiKey']
        ];
    }

    /**
     * Set correct soap client, differs per country
     *
     */
    private function setRestClient()
    {
        $this->restClient = new GuzzleHttp\Client([
            'headers' => [
                'X-Auth-Key' => $this->authorization['apiKey']
            ],
            'base_uri' => $this->webServiceUrl
        ]);
    }

    /**
     * @param \GuzzleHttp\Exception\ClientException | \GuzzleHttp\Exception\ServerException $exception
     * @param \stdClass $message
     *
     * @return array
     */
    private function getAdditional($exception, $message)
    {
        $additional = [];
        $statusCode = $exception->getResponse()->getStatusCode();

        if ($statusCode === 400) {
            if (is_array($message)) {
                $message = $message[0];
            }
            if (!property_exists($message, 'customerFacingMessage')) {
                $message->customerFacingMessage = $message->message;
            }
            $errorType = $message->type . '.' . $message->actionCode;
            $additional = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => $errorType,
                        'description' => $message->message
                    ],
                    'messages' => [
                        [
                            'message' => $message->customerFacingMessage,
                            'description' => $message->customerFacingMessage,
                        ]
                    ]
                ]
            ];
        } elseif ($statusCode === 401) {
            $additional = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => 'Unauthorized'
                    ]
                ]
            ];
        } elseif ($statusCode === 404) {
            $additional = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => 'NotFound'
                    ]
                ]
            ];
        } elseif ($statusCode === 422) {
            $additional = [
                'return' => [
                    'resultId' => 2,
                    'failures' => [
                        'failure' => 'BadInput'
                    ]
                ]
            ];
        } elseif ($statusCode === 429) {
            $additional = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => 'TooManyRequests'
                    ]
                ]
            ];
        } elseif ($statusCode === 500) {
            $additional = [
                'return' => [
                    'resultId' => 1,
                    'failures' => [
                        'failure' => 'InternalServerError'
                    ],
                ]
            ];
        }
        return $additional;
    }

    /**
     * Getter for order
     *
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Getter for authorization
     *
     * @return array
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Getter for mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Getter for order result
     *
     * @return object
     */
    public function getOrderResult()
    {
        if (is_array($this->orderResult)) {
            return json_decode(json_encode($this->orderResult));
        }
        return $this->orderResult;
    }

    /**
     * Verifies whether the URL is correct
     *
     * @param $url
     * @return bool
     */
    protected function verifyUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        }

        return false;
    }
}
