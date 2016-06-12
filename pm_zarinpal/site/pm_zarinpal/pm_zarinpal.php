<?php
ini_set('display_errors', 1);
defined('_JEXEC') or die('Restricted access');

class pm_zarinpal extends PaymentRoot
{

    function showPaymentForm($params, $pmconfigs)
    {
        require dirname(__FILE__) . "/paymentform.php";
    }

    //function call in admin
    function showAdminFormParams($params)
    {
        $array_params = [
            'merchant_id',
            'transaction_end_status',
            'transaction_pending_status',
            'transaction_failed_status'
        ];
        foreach ($array_params as $key) {
            if (!isset($params[$key])) $params[$key] = '';
        }
        $params['testmode'] = isset($params['testmode']) ? $params['testmode'] : "1";
        $params['gate_type'] = isset($params['gate_type']) ? $params['gate_type'] : "0";
        $params['currency'] = isset($params['currency']) ? $params['currency'] : "0";
        $orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
        require dirname(__FILE__) . "/adminparamsform.php";
    }

    function checkTransaction($pmconfigs, $order, $act)
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        try {
            if ($input->getString('Status') != 'OK')
                throw new Exception("پرداخت ناموفق بود", 3);

            $MerchantID = $pmconfigs['merchant_id'];
            $session =& JFactory::getSession();
            $Amount = (int)$session->get('amount');
            $Authority = $input->getString('Authority');

            $verifyContext = compact('MerchantID', 'Amount', 'Authority');
            $verify = $this->zarinPalRequest('verification', $verifyContext, $pmconfigs['testmode']);

            if (!$verify)
                throw new Exception("عملیات اعتبارسنجی تراکنش ناموفق بود. اشکالی در ارتباط با زرین پال پیش آمده است.", 3);

            $status = $verify->Status;
            if ($status == 100) {
                $RefID = $verify->RefID;

                $transaction = new stdClass();
                $transaction->id = null;
                $transaction->order_id = (string)$order->order_id;
                $transaction->ref_id = (string)$RefID;
                $transaction->amount = (string)$Amount;

                JFactory::getDBO()->insertObject('#__jshopping_zarinpal', $transaction);

                $message = "خرید شما موفق بود <br> شماره سفارش شما: {$order->order_id} <br>شماره پیگیری زرین پال: {$RefID}";
                $app->enqueueMessage($message, 'message');
                return array(1, $message, $RefID);
            }

            throw new Exception($this->zarinPalStatusMessage($status), 2);

        } catch (Exception $e) {
            $message = "خطای پیش آمده این است: " . '<br>' . $e->getMessage();
            saveToLog("payment.log", "Status pending. Order ID " . $order->order_id . ". Error Reason: " . $e->getMessage());
            $app->enqueueMessage($message, 'warning');
            return array($e->getCode(), $message);
        }
    }

    function showEndForm($pmconfigs, $order)
    {
        // zarinpal request context
        $MerchantID = $pmconfigs['merchant_id'];
        $Amount = $this->zarinPalAmount($order->order_total, $pmconfigs['currency']);
        $Description = "سفارش شماره " . $order->order_number;
        $Email = $order->email;
        $Mobile = $order->phone;
        $pm_method = $this->getPmMethod();
        $uri = JURI::getInstance();
        $liveurlhost = $uri->toString(array("scheme", 'host', 'port'));
        $return = $liveurlhost . SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=" . $pm_method->payment_class);
        $CallbackURL = $return . "&orderID=" . $order->order_id;

        $requestContext = compact(
            'MerchantID',
            'Amount',
            'Description',
            'Email',
            'Mobile',
            'CallbackURL'
        );

        $app = JFactory::getApplication();

        $testMode = $pmconfigs['testmode'];

        try {
            $request = $this->zarinPalRequest('request', $requestContext, $testMode);

            if (!$request)
                throw new Exception("مشکلی در ارتباط پیش آمده است. لطفا چند دقیقه بعد دوباره امتحان کنید.");

            $status = $request->Status;
            if ($status == 100) {
                $session =& JFactory::getSession();
                //$session->set('orderID', $order->order_id);
                $session->set('amount', $Amount);

                $prefix = (bool)$testMode ? 'sandbox' : 'www';
                $postfix = (bool)$pmconfigs['gate_type'] ? '/ZarinGate' : '';
                $zarinpalRedirect = "https://{$prefix}.zarinpal.com/pg/StartPay/{$request->Authority}{$postfix}";

                $app->redirect($zarinpalRedirect);
                exit;
            }

            $message = $this->zarinPalStatusMessage($status);
            throw new Exception($message);
        } catch (Exception $e) {
            $message = "خطای پیش آمده این است:" . "<br>" . $e->getMessage();
            $app->enqueueMessage($message, 'warning');
            exit;
        }
    }

    function getUrlParams($pmconfigs)
    {
        $params = array();
        $params['order_id'] = JRequest::getInt("orderID");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 1;
        return $params;
    }

    /*
 * return related zarinpal status message
 */
    private function zarinPalStatusMessage($status)
    {
        $status = (string)$status;
        $statusCode = [
            '-1' => 'اطلاعات ارسال شده ناقص است.',
            '-2' => 'IP و یا کد پذیرنده اشتباه است',
            '-3' => 'با توجه به محدودیت های شاپرک امکان پرداخت با رقم درخواست شده میسر نمی باشد',
            '-4' => 'سطح تایید پذیرنده پایین تر از سطح نقره ای است',
            '-11' => 'درخواست موردنظر یافت نشد',
            '-12' => 'امکان ویرایش درخواست میسر نمی باشد',
            '-21' => 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد',
            '-22' => 'تراکنش ناموفق می باشد',
            '-33' => 'رقم تراکنش با رقم پرداخت شده مطابقت ندارد',
            '-34' => 'سقف تقسیم تراکنش از لحاظ تعداد یا رقم عبور نموده است',
            '-40' => 'اجازه دسترسی به متد مربوطه وجود ندارد',
            '-41' => 'اطلاعات ارسالی مربوط به اطلاعات اضافی غیرمعتبر می باشد',
            '-42' => 'مدت زمان معتبر طول عمر شناسه پرداخت باید بین ۳۰ دقیقه تا ۴۵ روز می باشد',
            '-54' => 'درخواست موردنظر آرشیو شده است',
            '101' => 'عملیات پرداخت موفق بوده و قبلا اعتبارسنجی تراکنش انجام شده است'
        ];
        if (isset($statusCode[$status])) {
            return $statusCode[$status];
        }
        return 'خطای نامشخص. کد خطا: ' . $status;
    }

    /*
 * make a zarinpal soap request
 */
    private function zarinPalRequest($type, $context, $testmode)
    {
        try {
            $prefix = (bool)$testmode ? 'sandbox' : 'www';
            $client = new SoapClient("https://{$prefix}.zarinpal.com/pg/services/WebGate/wsdl", array('encoding' => 'UTF-8'));

            $type = 'Payment' . ucfirst($type);
            $result = $client->$type($context);
            return $result;
        } catch (SoapFault $e) {
            return false;
        }
    }

    /*
 * detect currency for amount
 */
    private function zarinPalAmount($price, $currency)
    {
        if ((bool)$currency)// $currency == '1' => 'rial'
        {
            $price /= 10;
        }

        return (int)$price;
    }
}